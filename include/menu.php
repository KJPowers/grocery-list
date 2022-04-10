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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/menu.php $
# Last Updated: $Date: 2010-09-19 22:57:50 -0400 (Sun, 19 Sep 2010) $
# Author(s): Neil McNab
#
# Description:
#   Left side navigation menu.
#
########################################################################
?>

<p><a href='index.php'>Home</a></p>
<p><a href='start.php'>Start</a></p>
<p><a href='items.php'>Manage Items</a></p>
<p><a href='products.php'>Manage Products</a></p>
<p><a href='lists.php'>Manage Lists</a></p>

<p>Your Lists</p>

<?php

$menudb = dbconnect();

if ($menudb != NULL) {
    $result = get_lists($menudb);

    foreach ($result as $row) {
        print '<p><a href="listshop.php?id=' . $row['listid'] . '">' . $row['listname'] . '</a></p>';
    }
}

$menudb = NULL;

?>
