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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/migrate.php $
# Last Updated: $Date: 2010-03-06 03:03:07 -0500 (Sat, 06 Mar 2010) $
# Author(s): Neil McNab
#
# Description:
#   This moves items from the old groceries table to the new schema.
#
########################################################################

$title = "Migrate from Old Database Format";

include_once("include/header.php");

$db = dbconnect();

$result = dbexist($db, "SELECT count(*) FROM groceries");

$result2 = dbexist($db, "SELECT count(*) FROM listitems WHERE acctid='$GLIST_ACCT_ID'");

if ($result AND !$result2) {
    print "<p>Creating new Grocery List named 'Migrated.'</p>";
    $db->exec("INSERT INTO lists SET listname='Migrated',acctid='$GLIST_ACCT_ID'");

    $listid = dboneshot($db, "SELECT listid FROM lists WHERE listname='Migrated' AND acctid='$GLIST_ACCT_ID'");

    $rows = $db->query("SELECT * FROM groceries");
    $count = 0;
    foreach ($rows as $row) {
        if (!$db->exec("INSERT INTO products SET acctid='$GLIST_ACCT_ID',productname='" . $row['itemname'] . "'")) {
            print "<p>INSERT ERROR 10</p>";
        }
        $productid = dboneshot($db, "SELECT productid FROM products WHERE acctid='$GLIST_ACCT_ID' AND productname='" . $row['itemname'] . "'");
        for ($i = 0; $i < intval($row['quantity']); $i++) {
            $result = $db->exec("INSERT INTO listitems SET acctid='$GLIST_ACCT_ID',listid='$listid',productid='$productid',size='" . intval($row['size']) . "',units='" . $row['units'] . "',notes='" . $row['notes'] . "',type='tobuy'");
            if (!$result) {
                print "<p>INSERT ERROR 20 $i</p>";
            }
        }
        $count++;
    }
    print "<p>Migrated $count items.</p>\n";
} else {
    print "<p>Your database appears to have already been migrated, skipping...</p>";
}

$db = NULL;

print "<p><a href='start.php'>Continue back to the start page...</a></p>";

include_once($WEBROOT . "/include/footer.php");

?>
