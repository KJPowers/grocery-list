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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/dbcheck.php $
# Last Updated: $Date: 2010-03-06 03:03:07 -0500 (Sat, 06 Mar 2010) $
# Author(s): Neil McNab
#
# Description:
#   Database sanity checks, use this when something goes wrong.  It 
# does not make changes to the database.
#
########################################################################

$title = "Database Integrity Check";

include_once("include/header.php");
include_once("include/db.php");

$db = dbconnect();

$listname = $db->query(" SELECT * FROM listitems LEFT JOIN lists USING (listid) WHERE listname IS NULL;");

    $count = 0;
    foreach ($listname as $row) {
        print "ERROR: In table 'listitems' at listitemid=" . $row['listitemid'] . ", listid=" . $row['listid'] . " does not exist in table 'lists'.<br />\n";
        $count++;
    }
    if ($count == 0) {
        print "Test 1: OK<br />\n";
    }

$listname = $db->query(" SELECT * FROM listitems LEFT JOIN products USING (productid) WHERE productname IS NULL;");

    $count = 0;
    foreach ($listname as $row) {
        print "ERROR: In table 'listitems' at listitemid=" . $row['listitemid'] . ", productid=" . $row['productid'] . " does not exist in table 'products'.<br />\n";
        $count++;
    }
    if ($count == 0) {
        print "Test 2: OK<br />\n";
    }

$listname = $db->query(" SELECT * FROM items LEFT JOIN products USING (productid) WHERE productname IS NULL;");

    $count = 0;
    foreach ($listname as $row) {
        print "WARNING: In table 'items' at itemid=" . $row['itemid'] . ", productid=" . $row['productid'] . " does not exist in table 'products'.<br />\n";
        $count++;
    }
    if ($count == 0) {
        print "Test 3: OK<br />\n";
    }

$listname = $db->query(" SELECT * FROM vendormap LEFT JOIN vendors USING (vendorid) WHERE vendorname IS NULL;");

    $count = 0;
    foreach ($listname as $row) {
        print "ERROR: In table 'vendormap' at vendorid=" . $row['vendorid'] . ", itemid=" . $row['itemid'] . "; vendorid=" . $row['vendorid'] . " does not exist in table 'vendors'.<br />\n";
        $count++;
    }
    if ($count == 0) {
        print "Test 4: OK<br />\n";
    }

$listname = $db->query(" SELECT * FROM vendormap LEFT JOIN items USING (itemid) WHERE itemname IS NULL;");

    $count = 0;
    foreach ($listname as $row) {
        print "ERROR: In table 'vendormap' at vendorid=" . $row['vendorid'] . ", itemid=" . $row['itemid'] . "; itemid=" . $row['itemid'] . " does not exist in table 'items'.<br />\n";
        $count++;
    }
    if ($count == 0) {
        print "Test 5: OK<br />\n";
    }

/* do duplicate checks in listitems, loop through listid and productid */
$listname = $db->query("SELECT listid FROM lists;");
$listname2 = $db->query("SELECT productid FROM products;");
    $count = 0;
    foreach ($listname as $row) {
        $listid = $row['listid'];
        foreach ($listname2 as $row2) {
            $productid = $row['productid'];
            $countres = dboneshot($db, " SELECT count(*) FROM listitems WHERE listid='$listid' AND productid='$productid' AND type='fullinventory'");
            if ($countres > 1) {
                $count++;
                print "ERROR: IN table 'listitems' at listid='$listid', productid='$productid'; too many rows of type='fullinventory.'<br />\n";
            }
            $count = dboneshot($db, " SELECT count(*) FROM listitems WHERE listid='$listid' AND productid='$productid' AND type='mininventory'");
            if ($countres > 1) {
                $count++;
                print "ERROR: IN table 'listitems' at listid='$listid', productid='$productid'; too many rows of type='mininventory.'<br />\n";
            }
        }
    }

    if ($count == 0) {
        print "Test 6: OK<br />\n";
    }

// if needed, check for differences between fullinventory and mininventory non-size fields
// determine what to do with duplicates from shopping list, merge or keep separate?


$db = NULL;

include_once($WEBROOT . "/footer.php");

?>
