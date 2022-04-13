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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/functions.php $
# Last Updated: $Date: 2011-01-27 17:55:59 -0500 (Thu, 27 Jan 2011) $
# Author(s): Neil McNab
#
# Description:
#   Generic functions used through the code.
#
########################################################################

function PageURL() {
 $pageURL = 'http';
  //$end = $_SERVER['REQUEST_URI'];
  $end = $_SERVER['SCRIPT_NAME'];
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["HTTPS"] == "on" && $_SERVER["SERVER_PORT"] != "443") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } elseif ($_SERVER["HTTPS"] != "on" && $_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"];
 }
 return $pageURL . $end;
}

function list_menu_gen($id) {
    global $GLIST_INVENTORY_MODE;
    print "<a href='listshop.php?id=$id'>Go Shopping</a> | ";
    print "<a href='listitems.php?id=$id'>Items to Buy</a>";
    if ($GLIST_INVENTORY_MODE) {
        print " | <a href='listinventory.php?id=$id'>Inventory</a> | ";
        print "<a href='listbaselines.php?id=$id'>Baseline</a>";
    }
}

function form_gen($list, $submit, $action="") {
    print "\n";
    print "<form method=\"post\" action=\"$action\">\n";
    foreach ($list as $item) {
        $id = $item[0];
        $label = $item[1];
        $html = $item[2];
        print "<div class='formrow'><label class='formlabel' for='$id'>$label:</label> <span>$html</span>";
//        if ($id == count) {
//		print "<script type='text/javascript'> incrementor('count'); </script>";
//	}
        print "</div>\n";
    }
    print "<p>" . $submit . "</p>";
    print "<p>* Indicates a required field</p>";
    print "\n</form>\n\n";
}

function get_option($id, $name, $selected='') {
    $text = '';
    $text .= "<option value='$id'";
    if ($selected == $id)
        $text .= " selected='selected' ";
    $text .= ">" . htmlspecialchars($name) . "</option>\n";
    return $text;
}

function getElapsedString($originalDate, $roundTo=0) {
  $elapsedTime =  time() - $originalDate;

  if($elapsedTime==1) {
      // One second
      $elapsedString = $elapsedTime . " second";
  } else if($elapsedTime<(60)) {
      // Seconds
      $elapsedString = $elapsedTime . " seconds";
  } else if($elapsedTime<(60*60)) {
      // Minutes
      $elapsedString = round($elapsedTime/60, $roundTo) . " minutes";
  } else if($elapsedTime<(60*60*24*2)) {
      // Hours
      $elapsedString = round($elapsedTime/60/60, $roundTo) . " hours";
  } else if($elapsedTime<(60*60*24*7*2)) {
      // Days
      $elapsedString = round($elapsedTime/60/60/24, $roundTo) . " days";
  } else if($elapsedTime<(60*60*24*30*2)) {
      // Weeks
      $elapsedString = round($elapsedTime/60/60/24/7, $roundTo) . " weeks";
  } else if($elapsedTime<(60*60*24*365*2)) {
      // Months
      $elapsedString = round($elapsedTime/60/60/24/30, $roundTo) . " months";
  } else {
      // Years
      $elapsedString = round($elapsedTime/60/60/24/365, $roundTo) . " years";
  }

  return $elapsedString;
}

function measure_unit_convert($count, $unit, $metric=TRUE) {
    $unit = measure_unit_normalize($unit);
    //volume
    if ($unit == 'l') {
        $unit = 'ml';
        $count = $count * 1000;
    }
    if ($unit == 'pt') {
        $unit = 'gal';
        $count = $count * 0.125;
    }
    if ($unit == 'qt') {
        $unit = 'gal';
        $count = $count * 0.25;
    }
    if ($unit == 'gal') {
        $unit = 'fl. oz';
        $count = $count * 128;
    }
    //weights
    if ($unit == 'kg') {
        $unit = 'g';
        $count = $count * 1000;
    }
    if ($unit == 'mg') {
        $unit = 'g';
        $count = $count / 1000;
    }
    if ($unit == 'lb') {
        $unit = 'oz';
        $count = $count * 16;
    }
    // quantity
    if ($unit == 'doz') {
        $unit = 'ct';
        $count = $count * 12;
    }

    // do conversions here
    if ($metric AND in_array($unit, array('oz','fl. oz'))) {
        if ($unit == 'oz') {
            $unit = 'g';
            $count = $count * 28.3495231;
        }
        if ($unit == 'fl. oz') {
            $unit = 'ml';
            $count = $count * 29.5735296;
        }
        if ($unit == 'sq. ft') {
            $unit = 'sq. m';
            $count = $count * 0.09290304;
        }
    }
    elseif (in_array($unit, array('ml', 'g'))) {
        if ($unit == 'g') {
            $unit = 'oz';
            $count = $count / 28.3495231;
        }
        if ($unit == 'ml') {
            $unit = 'fl. oz';
            $count = $count / 29.5735296;
        }
        if ($unit == 'sq. m') {
            $unit = 'sq. ft';
            $count = $count / 0.09290304;
        }
    }

    return array(round($count), $unit);
}

function measure_unit_normalize($unit) {
    $map = array(
		array("lbs", "lb"),
		array("pound", "lb"),
		array("pint", "pt"),
		array("quart", "qt"),
		array("ounce", "oz"),
		array("dozen", "doz"),
		array("gallon", "gal"),
		array("milliliter", "ml"),
		array("millilitre", "ml"),
		array("liter", "l"),
		array("litre", "l"),
		array("kilogram", "kg"),
		array("gram", "g"),
		array("milligram", "mg"),
		array("gr", "g"),
		array("cc", "cm3"),
		array("cubic centimeter", "cm3"),
		array("count", "ct"),
		array("fl oz", "fl. oz"),
		array("fluid ounce", "fl. oz"),
		array("square feet", "sq. ft"),
		array("sq ft", "sq. ft"),
		array("square meter", "sq. m"),
		array("sq m", "sq. m"),
    );

    $newunit = trim($unit);
    $newunit = trim($newunit, ".");
    $newunit = strtolower($newunit);

    foreach ($map as $item) {
        if (($newunit == $item[0]) OR ($newunit == $item[0] . "s")) {
            $newunit = $item[1];
        }
    }

    return $newunit;
}

?>
