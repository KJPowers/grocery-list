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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/dbcheck.php $
# Last Updated: $Date: 2010-03-04 15:47:47 -0800 (Thu, 04 Mar 2010) $
# Author(s): Neil McNab
#
# Description:
#   Database sanity checks, use this when something goes wrong.  It
# does not make changes to the database.
#
########################################################################

$title = "Account DB Check";
include_once("include/header.php");

if (!acl_setpermissions($GLIST_ACCT_ID)) {
    print "<p>ERROR: You don't have access to account settings for this account.</p>";
    include_once($WEBROOT . "/include/footer.php");
    exit;
}

print "<p>This will check your account database against your requested settings and make sure everything is in order.</p>";

$db = dbconnect();

// TODO

// Force lock between generic and item names (old behavior)
if ($GENERIC_LOCK) { 
    $q = sprintf("SELECT * FROM items LEFT JOIN products USING (productid) WHERE items.acctid=%s", $db->quote($GLIST_ACCT_ID));
    $listname = $db->query($q);

    $count = 0;
    foreach ($listname as $row) {
      if ($row['itemname'] != $row['productname']) {
        print "ERROR: In table 'items' at itemid=" . $row['itemid'] . ", " . $row['itemname'] . " is not the same product name as " . $row['productname'] . ".<br />\n";
        $count++;
      }
    }
    if ($count == 0) {
        print "Generic lock check: OK<br />\n";
    }
}

// Require compatible units between inventory and baseline (and list?)
//$CHECK_INVENTORY_UNITS = TRUE;

if ($CHECK_LIST_UNITS) { 
    $listname = $db->query("SELECT * FROM products LEFT JOIN listitems USING (productid) WHERE type IN ('tobuy', 'fullinventory', 'mininventory') AND products.acctid=" . $db->quote($GLIST_ACCT_ID) . " GROUP BY productid");

    $count = 0;
    $keys = array();
    foreach ($listname as $row) {
        if (!array_key_exists($row['productid'], $keys)) {
            $keys[$row['productid']] = $row['units'];
        }
        if ($keys[$row['productid']] != $row['units']) {
            print "ERROR: In table 'listitems' at listitemid=" . $row['listitemid'] . ", inventory entry has size of zero.<br />\n";
            $count++;
        }
    }
    if ($count == 0) {
        print "List compatible units check: OK<br />\n";
    }
}

// Require non zero amounts for inventory
if ($INVENTORY_NON_ZERO) { 
    $listname = $db->query(" SELECT * FROM listitems WHERE type='instock' AND size=0 AND acctid=" . $db->quote($GLIST_ACCT_ID));

    $count = 0;
    foreach ($listname as $row) {
        print "ERROR: In table 'listitems' at listitemid=" . $row['listitemid'] . ", inventory entry has size of zero.<br />\n";
        $count++;
    }
    if ($count == 0) {
        print "Inventory non-zero check: OK<br />\n";
    }
}

// Require non zero amounts for list
if ($LIST_NON_ZERO) { 
    $listname = $db->query(" SELECT * FROM listitems WHERE type='tobuy' AND size=0 AND acctid=" . $db->quote($GLIST_ACCT_ID));

    $count = 0;
    foreach ($listname as $row) {
        print "ERROR: In table 'listitems' at listitemid=" . $row['listitemid'] . ", to buy list entry has size of zero.<br />\n";
        $count++;
    }
    if ($count == 0) {
        print "List non-zero check: OK<br />\n";
    }
}


$db = NULL;

include_once($WEBROOT . "/include/footer.php");

?>
