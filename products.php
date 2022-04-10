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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/products.php $
# Last Updated: $Date: 2011-08-30 11:41:08 -0400 (Tue, 30 Aug 2011) $
# Author(s): Neil McNab
#
# Description:
#   Manage a list of products.
#
########################################################################

$title = "Product List";

include_once("include/header.php");

function show_buttons($delete, $float=FALSE) {
    $style = "";
    if ($float) {
        $style = "style='display:block; position:fixed; bottom:0; left:0;'";
    }
    print "<div $style><input type='submit' name='submit' value='Delete' /></div>";
}

$select = $_REQUEST['select'];

$db = dbconnect();

if ($_REQUEST['submit'] == "Delete") {
    $count = 0;
    foreach ($_REQUEST['cb'] as $value) {
        // TODO add error checking here
        delete_product_by_id($db, $value);
        $count++;
    }
    print "<p>Deleted $count items.</p>";
    print "<p>You should probably check your <a href='items.php'>Items</a> list and associate them with products if needed.</p>";
}

$result = get_products($db);

print '<p><a href="editproduct.php">Add New Product</a></p>';

print "<p>NOTE: Deleting a product will remove it from all lists.</p>";
print '<form action="">';
show_buttons(TRUE);
print '<table>';
print '<tr><th>Generic Product Name</th><!--<th>Expiration</th><th>Priority</th>--><th><input name="select" type="submit" value="';
if ($select == "Select All") {
    print "Select None";
} else {
    print "Select All";
}
print '" /></th></tr>';

foreach ($result as $row) {
    print '<tr><td><a href="editproduct.php?id=' . $row['productid'] . '">' . htmlspecialchars($row['productname']) . '</a></td><!--<td>' . $row['expiration'] . '</td><td>' . $row['priority'] . '</td>--><td><input name="cb[]" value="' . $row['productid'] . '" type="checkbox"';
    if ($select == "Select All") {
        print ' checked="checked"';
    }
    print ' /></td></tr>';
}
print '</table>';

show_buttons(TRUE);
show_buttons(TRUE, TRUE);
print '</form>';

$db = NULL;

print "<p>NOTE: Deleting a product will remove it from all lists.</p>";

include_once($WEBROOT . "/include/footer.php");

?>
