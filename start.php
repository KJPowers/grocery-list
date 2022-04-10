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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/start.php $
# Last Updated: $Date: 2012-03-01 11:58:45 -0500 (Thu, 01 Mar 2012) $
# Author(s): Neil McNab
#
# Description:
#   Start page, includes logic for smartly suggesting to the user.
#
########################################################################


$title = "Start Page";
$onload = "document.getElementsByName('upc')[0].focus();";
include_once("include/site.php");

$db = dbconnect();

if ($db == NULL) {
    include_once("include/header.php");
    print "<p>ERROR: Could not connect to database, check your include/config.php file.</p>";
    print "<p>Also make sure your database has been created and check the permissions on your database.</p>";
    show_features();
    include_once($WEBROOT . "/include/footer.php");
    exit;
}

    // process form here
    if (isset($_REQUEST['action'])) {
        $_SESSION['addremove'] = $_REQUEST['action'];
        if (isset($_REQUEST['updatelist'])) {
          $_SESSION['checkupdatelist'] = TRUE;
        } else {
          $_SESSION['checkupdatelist'] = FALSE;
        }
    }

    if ($_REQUEST['action'] == 'add') {
        if (isset($_REQUEST['listinventory'])) {
            if (isset($_REQUEST['updatelist'])) {
                item_remove($db, $_REQUEST['upc'], $_REQUEST['id'], 'tobuy', FALSE);
            }
            // redirect here
            $_REQUEST['add'] = '';
            redirect('listinventory.php', $_REQUEST);
        }
        if (isset($_REQUEST['listitems'])) {
            // redirect here
            $_REQUEST['add'] = '';
            redirect('listitems.php', $_REQUEST);
        }
    }

include_once("include/header.php");

    if ($_REQUEST['action'] == 'remove') {
        if (isset($_REQUEST['listinventory'])) {
            $result = item_remove($db, $_REQUEST['upc'], $_REQUEST['id'], 'instock');
        }
        if (isset($_REQUEST['listitems'])) {
            $result = item_remove($db, $_REQUEST['upc'], $_REQUEST['id'], 'tobuy');
        }
        if ($result === NULL) {
            printf("<p>Item with UPC %s does not exist.</p>\n", $_REQUEST['upc']); 
            $_REQUEST['upc'] = '';
        }
    }

$showform = TRUE;
if (!isset($_REQUEST['action'])) {
    $showform = friendly_dbcheck($db);
}

if ($showform) {
    // set form defaults
    $checked = ' checked="checked" ';
    $radioadd = $checked;
    $radioremove = '';
    if ($_SESSION['addremove'] == 'remove') {
        $radioadd = '';
        $radioremove = $checked;
    }
    $updatelist = $checked;
    if (isset($_SESSION['checkupdatelist']) && ($_SESSION['checkupdatelist'] == FALSE)) {
        $updatelist = '';
    }

    // put quick add form here

    print '<script type="text/javascript" src="include/cuecat.js"></script>';

    $items = array();
    $items[] = array('upc', "*UPC", '<input id="upc" name="upc" maxlength="255" size="15" value="' . $_REQUEST['upc'] . '" />' . get_upc_link(PageUrl()));
    $items[] = array('remove', "Remove", '<input type="radio" id="remove" name="action" value="remove" ' . $radioremove . ' />');
    $items[] = array('add', "Add", '<input type="radio" id="add" name="action" value="add" ' . $radioadd . ' />');

    if($GLIST_INVENTORY_MODE) {
        //$submit .= '<input type="submit" name="removelistinventory" value="Remove From Inventory" />';
        $items[] = array('updatelist', "Remove from Items to Buy", '<input type="checkbox" id="updatelist" name="updatelist" ' . $updatelist . ' />');
        $submit = '<input type="submit" name="listinventory" value="Update Inventory" />';
    } else {
        $submit = '<input type="submit" name="listitems" value="Update Shopping List" />';
    }

    $qresult = get_lists($db);
    $result = $qresult->fetchAll();
    if (sizeof($result) > 1) {
        $txt = '';
        $txt .= "<select name='id'>\n";
        foreach ($result as $row) {
            $txt .= get_option($row['listid'], $row['listname']);
        }
        $txt .= "</select>";
        $items[] = array('id', "*List", $txt);        
    } else {
        $submit = '<input type="hidden" name="id" value="' . $result[0]['listid'] . '" />' . $submit;
    }

    form_gen($items, $submit);

    ob_flush();
}


    print "<h2>Your Lists</h2>";

    foreach ($result as $row) {
        $id = $row['listid'];
        print '<p>' . $row['listname'] . ' - ';
        list_menu_gen($id);
        print "</p>";

    }

