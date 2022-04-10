<?php
########################################################################
#
# Project: Grocery List
# URL: http://sourceforge.net/projects/grocery-list/
# E-mail: neil@nabber.org
#
# Copyright: (C) 2012, Neil McNab
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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/eandata.php $
# Last Updated: $Date: $
# Author(s): Neil McNab
#
# Description:
#   Interface to eandata.com web service.
#
########################################################################

if (!defined('EANDATAKEY')) {
    // set your key here
    //define('EANDATAKEY', '');
}

function eandata_lookup($upc) {
    $result = array();

    if (!defined('EANDATAKEY')) {
        return FALSE;
    }

    $url = sprintf("http://eandata.com/feed.php?find=%013d&mode=xml&keycode=%s&comp=no", $upc, EANDATAKEY);

    $data = eandata_http_get($url);
    $xml = simplexml_load_string($data);

    $code = strval($xml->status->code);
    // 404 not found, 200 OK, 4xx or 5xx some error
    if ($code != '200') {
        return $result;
    }

    $result['upc'] = strval($xml->product->ean13);
    $result['description'] = strval($xml->product->product);
    $result['size'] = strval($xml->product->description);

    return $result;
}

function eandata_http_get($request) {
  $file = FALSE;
  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $file = curl_exec($ch);
    if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) !== 200 ) {
      //print 'Bad Data File '. RPC_URL . " " . curl_getinfo($ch,CURLINFO_HTTP_CODE); # . " " . $file);
      return FALSE;
    }
  } else {
  // PHP built-in method, not secure, using as failback only
    $file = file_get_contents($request);
  }
  return $file;
}

//print_r(eandata_lookup('013700250125'));

?>


