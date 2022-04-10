<?php
########################################################################
#
# Project: Grocery List
# URL: http://sourceforge.net/projects/grocery-list/
# E-mail: neil@nabber.org
#
# Copyright: (C) 2010, Neil McNab
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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/listshop.php $
# Last Updated: $Date: 2011-07-14 15:56:37 -0400 (Thu, 14 Jul 2011) $
# Author(s): Neil McNab
#
# Description:
#   Generate a shopping list based on database values.
#
########################################################################

$QTY_VIEW='1';
$TOTAL_VIEW='2';

include_once("include/site.php");

$db = dbconnect();

$listname = get_listname_from_id($db, $_REQUEST['id']);

$title = "List - $listname";

$id = $_REQUEST['id'];
$view = $_REQUEST['view'];
$fmt = $_REQUEST['fmt'];

if (!$GLIST_INVENTORY_MODE) {
    $view = $QTY_VIEW;
}

if (!empty($id) AND $id >= 0) {

  if (empty($view)) {
    // set default view
    if (dbexist($db, "SELECT * FROM listitems WHERE NOT (units='' OR size='0') AND acctid='$GLIST_ACCT_ID' AND type='tobuy' AND listid=" . $db->quote($id))) {
        $view = $TOTAL_VIEW;
    } elseif (!dbexist($db, "SELECT * FROM listitems WHERE type='mininventory' AND acctid='$GLIST_ACCT_ID' AND listid=" . $db->quote($id))) {
        $view = $QTY_VIEW;
    } else {
        $view = $TOTAL_VIEW;
    }
  }

$results = array();

// key on producutid,units for total view, all three for units view

foreach (array('tobuy','fullinventory','mininventory','instock') as $key) {
    foreach(get_view_shop_by_listid($db, $id, $key)->fetchAll() as $res) {
        if (!array_key_exists($res['productname'], $results))
            $results[$res['productname']] = array();
        if (!array_key_exists($res['units'], $results[$res['productname']]))
            $results[$res['productname']][$res['units']] = array();
        if (!array_key_exists($res['size'], $results[$res['productname']][$res['units']]))
            $results[$res['productname']][$res['units']][$res['size']] = array();
        if (!array_key_exists($res['type'], $results[$res['productname']][$res['units']][$res['size']])) {
            $results[$res['productname']][$res['units']][$res['size']][$res['type']] = array('count'=>$res['count'],'total'=>$res['total'],'notes'=>$res['notes']);
        }
    }
}

$output = array();

if ($view == $QTY_VIEW) {

//print "<p><a href='?id=$id&amp;view=$TOTAL_VIEW'>Switch to Quantity View</a></p>\n";

$keys = array_keys($results);
asort($keys);
foreach ($keys as $name) {
  $row1 = $results[$name];
  foreach ($row1 as $units => $row2) {
    foreach ($row2 as $size => $row3) {
        $invsize = 0;
        $total = 0;
        $buysize = 0;
      foreach ($row3 as $type => $res) {
          if ($type == 'tobuy') {
            $buysize = $res['count'];
          }
          if ($type == 'instock') {
            $invsize = $res['count'];
          }
      }

      $style = 'text-align: left;';
      if (floatval($invsize) <= 0) {
        $style .= 'color: red;';
      }

      $total = floatval($buysize);
      $notes = htmlspecialchars($res['notes']);

      if ($total > 0) {
          list($size, $units) =  measure_unit_convert($size, $units, FALSE);
          list($size2, $units2) =  measure_unit_convert($size, $units, TRUE);
          if ($units == 'ct') {
              $output[] = array("$total " . htmlspecialchars($name) .  " ($size $units)", "", $style);
              //print "<p style='$style'><input type='checkbox' /> $total " . htmlspecialchars($name) .  " ($size $units)</p>\n";
          } elseif ($GLIST_INVENTORY_MODE) {
              $output[] = array("$total " . htmlspecialchars($name) .  " ($size $units/$size2 $units2)", $notes, $style);
              //print "<p style='$style'><input type='checkbox' /> $total " . htmlspecialchars($name) . " - $notes ($size $units/$size2 $units2)</p>\n";
          } else {
              $output[] = array("$total " . htmlspecialchars($name), $notes, $style);
              //print "<p style='$style'><input type='checkbox' /> $total " . htmlspecialchars($name) . " - $notes ($size $units/$size2 $units2)</p>\n";
          }
      }
    }
  }
}

} else {

$keys = array_keys($results);
asort($keys);
foreach ($keys as $name) {
        $row1 = $results[$name];
        $minsize = 0;
        $fullsize = 0;
        $invsize = 0;
        $total = 0;
        $buysize = 0;
  foreach ($row1 as $units => $row2) {
    foreach ($row2 as $size => $row3) {
      foreach ($row3 as $type => $res) {
          if ($type == 'mininventory') {
            $minsize = $res['total'];
          }
          if ($type == 'fullinventory') {
            $fullsize = $res['total'];
          }
          if ($type == 'tobuy') {
            $buysize += floatval($res['total']);
          }
          if ($type == 'instock') {
            $invsize += floatval($res['total']);
          }
      }
    }

  }

      $style = 'text-align: left;';
      if (floatval($invsize) <= 0) {
        $style .= 'color: red';
      }
      $understock = floatval($minsize) - floatval($invsize);
      $addstock = 0;
      if ($understock > 0) {
        $addstock = floatval($fullsize) - floatval($invsize);
      }
      $total = floatval($buysize) + $addstock;

      if ($total > 0) {
          $notes = htmlspecialchars($res['notes']);
          list($total, $units) =  measure_unit_convert($total, $units, FALSE);
          list($total2, $units2) =  measure_unit_convert($total, $units, TRUE);
          if ($units == 'ct') {
              $output[] = array("$total $units - " . htmlspecialchars($name), $notes, $style);
              //print "<p style='$style'><input type='checkbox' />$total $units - " . htmlspecialchars($name) . " - $notes</p>\n";
          } else {
              $output[] = array("$total $units/$total2 $units2 - " . htmlspecialchars($name), $notes, $style);
              //print "<p style='$style'><input type='checkbox' />$total $units/$total2 $units2 - " . htmlspecialchars($name) . " - $notes</p>\n";
          }
      }
}



}

// do display here based on format
if ($fmt == 'ical') {
    include_once("include/ical.php");
    header("Content-Type: text/calendar");
    header("Content-Disposition: inline; filename=ical.ics");

    $myicalfile = new ical_File();
    $mycalendar = new vCalendar();
    $mycalendar->add('X-WR-CALNAME', new ical_Property($title));
    $myicalfile->add('VCALENDAR', $mycalendar);

    foreach ($output as $item) {
          $notes = $item[1];
          $myevent = new vTodo();
          $mycalendar->add('VTODO', $myevent);
          if (strlen($notes) > 0) {
              $myevent->add('DESCRIPTION', new ical_Property($notes));
          }

          $myevent->add('SUMMARY', new ical_Property($item[0]));
    }

    print_r($myicalfile->ical_dump());

} elseif ($fmt == 'txt') {
    header("Content-Type: text/plain");
    print "$title\n\n";
    foreach ($output as $item) {
       print $item[0] . " - " . $item[1] . "\n";
    }    
} elseif ($fmt == 'csv') {
    header("Content-Type: text/csv");
    //header("Content-Disposition: inline; filename=ical.ics");
} elseif ($fmt == 'xml') {
    header("Content-Type: application/xml");

} elseif ($fmt == 'mail') {
  if (!empty($GLIST_EMAIL)) {
    $msg = '';
    foreach ($output as $item) {
       $msg .= $item[0] . " - " . $item[1] . "\n";
    }
    if (!empty($GLIST_FROM_EMAIL)) { 
      mail($GLIST_EMAIL, $title, $msg, "From: $GLIST_FROM_EMAIL");
    } else {
      mail($GLIST_EMAIL, $title, $msg);
    }
    header("Location: ?id=$id&view=$view");
  }

} else {
  include_once("include/header.php");

  if (!acl_read($GLIST_ACCT_ID)) {
    print "<p>ERROR: You don't have permissions to view this list.</p>";
    include_once($WEBROOT . "/include/footer.php");
    exit;
  }

print "<p><a id='top' />";
list_menu_gen($id);
print "</p>\n";

if ($GLIST_INVENTORY_MODE) {
  if ($view == $QTY_VIEW) {
    print "<p><a href='?id=$id&amp;view=$TOTAL_VIEW'>Switch to Quantity View</a></p>\n";
  } else {
    print "<p><a href='?id=$id&amp;view=$QTY_VIEW'>Switch to Item Count View</a></p>\n";
  }
}

  foreach ($output as $item) {
    print "<p style='" . $item[2]  . "'><input type='checkbox' /> " . $item[0] . " - " . $item[1] . "</p>\n";
  }

  if ($view == $QTY_VIEW AND $GLIST_INVENTORY_MODE) {
    if (dbexist($db, "SELECT * FROM listitems WHERE acctid='$GLIST_ACCT_ID' AND type='mininventory' AND listid=" . $db->quote($id))) {
      print '<p>NOTE: This view does not include inventory and baseline calculations.</p>';
    }
  } elseif ($view == $TOTAL_VIEW) {
    $result = $db->query("SELECT productname FROM listitems LEFT JOIN products USING (productid) WHERE (units='' OR size='0') AND listitems.acctid='$GLIST_ACCT_ID' AND products.acctid='$GLIST_ACCT_ID' AND type='tobuy' AND listid=" . $db->quote($id) . " GROUP BY productid;");

    if ($result->rowCount()) {
      print '<p>NOTE: This view does not include items on your list without Amount/Units values: ';
      // show missing items here
      $resultarray = array();
      foreach ($result as $row) {
        $resultarray[] = $row[0];
      }
      print implode(", ", $resultarray);
      print '</p>';
    }
  }
  print "<p>";
  print " <a class='txticon' href='?fmt=txt&amp;id=$id&amp;view=$view'>TEXT</a>";
//  print " <a class='csvicon' href='?fmt=csv&amp;id=$id&amp;view=$view'>CSV</a>";
  print " <a class='icalicon' href='?fmt=ical&amp;id=$id&amp;view=$view'>ICAL</a>";
//  print " <a class='xmlicon' href='?fmt=xml&amp;id=$id&amp;view=$view'>XML</a>";
  print "</p>\n";
  if (!empty($GLIST_EMAIL)) {
    print "<p><a href='?fmt=mail&amp;id=$id&amp;view=$view'>Send by e-mail to $GLIST_EMAIL</a></p>";
  }

  include_once($WEBROOT . "/include/footer.php");
}

}

$db = NULL;

?>