$db = NULL;

show_features();

include_once($WEBROOT . "/include/footer.php");

function friendly_dbcheck($db) {

global $GLIST_ACCT_ID;

if ($db->query("show tables;")->rowCount() <= 1) {
    print "<p>ERROR: Your database hasn't been setup yet, <a href='sqlload.php'>click here</a> to do that now.</p>";    
}

$result = dbexist($db, "SELECT count(*) FROM groceries");

$result2 = dbexist($db, "SELECT count(*) FROM listitems WHERE acctid='$GLIST_ACCT_ID'");

if ($result AND !$result2) {
	print "<p>You appear to have an existing list that hasn't been migrated, <a href='migrate.php'>click here</a> to do that now.</p>";
}

$result1 = dboneshot($db, "SELECT listid FROM lists WHERE acctid='$GLIST_ACCT_ID' LIMIT 1");

if (!$result1) {
	print "<p>You haven't created a list a yet, <a href='editlist.php'>click here to make one</a>.</p>";
} elseif (!$result2) {
	print "<p>You don't have any items in your list yet, <a href='listitems.php?id=$result1'>click here to add one</a>.</p>";
}

return $result1;

}


function show_features() {

global $DEMO;

if ($DEMO) {
?>

<h2>Features</h2>

<ul>
<li>Compatible with wedge and USB barcode scanners</li>
<li>Add, edit, and delete items from your grocery list</li>
<li>Supports UPC barcodes and gets information from <a href='http://www.upcdatabase.com/'>The Internet UPC Database</a> or <a href='http://www.eandata.com'>EANData.com</a> (free API key required for both)</li>
<li>Optional support for ISBN and gets information from <a href='http://isbndb.com/'>ISBNdb.com</a> (free API key required)</li>
<li>Support for the Android <a href="https://code.google.com/p/zxing/wiki/GetTheReader">ZXing Barcode Scanner</a></li>
<li>Manage multiple lists</li>
<li>Group multiple items into a single product category</li>
<li>View your shopping list on your mobile device</li>
<li>Print your shopping list</li>
<li>Optionally integrate your shopping list with the inventory tracker</li>
<li>Optionally set thresholds to buy more when inventory gets too low</li>
<li>Support for amounts and units in addition to quantity</li>
<li>Export to any Calendar/Task management application that supports iCal (RFC 2445)</li>
<li>Send your list to your email address</li>
</ul>

<?php
}
}

function item_remove($db, $upc, $listid, $type, $print=TRUE) {
    $typelist = array($type);

    $data = get_product_by_upc($db, $upc);
    $data = $data->fetch();
    if ($data === FALSE) {
        return NULL;   
    }
    // keys: itemname, productname, size, units
    $result = deleteone_listitem_by_productid($db, $data['productid'], $listid, $typelist, $data['size'], $data['units']);
    if (!$result) {
        $result = deleteone_listitem_by_productid($db, $data['productid'], $listid, $typelist);
    }
    if ($print) {
      if ($result) {
        printf("<p>Removed %s (%s) from list.</p>\n", $data['productname'], $data['itemname']); 
      } else {
        printf("<p>Could NOT Remove %s (%s) from list.</p>\n", $data['productname'], $data['itemname']); 
      }
    }
    return $result;
}

function redirect($page, $data) {
    $query = '?';
    foreach ($data as $key => $value) {
        $query .= $key . '=' . $value . '&';
    }
    header("Location: $page$query");
}

?>
