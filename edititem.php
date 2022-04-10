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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/edititem.php $
# Last Updated: $Date: 2011-01-27 18:23:39 -0500 (Thu, 27 Jan 2011) $
# Author(s): Neil McNab
#
# Description:
#   Edit or add a particular item in the database.
#
########################################################################

$title = "Edit Item";
$head .= '<script type="text/javascript" src="include/autocomplete.js"></script>';
$onload = "document.getElementsByName('upc')[0].focus(); fillit('cat','newcat');";

include_once("include/header.php");

$db = dbconnect();

$id = $_REQUEST['id'];
$prid = $_REQUEST['cat'];
$newcat = $_REQUEST['newcat'];
$itemname = $_REQUEST['itemname'];
$size = $_REQUEST['size'];
$units = measure_unit_normalize($_REQUEST['units']);
$priority = $_REQUEST['priority'];
$upc = $_REQUEST['upc'];

if (isset($_REQUEST['submit'])) {
    $errors = '';
    if (empty($_REQUEST['newcat']) AND empty($_REQUEST['cat'])) {
        $errors .= "<p>ERROR: Missing Generic Item name.</p>";
    }
    print $errors;

    if (strlen($errors) <= 0) {
	// handle product table first
	if (empty($prid)) {
            $prid = add_product($db, $newcat);
	}
        $id = add_update_item($db, $id, $prid, $itemname, $size, $units, $upc, $priority);
        print "<p>Updated item.</p>";
    }
}

$result = array('upc' => $upc, 'productid' => $prid, 'size' => $size, 'units' => $units, 'itemname' => $itemname, 'newcat' => $newcat, 'priority' => $priority);

if (!empty($id)) {
        $tmpresult = get_item_by_itemid($db, $id);
	if ($tmpresult === FALSE) {
            print "query failed id";
	} else {
	    $result = $tmpresult->fetch();
        }
}

if (!empty($upc) AND isset($_REQUEST['lookup'])) {
    $result = get_upc($db, $upc);
    $id = $result['itemid'];
}

print '<script type="text/javascript" src="include/cuecat.js"></script>';

$items = array();
$items[] = array("upc", "Bar code", "<input id='upc' name='upc' value='" . $result['upc'] . "' />" . get_upc_link(PageUrl()) . "<input type='submit' name='lookup' 
value='Lookup' />");
$txt = "<select id='cat' name='cat'>\n";
$txt .= get_option("", "(new)", $result['productname']);
foreach (get_products($db) as $row) {
    $txt .= get_option($row['productid'], $row['productname'], $result['productid']);
}
$txt .= "</select><input id='newcat' name='newcat' value='" . $result['newcat'] . "' onKeyUp=\"findit('cat',this)\" />\n";
$items[] = array("cat", "*Generic Product Name", $txt);
$items[] = array("itemname", "Item Brand Name", "<input id='itemname' size='40' name='itemname' value='" . $result['itemname'] . "' />");
$items[] = array("size", "Size", "<input id='size' name='size' value='" . $result['size'] . "' />");
$items[] = array("units", "Units", "<input id='units' name='units' value='" . $result['units'] . "' />");

form_gen($items, "<input type='hidden' name='id' value='" . $id . "' /><input type='submit' name='submit' value='Add/Update' />");

$db = NULL;

print "<p>Return to <a href='items.php'>Manage Items</a>.</p>\n";

include_once($WEBROOT . "/include/footer.php");

?>
