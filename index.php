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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/index.php $
# Last Updated: $Date: 2010-04-04 18:03:19 -0400 (Sun, 04 Apr 2010) $
# Author(s): Neil McNab
#
# Description:
#   Index page, prompts user for choice of code version.
#
########################################################################


include_once("include/config.defaults.php");

if (!$DEMO) {
    header("Location: start.php");
} else {
    include_once("include/header.php");
    print '<p><a href="start.php">Try Demo</a></p>';
    print '<p><a href="https://sourceforge.net/projects/grocery-list/">Download</a></p>';
    include_once("include/footer.php");
}

?>
