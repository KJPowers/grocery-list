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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/site.php $
# Last Updated: $Date: 2010-03-07 16:12:00 -0500 (Sun, 07 Mar 2010) $
# Author(s): Neil McNab
#
# Description:
#   Sets up variables and functions that every page needs, even non-HTML 
# pages.
#
########################################################################

session_start();
// for security when not over SSL
session_regenerate_id(TRUE);

include_once("config.defaults.php");
include_once("functions.php");
include_once("upc.php");

  $WEBROOT = dirname(dirname(__FILE__));

  include_once("db.php");

  db_demo();
?>
