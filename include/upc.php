<?php
########################################################################
#
# Project: Grocery List
# URL: http://sourceforge.net/projects/grocery-list/
# E-mail: neil@nabber.org
#
# Copyright: (C) 2010-2011, Neil McNab
# License: GNU General Public License Version 3
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 3 of the License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/upc.php $
# Last Updated: $Date: 2012-03-01 11:29:31 -0500 (Thu, 01 Mar 2012) $
# Author(s): Neil McNab
#
# Description:
#   Generic UPC functions.
#
########################################################################

include_once("db.php");
include_once("cuecat.php");

// define database lookup order
if (!isset($UPC_DBS)) {
    $UPC_DBS = array('upcdatabasexml');
}

// define database cache timeout
if (!isset($UPCCACHE_TIMEOUT)) {
    $UPCCACHE_TIMEOUT=3600*24*30;
}

// define database cache record limit
if (!isset($UPCCACHE_LIMIT)) {
    $UPCCACHE_LIMIT=10000000;
}

define('UPC_ROOT', 'http://zxing.appspot.com/scan?ret=');

function get_upc_link($page_url) {
    // check android here, other mobile types?
    $show = false;
    if (stristr($_SERVER['HTTP_USER_AGENT'], "android")) {
        $show = true;
    }
/*    if (stristr($_SERVER['HTTP_USER_AGENT'], "blackberry")) {
        $show = true;
    }
    if (stristr($_SERVER['HTTP_USER_AGENT'], "iphone")) {
        $show = true;
    } */
    if ($show) {
        print '<a href="' . UPC_ROOT . urlencode($page_url) . '?upc={CODE}">Scan Barcode</a>';
    }
};

function get_upc($db, $upc) {
    $result = array();

    if (empty($upc)) {
        return $result;
    }
    if ((substr($upc, 0, 1) == ".") AND (substr($upc, -1, 1) == ".")) {
	$upc = cuecat_decode($upc);
    }
    // remove all non-numbers
    $upc = trim(preg_replace('/[^0-9]/', '', $upc));
    //normalize UPC here so we don't miss the cache because of different representations
    $upc = convert_isbn(convert_upce($upc));

    $response = upc_lookup_cached($db,$upc);
    //overwrite with our custom data
    $newresult = get_item_by_upc($db, $upc);

    if ($response === FALSE) {
        echo "<p>Errors detected during UPCdatabase lookup<br>Please confirm results before continuing</p>";
    } elseif (is_array($response)) {
        if (array_key_exists('description', $response)) {
                $desc = trim($response['description']);
        }

        if (array_key_exists('size', $response)) {
                $pattern = "/^([xX\.0-9\/]*)(.*)/";
                preg_match($pattern, trim($response['size']), $matches);
                $size = trim($matches[1]);
                $units = measure_unit_normalize($matches[2]);
        }
        $result = array_merge($result, array('itemname' => $desc, 'size' => $size, 'units' => $units));
        $result['upc'] = $response['upc'];
    } elseif ($response !== TRUE AND $newresult === NULL) {
        print "<p>Bar code not found: " . $response . "</p>";
        $result['upc'] = $upc;
    }

    foreach (array('upc','size','units','itemname','productid','itemid') as $key) {
        if (!empty($newresult[$key])) {
            $result[$key] = $newresult[$key];
        }
    }
    return $result;
}

function upc_lookup_cached($connection, $upc) {
	global $UPCCACHE_TIMEOUT, $UPCCACHE_LIMIT, $UPC_DBS;

	// check if exists in database, ignore if older than one month
        $sql = "SELECT * FROM upccache WHERE upc=" . $connection->quote($upc);

        $sql_result = $connection->query($sql);

        $row = $sql_result->fetch();
	$updatesql = "INSERT INTO upccache SET upc=%s,itemname=%s,sizeweight=%s,timestamp=NULL";
        if ($row !== FALSE) {
		//print sprintf("%s", time() - strtotime($row['timestamp']));
		if ((time() - strtotime($row['timestamp'])) < $UPCCACHE_TIMEOUT ) {
			// if exist, return value 
			$connection = NULL;
			return array('description' => $row['itemname'], 'size' => $row['sizeweight'], 'upc' => $upc);
		}
		$updatesql = "UPDATE upccache SET upc=%s,itemname=%s,sizeweight=%s,timestamp=NULL WHERE upc=" . $connection->quote($upc);
	}
	// do lookup here
        $result = upc_backend_lookup_search($upc, $UPC_DBS);

            // remove if too many entries
            $count = intval(dboneshot($connection, "SELECT count(*) FROM upccache"));
            if ($count > $UPCCACHE_LIMIT) {
                $connection->exec("DELETE FROM upccache ORDER BY timestamp LIMIT " . strval($count - $UPCCACHE_LIMIT));
            }

		// add upc_lookup results to database
        	$sql = sprintf($updatesql, $connection->quote($upc), $connection->quote($result['description']), $connection->quote($result['size']));
        	$sql_result = $connection->exec($sql);
                if (!$sql_result) {
                    print "update failed 50";
                }
	
	return array_merge(array("upc" => $upc), $result);
}

