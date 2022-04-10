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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/sqlload.php $
# Last Updated: $Date: 2010-03-06 03:03:07 -0500 (Sat, 06 Mar 2010) $
# Author(s): Neil McNab
#
# Description:
#   Initializes the database from SQL file if it hasn't been done yet.
#
########################################################################

$title = "SQL Database Load Page";

include_once("include/header.php");

$db = dbconnect();

if ($db->query("show tables;")->rowCount() <= 1) {
    $commands = explode(";", file_get_contents('glist.sql'));
    foreach ($commands as $command) {
        $result = $db->exec($command);
    }
    if ($db->query("show tables;")->rowCount() <= 1) {
        print "<p>ERROR: Database tables did not load correctly.</p>";
    } else {
        print "<p>OK: Database tables appear to have loaded correctly.</p>";
    }
} else {
    print "<p>Your database appears to have already been setup, skipping...</p>";    
}

$db = NULL;

print "<p><a href='start.php'>Continue back to the start page...</a></p>";    

include_once($WEBROOT . "/include/footer.php");

?>
