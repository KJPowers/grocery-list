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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/listitems.php $
# Last Updated: $Date: 2011-08-30 12:20:44 -0400 (Tue, 30 Aug 2011) $
# Author(s): Neil McNab
#
# Description:
#   Manage a list of items to buy for a particular list.
#
########################################################################

include_once("include/site.php");

$type='tobuy';
$typelist=array('tobuy');

$db = dbconnect();

$listname = get_listname_from_id($db, $_REQUEST['id']);

$title = "List - $listname";
$head .= '<script type="text/javascript" src="include/autocomplete.js"></script>';
$onload = "document.getElementsByName('upc')[0].focus(); fillit('productid','newproductid');";

include_once("include/header.php");

$select = $_REQUEST["select"];
$id = intval($_REQUEST['id']);

if (!acl_read($GLIST_ACCT_ID)) {
    print "<p>ERROR: You don't have permissions to view this list.</p>";
    include_once($WEBROOT . "/include/footer.php");
    exit;
}

$delete = acl_delete($GLIST_ACCT_ID);
$modify = acl_modify($GLIST_ACCT_ID);
$create = acl_modify($GLIST_ACCT_ID);
$disabled = "";
if (!$modify) {
    $disabled = 'disabled="disabled"';
}

function show_buttons($modify, $float=FALSE) {
  $style = "";
  if ($float) {
    $style = "style='display:block; position:fixed; bottom:0; left:0;'";
  }
  if ($modify) {
    print "<div $style>";
    print '<a href="#top"><input type="button" value="Top" /></a>';
    print "<input type='submit' name='submit' value='Update' />";
    print "</div>";
  }
}

