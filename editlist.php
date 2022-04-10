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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/editlist.php $
# Last Updated: $Date: 2010-08-17 15:17:01 -0400 (Tue, 17 Aug 2010) $
# Author(s): Neil McNab
#
# Description:
#   Edit or add a list in the database.
#
########################################################################

$title = "Edit List";
$onload = "document.getElementsByName('listname')[0].focus();";
include_once("include/header.php");

$id = $_REQUEST['id'];

$db = dbconnect();

if (isset($_REQUEST['submit'])) {
    $listname = $_REQUEST['listname'];

    $errors = '';
    if (empty($listname)) {
        $errors .= "<p>ERROR: Empty list name.</p>";
    }
    print $errors;

    if (strlen($errors) <= 0){
        $id = add_update_list($db, $id, $listname);
        if ($id === NULL) {
	    print "<p>ERROR: 120 query failed</p>";
        } else {
            print "<p>Added List $listname</p>";
        }
    }
} 

$result = array();
if (!empty($id)) {

  if (!acl_modify_list($GLIST_ACCT_ID)) {
    print "<p>ERROR: You don't have permissions to edit this list.</p>";
    include_once($WEBROOT . "/include/footer.php");
    exit;
  } 

	$tmpresult = get_list_by_listid($db, $id);
	if (!$tmpresult) {
	    print "<p>ERROR: 100 query failed</p>";
	} else {
	    $result = $tmpresult->fetch();
        }
} else {
  if (!acl_create_list($GLIST_ACCT_ID)) {
    print "<p>ERROR: You don't have permissions to create a list.</p>";
    include_once($WEBROOT . "/include/footer.php");
    exit;
  }
}

$items = array(array("listname", "*List Name", "<input id='listname' name='listname' value='" . $result['listname'] . "' />"));

form_gen($items, "<input type='hidden' name='id' value='" . $id . "' /><input type='submit' name='submit' value='Add/Update' />");

print "<p>Return to <a href='lists.php'>Manage Lists</a>.</p>\n";


$db = NULL;

include_once($WEBROOT . "/include/footer.php");

?>