function upc_backend_lookup_search($upc, $searches=array('upcdatabasexml')) {
    $result = array();
    $start = substr($upc, 0, 3);
    if ((strlen($upc) == 13) AND ($start == '978' OR $start == '979')) {
          // ISBN
        $tempresult = upc_backend_lookup($upc, 'isbndb');
        if ($tempresult !== FALSE AND !empty($tempresult['description'])) {
            return $tempresult;
        }
    }
    // this prioritizes lookups
    for($i = 0; $i < sizeof($searches); $i++) {
        $tempresult = upc_backend_lookup($upc, $searches[$i]);
        // add results here
        if (!empty($tempresult['description']) AND empty($result['description'])) {
            $result['description'] = $tempresult['description'];
        }
        if (!empty($tempresult['size']) AND empty($result['size'])) {
            $result['size'] = $tempresult['size'];
        }
    }

    return $result;
}

function upc_backend_lookup($upc, $db='upcdatabasexml') {
    // this connects to the various backends
    if ($db == 'isbndb') {
        @include_once('isbndb.php');
        if (function_exists('isbndb_lookup'))
            return isbndb_lookup($upc);
    }
    if ($db == 'upcdatabasexml') {
        @include_once('upcdbxml.php');
        if (function_exists('upcxml_lookup'))
            return upcxml_lookup($upc);
    }
    if ($db == 'eandata') {
        @include_once('eandata.php');
        if (function_exists('eandata_lookup'))
            return eandata_lookup($upc);
    }
    if ($db == 'amazon') {
        @include_once('amazon.php');
        if (function_exists('amazon_upc_lookup'))
            return amazon_upc_lookup($upc);
    }
    return array();
}

function compute_check_digit($upc) {
    $upca = convert_upce($upc);
    if (strlen($upca) == 13) {
        $upca = substr($upca, 1);
    }
    $check = 0;
    for ($i = 0; $i < strlen($upca); $i++) {
        if ($i % 2) {
            $check += intval(substr($upca, $i, 1));
        } else {
            $check += intval(substr($upca, $i, 1)) * 3;
        }
    }
    $check = $check % 10;
    if ($check != 0) {
        $check = 10 - $check;
    }
    return substr($upc, 0, -1) . strval($check);
}

function convert_isbn($Item) {
  if (strlen($Item) == 10) {
      return '978' . $Item;
  }
  return $Item;
}

function convert_upce($Item) {
  // http://www.barcodeisland.com/upce.phtml
  if (strlen($Item) == 8) {
    $upca = '';
    if ($Item[6] == '0' OR $Item[6] == '1' OR $Item[6] == '2') {
      $upca .= $Item[1] . $Item[2] . $Item[6] . '0000' . $Item[3] . $Item[4] . $Item[5];
    } elseif ($Item[6] == '3') {
      $upca .= $Item[1] . $Item[2] . $Item[3] . '00000' . $Item[4] . $Item[5];
    } elseif ($Item[6] == '4') {
      $upca .= $Item[1] . $Item[2] . $Item[3] . $Item[4] . '00000' . $Item[5];
    } else {
      $upca .= $Item[1] . $Item[2] . $Item[3] . $Item[4] . $Item[5] . '0000' . $Item[6];
    }
    return $Item[0] . $upca . $Item[7];
  }
  return $Item;
}

//print compute_check_digit('0872710X');
//print compute_check_digit('0522580X');

?>