if (!empty($id) AND $id >= 0) {

print "<p><a id='top' />";
list_menu_gen($id);
print "</p>\n";

// process update form

if ($_REQUEST['submit'] == "Update") {
    $listitemsstore = array();
    for ($i = 0; $i < sizeof($_REQUEST['listitemid']); $i++) {
        $key = $_REQUEST['listitemid'][$i];
        $listitemsstore[$key] = array(false, $_REQUEST['size'][$i], measure_unit_normalize($_REQUEST['units'][$i]), $_REQUEST['notes'][$i], $_REQUEST['count'][$i]);
    }
    $prodcount = 0;
    $itemcount = 0;
    $updatedkeys = array();
    foreach ($_SESSION['listitemsstore'] as $key => $value) {
        if ($LIST_NON_ZERO AND floatval($listitemsstore[$key][1]) == 0) {
            print "<p>ERROR: Amount must be non-zero.</p>";
            continue;
        }
        if ($_SESSION['listitemsstore'][$key][4] != $listitemsstore[$key][4]) {
            $prev = intval($_SESSION['listitemsstore'][$key][4]);
            $new = intval($listitemsstore[$key][4]);
            $diff = $new - $prev;
            for ($i = 0; $i < $diff; $i++) {
                 //do inserts here
                 if (!copy_listitem_from_listitemid($db, $key)) {
                     print "<p>ERROR: 100 $i</p>";
                 } else {
                     $updatedkeys[$key] = '';
                 }
            }
            if ($diff < 0) {
                $diff = abs($diff);
                //do deletes here
                if (!delete_similar_listitems_from_listitemid($db, $key, $typelist, $diff)) {
                     print "<p>ERROR: 110</p>";
                } else {
                     $updatedkeys[$key] = '';
                }
            }
        }
        /*if ($_SESSION['listitemsstore'][$key][0] != $listitemsstore[$key][0]) {
            //print "<p>Updating productid $prodid.</p>";
            $prodcount += update_productname_by_listitemid($db, $listitemsstore[$key][0], $key);
        }*/
        if (sizeof(array_diff_assoc(array_slice($_SESSION['listitemsstore'][$key], 1, 3), array_slice($listitemsstore[$key], 1, 3))) > 0) {
            //print "<p>Updating listitem $key.</p>";
            if (!update_similar_listitems_from_listitemid($db, $key, $typelist, $listitemsstore[$key][1], $listitemsstore[$key][2], $listitemsstore[$key][3])) {
                print "<p>ERROR: 120</p>";
            } else {
                $updatedkeys[$key] = '';
            }
        }
    }
    //print "<p>Updated $prodcount product names in database.</p>";
    print "<p>Updated " . sizeof($updatedkeys) . " items in list.</p>";
}

// process delete form

if ($_REQUEST['submit'] == "Update") {
    $count = 0;
    foreach ($_REQUEST['cb'] as $value) {
        if (!delete_listitem_by_id($db, $value, $typelist)) {
            print "<p>ERROR: 130 $count</p>";
        } else {
            $count += 1;
        }
    }
    print "<p>Deleted $count items.</p>";
}


///////// process add form

$addupc = '';
$addsize = '';
$addunits = '';
$addprid = '';
$addprname = '';
$addnotes = '';
$addcount = 1;

if (isset($_REQUEST['add'])) {
    $addupc = $_REQUEST['upc'];
    $addsize = $_REQUEST['size'];
    $addunits = measure_unit_normalize($_REQUEST['units']);
    $addprid = $_REQUEST['productid'];
    $addprname = $_REQUEST['productname'];
    $addnotes = $_REQUEST['notes'];
    $errors = "";
    $result = array();

	if (empty($addprid) AND !empty($addprname)) {
                $addprid = add_product($db, $addprname);
                if ($addprid === NULL) {
                        print "<p>INSERT ERROR 10</p>";
                }
	}

        if (!empty($_REQUEST['upc'])) {
            $result = get_upc($db, $_REQUEST['upc']);
            $addupc = $result['upc'];
            if (empty($addsize)) {
	        $addsize = $result['size'];
            }
            if (empty($addunits)) {
                $addunits = measure_unit_normalize($result['units']);
            }
            //lookup productid here, use JOIN to validate that it exists
            if (empty($addprid)) {
                $addprid = get_productid_by_upc($db, $addupc);
            }
        }

    if(empty($addprid)) {
        $errors .= "<p>ERROR: No Generic Product name.</p>";
    }
    if ($LIST_NON_ZERO AND floatval($addsize) == 0) {
        $errors .= "<p>ERROR: Amount must be non-zero.</p>";
    }

    if (strlen($errors) == 0) {
        if (!empty($_REQUEST['count'])) {
            $addcount = intval($_REQUEST['count']);
        }

        add_update_item_by_upc($db, $addupc, $addprid, $result['itemname'], $result['size'], $result['units']);

        for ($i = 0; $i < $addcount; $i++) {
            if (!add_listitem($db, $id, $addprid, $addsize, $addunits, $addnotes, $type)) {
                print "<p>INSERT ERROR 20 $i</p>";
            }
        }

        $addupc = '';
        $addcount = 1;
        $addsize = '';
        $addunits = '';
        $addprid = '';
        $addprname = '';
        $addnotes = '';
        print "<p>Added product.</p>";
    } else {
        print $errors;
    }
}


///////////  add form

if ($create) {

print '<script type="text/javascript" src="include/cuecat.js"></script>';

$items = array();

$txt = "<select id='productid' name='productid'>\n";
$txt .= get_option("", "(new)");
foreach (get_products($db) as $row) {
    $txt .= get_option($row['productid'], $row['productname'], $addprid);
}
$txt .= "</select>";
$txt .= '<input id="newproductid" name="productname" maxlength="255" size="12" value="' . htmlspecialchars($addprname) . '" onkeyup="findit(\'productid\',this)" />';

$items[] = array("productid", "*Generic Product Name", $txt);
$items[] = array("count", "*Quantity", '<input id="count" name="count" maxlength="10" size="3" value="' . $addcount . '" />');
$items[] = array("size", "Amount", '<input id="size" name="size" maxlength="10" size="3" value="' . $addsize . '" />');
$items[] = array("units", "Units", '<input id="units" name="units" size="6" maxlength="255" value="' . $addunits . '" />');
$items[] = array("notes", "Notes", '<input id="notes" name="notes" size="20" maxlength="255" value="' . htmlspecialchars($addnotes) . '" />');
$items[] = array("upc", "Bar Code", '<input id="upc" name="upc" maxlength="255" size="15" value="' . $addupc . '" />' . get_upc_link(PageUrl()));

    form_gen($items, "<input type='hidden' name='id' value='$id' /><input type=\"submit\" name=\"add\" value=\"Add to Shopping List\" />");
}

ob_flush();

//////////////// table list 

$result = get_listitems_by_id($db, $id, $typelist);

print '<p>';
for ($i=65; $i < 65+26; $i++) {
    print '<a href="#' . chr($i)  . '">' . chr($i)  . '</a> ';
}
print '</p>';


print '<form method="post" action="">';
show_buttons($modify);
print '<table>';
print '<tr>';
//print '<th>Top</th>';
if ($delete) {
print '<th><input name="select" type="submit" value="';
if ($select == "Select All") {
    print "Select None";
} else {
    print "Select All";
}
print '" /></th>';
}
print '<th>Quantity</th><th>Generic Product Name</th><th>Amount</th><th>Units</th><th>Notes</th>';
print '</tr>';

$tempstore = array();
$nav = array();

$i = 0;
foreach ($result as $row) {
    $tempstore[$row['listitemid']] = array($row['productname'], $row['size'], $row['units'], $row['notes'], $row['count']);

    print '<tr>';
//    print '<td><a href="#top"><input type="button" value="/\" /></a></td>';
if ($delete) {
    print '<td><input name="cb[]" value="' . $row['listitemid'] . '" type="checkbox"';
    if ($select == "Select All") {
        print ' checked="checked"';
    }
    print ' />';
}
    print '<input name="listitemid[]" type="hidden" value="' . $row['listitemid'] . '" />';
    print '</td>';
    print '<td><input id="count' . $i . '" name="count[]" maxlength="10" size="3" value="' . $row['count'] . '" ' . $disabled . ' />';
if ($modify) {
    print '<script type="text/javascript">incrementor("count' . $i . '");</script></td>';
}

    $navid = strtoupper($row['productname'][0]);
    if (!in_array($navid, $nav)) {
        print '<td><a id="' . $navid . '" href="editproduct.php?id=' . $row['productid'] . '">' . htmlspecialchars($row['productname']) . '</a></td>';
        $nav[] = $navid;
    } else {
        print '<td><a href="editproduct.php?id=' . $row['productid'] . '">' . htmlspecialchars($row['productname']) . '</a></td>';
    }
    print '<td><input name="size[]" maxlength="10" size="3" value="' . $row['size'] . '" ' . $disabled . ' /></td>';
    print '<td><input name="units[]" maxlength="255" size="6" value="' . $row['units'] . '" ' . $disabled . ' /></td>';
    print '<td><input name="notes[]" maxlength="255" value="' . htmlspecialchars($row['notes']) . '" ' . $disabled . ' /></td>'; 
    print '</tr>';
    print "\n";
    $i++;
}
print '</table>';

show_buttons($modify);
show_buttons($modify, TRUE);

print "<p><input type='hidden' name='id' value='$id' />";
print '</p></form>';


$_SESSION['listitemsstore'] = $tempstore;

$db = NULL;

}

include_once($WEBROOT . "/include/footer.php");

?>
