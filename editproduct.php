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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/editproduct.php $
# Last Updated: $Date: 2010-05-04 02:59:40 -0400 (Tue, 04 May 2010) $
# Author(s): Neil McNab
#
# Description:
#   Edit or add a product in the database.
#
########################################################################

$title = "Edit Product";
$onload = "document.getElementsByName('newcat')[0].focus();";
include_once("include/header.php");

$id = $_REQUEST['id'];

$db = dbconnect();

if (isset($_REQUEST['submit'])) {
    if (empty($_REQUEST['newcat'])) {
        print "<p>ERROR: Empty Generic Product Name.</p>";
    } else {
	// handle product table first
	$expiration = $_REQUEST['exp'];
	$newcat = $_REQUEST['newcat'];

        $id = add_update_product($db, $id, $_REQUEST['newcat']);

        if ($id === NULL) {
            print "<p>query failed 2</p>";
        }
        else {
            print "<p>product added/edited</p>";
        }
    }
}

$result = array();

if (!empty($id)) {
	$tmpresult = get_product_by_productid($db, $id);
	if (!$tmpresult) {
		print "query failed 100";
	} else {
                $result = $tmpresult->fetch();
        }
}

$items = array(array("newcat", "*Generic Product Name", "<input id='newcat' name='newcat' value='" . $result['productname'] . "' />"));

form_gen($items, "<input type='hidden' name='id' value='" . $result['productid'] . "' /><input type='submit' name='submit' value='Add/Update' />");

$db = NULL;

print "<p>Return to <a href='products.php'>Manage Products</a>.</p>\n";

include_once($WEBROOT . "/include/footer.php");

?>
