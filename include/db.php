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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/db.php $
# Last Updated: $Date: 2011-02-06 12:36:47 -0500 (Sun, 06 Feb 2011) $
# Author(s): Neil McNab
#
# Description:
#   Database access functions.
#
########################################################################

include_once("config.defaults.php");
include_once("acl.php");

function dbconnect() {
    global $DBDSN, $DBUSER, $DBPASS;
    try {
        $connection=new PDO($DBDSN,$DBUSER,$DBPASS);
    }
    catch(PDOException $e) {
//            echo 'Error : '.$e->getMessage();
            return NULL;
    }
    return $connection;
}

function db_demo() {
    $db = dbconnect();
    global $DEMO, $DEMO_RESET_TIME;
    if ($DEMO) {
        $db->exec("CREATE TABLE IF NOT EXISTS `demo` ( 
		`id` int(10) UNSIGNED NOT NULL,
		`timestamp` TIMESTAMP,
		PRIMARY KEY (`id`)
		)");

        $timestamp = intval(strtotime(dboneshot($db, "SELECT timestamp from demo")));
        if ($timestamp + $DEMO_RESET_TIME < time()) {
            // reset database here
            $contents = explode(";", file_get_contents('demo.sql'));
            foreach ($contents as $line) {
                $db->exec($line);
            }
            // update timestamp here
            $db->exec("DELETE FROM demo");
            $db->exec("INSERT INTO demo SET timestamp=NULL");
        }
    }
    $db = NULL;
}

function get_view_shop_by_listid($db, $id, $type) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    $q = "SELECT *,count(*) as count, count(*)*size as total from listitems LEFT JOIN products USING (productid) WHERE listitems.acctid=%s AND listid=%s AND type=%s GROUP BY productid, units, size";
    $query = sprintf($q, $db->quote($GLIST_ACCT_ID), $db->quote($id), $db->quote($type));
    return $db->query($query);
}

function get_view_baseline_by_listid($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    $q = "SELECT listitemid,productname,productid,MAX(size) as size,units,notes,MIN(size) as minsize FROM listitems LEFT JOIN products USING (productid) WHERE products.acctid=%s AND listitems.acctid=%s AND listid=%s AND (type='fullinventory' OR type='mininventory') GROUP BY listitems.productid ORDER BY productname";
    $query = sprintf($q, $db->quote($GLIST_ACCT_ID), $db->quote($GLIST_ACCT_ID), $db->quote($id));
    $result = $db->query($query);
    return $result;
}

function exist_item_by_upc($db, $upc) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return dbexist($db, sprintf("SELECT count(*) FROM items WHERE acctid=%s AND upc=%s", $db->quote($GLIST_ACCT_ID), $db->quote($upc)));
}

function get_similar_where_from_listitemid($db, $listitemid, $typelist) {
    global $GLIST_ACCT_ID;
    $result = $db->query(sprintf("SELECT * FROM listitems WHERE acctid=%s AND listitemid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($listitemid)))->fetch();
    $uniq = sprintf("acctid=%s AND productid=%s AND listid=%s AND units=%s AND size=%s AND type IN ('%s')", $db->quote($GLIST_ACCT_ID), $db->quote($result['productid']), $db->quote($result['listid']), $db->quote($result['units']), $db->quote($result['size']), implode("','", $typelist));
    return $uniq;
}

function get_list_by_listid($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return $db->query(sprintf("SELECT * FROM lists WHERE acctid=%s AND listid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
}

function get_product_by_productid($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return $db->query(sprintf("SELECT * FROM products WHERE acctid=%s AND productid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
}

function get_item_by_itemid($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return $db->query(sprintf("SELECT * FROM items WHERE items.acctid=%s AND itemid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
}

function get_item_by_upc($db, $upc) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return $db->query(sprintf("SELECT * FROM items WHERE acctid=%s AND upc=%s", $db->quote($GLIST_ACCT_ID), $db->quote($upc)))->fetch();
}

function get_products($db, $sort='productname') {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return $db->query(sprintf("SELECT * FROM products WHERE acctid=%s ORDER BY %s", $db->quote($GLIST_ACCT_ID), $sort));
}

function get_items($db, $sort='itemname') {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return $db->query(sprintf("SELECT * FROM items WHERE acctid=%s ORDER BY %s", $db->quote($GLIST_ACCT_ID), $sort));
}

function get_items_with_products($db, $sort='itemname') {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    $q = "SELECT * FROM items LEFT JOIN products USING (productid) WHERE items.acctid=%s AND products.acctid=%s ORDER BY %s";
    $query = sprintf($q, $db->quote($GLIST_ACCT_ID), $db->quote($GLIST_ACCT_ID), $sort);
    return $db->query($query);
}

function get_lists($db, $sort='listname') {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return $db->query(sprintf("SELECT * FROM lists WHERE acctid=%s ORDER BY %s", $db->quote($GLIST_ACCT_ID), $sort));
}

function get_listitems_by_id($db, $id, $typelist) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    $text = "SELECT *,count(*) as count FROM listitems LEFT JOIN products USING (productid) WHERE products.acctid=%s AND listitems.acctid=%s AND listid='%s' AND type IN ('%s') GROUP BY productid, size, units ORDER BY productname";
    $query = sprintf($text, $db->quote($GLIST_ACCT_ID), $db->quote($GLIST_ACCT_ID), $id, implode("','", $typelist));
    return $db->query($query);
}

function get_productid_by_upc($db, $upc) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    if (empty($upc))
        return NULL;
    $text = "SELECT productid FROM items LEFT JOIN products USING (productid) WHERE items.acctid=%s AND products.acctid=%s AND upc=%s";
    $query = sprintf($text, $db->quote($GLIST_ACCT_ID), $db->quote($GLIST_ACCT_ID), $db->quote($upc));
    return dboneshot($db, $query);
}

function get_product_by_upc($db, $upc) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    if (empty($upc))
        return NULL;
    $text = "SELECT * FROM items LEFT JOIN products USING (productid) WHERE items.acctid=%s AND products.acctid=%s AND upc=%s";
    $query = sprintf($text, $db->quote($GLIST_ACCT_ID), $db->quote($GLIST_ACCT_ID), $db->quote($upc));
    return $db->query($query);
}

function get_listname_from_id($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_read($GLIST_ACCT_ID))
        return NULL;
    return dboneshot($db, sprintf("SELECT listname FROM lists WHERE acctid=%s AND listid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
}

function add_product($db, $name, $priority='item', $expiration='') {
    global $PRODUCT_LIMIT;
    global $GLIST_ACCT_ID;
    if (!acl_create($GLIST_ACCT_ID))
        return NULL;
    $count = dboneshot($db, "SELECT count(*) FROM products WHERE acctid=" . $db->quote($GLIST_ACCT_ID));
    if (intval($count) >= $PRODUCT_LIMIT) {
        print "<p>ERROR: You are limited to $PRODUCT_LIMIT products.</p>";
        return NULL;
    }
    $db->exec(sprintf("INSERT INTO products SET productname=%s,priority=%s,expiration=%s,acctid=%s", $db->quote($name),  $db->quote($priority),  $db->quote($expiration), $db->quote($GLIST_ACCT_ID)));
    $query = sprintf("SELECT productid FROM products WHERE acctid=%s AND productname=%s", $db->quote($GLIST_ACCT_ID), $db->quote($name));
    return dboneshot($db, $query);
}

function add_item($db, $productid, $itemname='', $size='', $units='', $upc='', $priority='') {
    global $ITEM_LIMIT;
    global $GLIST_ACCT_ID;
    if (!acl_create($GLIST_ACCT_ID))
        return NULL;
    $count = dboneshot($db, "SELECT count(*) FROM items WHERE acctid=" . $db->quote($GLIST_ACCT_ID));
    if (intval($count) >= $ITEM_LIMIT) {
        print "<p>ERROR: You are limited to $ITEM_LIMIT products.</p>";
        return NULL;
    }
    $query = sprintf("INSERT INTO items SET upc=%s,itemname=%s,size=%s,units=%s,productid=%s,priority=%s,acctid=%s", $db->quote($upc), $db->quote($itemname),  $db->quote($size), $db->quote($units), $db->quote($productid), $db->quote($priority), $db->quote($GLIST_ACCT_ID));
    $db->exec($query);
    return dboneshot($db, sprintf("SELECT itemid FROM items WHERE acctid=%s AND itemname=%s", $db->quote($GLIST_ACCT_ID), $db->quote($itemname)));
}

function add_listitem($db, $listid, $productid, $size='', $units='', $notes='', $type='') {
// TODO add count check
    global $LISTITEM_LIMIT;
    global $GLIST_ACCT_ID;
    if (!acl_create($GLIST_ACCT_ID))
        return NULL;
    $count = dboneshot($db, "SELECT count(*) FROM listitems WHERE acctid=" . $db->quote($GLIST_ACCT_ID));
    if (intval($count) >= $LISTITEM_LIMIT) {
        print "<p>ERROR: You are limited to $LISTITEM_LIMIT list items.</p>";
        return NULL;
    }
    $query = sprintf("INSERT INTO listitems SET listid=%s,productid=%s,size=%s,units=%s,notes=%s,type=%s,acctid=%s", $db->quote($listid), $db->quote($productid), $db->quote($size), $db->quote($units),  $db->quote($notes), $db->quote($type), $db->quote($GLIST_ACCT_ID));
    $db->exec($query);
    return dboneshot($db, sprintf("SELECT listitemid FROM listitems WHERE listid=%s AND productid=%s AND size=%s AND units=%s AND notes=%s AND type=%s AND acctid=%s", $db->quote($listid), $db->quote($productid), $db->quote($size), $db->quote($units),  $db->quote($notes), $db->quote($type), $db->quote($GLIST_ACCT_ID)));
}

function add_list($db, $listname) {
    global $LIST_LIMIT;
    global $GLIST_ACCT_ID;
    if (!acl_create_list($GLIST_ACCT_ID))
        return NULL;
    $count = dboneshot($db, "SELECT count(*) FROM lists WHERE acctid=" . $db->quote($GLIST_ACCT_ID));
    if (intval($count) >= $LIST_LIMIT) {
        print "<p>ERROR: You are limited to $LIST_LIMIT lists.</p>";
        return NULL;
    }
    $db->exec(sprintf("INSERT INTO lists SET acctid=%s,listname=%s", $db->quote($GLIST_ACCT_ID), $db->quote($listname)));
    return dboneshot($db, sprintf("SELECT listid FROM lists WHERE acctid=%s AND listname=%s", $db->quote($GLIST_ACCT_ID), $db->quote($listname)));
}

function update_productname_by_listitemid($db, $productname, $listitemid) {
    global $GLIST_ACCT_ID;
    if (!acl_modify($GLIST_ACCT_ID))
        return NULL;
    // TODO later, optimize this query
    $prodid = dboneshot($db, sprintf("SELECT productid FROM listitems WHERE acctid=%s AND listitemid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($listitemid)));
    return $db->exec(sprintf("UPDATE products SET productname=%s WHERE productid=%s AND acctid=%s", $db->quote($productname), $db->quote($prodid), $db->quote($GLIST_ACCT_ID)));
}

function update_similar_listitems_from_listitemid($db, $listitemid, $typelist, $size, $units, $notes) {
    global $GLIST_ACCT_ID;
    if (!acl_modify($GLIST_ACCT_ID))
        return NULL;
    $count = intval($count);
    $text = sprintf("size=%s,units=%s,notes=%s", $db->quote($size), $db->quote(measure_unit_normalize($units)), $db->quote($notes));
    $result = $db->query(sprintf("SELECT * FROM listitems WHERE acctid=%s AND listitemid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($listitemid)))->fetch();
    $uniq = sprintf("productid=%s AND listid=%s AND type IN ('%s') AND acctid=%s", $db->quote($result['productid']), $db->quote($result['listid']), implode("','", $typelist), $db->quote($GLIST_ACCT_ID));
    $query = "UPDATE listitems SET $text WHERE $uniq";
    return $db->exec($query);
}

function add_update_item($db, $id, $productid, $name='', $size='', $units='', $upc='', $priority='') {
        if (empty($id)) {
            // do insert
            return add_item($db, $productid, $name, $size, $units, $upc, $priority);
        } else {
            // do update
            global $GLIST_ACCT_ID;
            if (!acl_modify($GLIST_ACCT_ID))
                return NULL;
            if($db->exec(sprintf("UPDATE items SET upc=%s,itemname=%s,size=%s,units=%s,productid=%s,priority=%s WHERE acctid=%s AND itemid=%s", $db->quote($upc), $db->quote($name),  $db->quote($size), $db->quote($units), $db->quote($productid), $db->quote($priority), $db->quote($GLIST_ACCT_ID), $db->quote($id)))) {
                return $id;
            }
        }
    return NULL;
}

function add_update_item_by_upc($db, $upc, $productid, $name='', $size='', $units='', $priority='') {
        if (!exist_item_by_upc($db, $upc)) {
            // do insert
            return add_item($db, $productid, $name, $size, $units, $upc, $priority);
        } else {
            // do update
            global $GLIST_ACCT_ID;
            if (!acl_modify($GLIST_ACCT_ID))
                return NULL;
            if($db->exec(sprintf("UPDATE items SET itemname=%s,size=%s,units=%s,productid=%s,priority=%s WHERE acctid=%s AND upc=%s", $db->quote($name),  $db->quote($size), $db->quote($units), $db->quote($productid), $db->quote($priority), $db->quote($GLIST_ACCT_ID), $db->quote($upc)))) {
                return $id;
            }
        }
    return NULL;
}

function add_update_list($db, $id, $listname) {
       if (empty($id)) {
            // do insert
            return add_list($db, $listname);
        } else {
            // do update
            global $GLIST_ACCT_ID;
            if (!acl_modify_list($GLIST_ACCT_ID))
                return NULL;
            if($db->exec(sprintf("UPDATE lists SET listname=%s WHERE acctid=%s AND listid=%s", $db->quote($listname), $db->quote($GLIST_ACCT_ID), $db->quote($id)))) {
                return $id;
            }
        }
    return NULL;
}

function add_update_product($db, $id, $name, $priority='item', $expiration='') {
        if (empty($id)) {
            // do insert
            return add_product($db, $name, $priority, $expiration);
        } else {
            // do update
            global $GLIST_ACCT_ID;
            if (!acl_modify($GLIST_ACCT_ID))
                return NULL;
            if($db->exec(sprintf("UPDATE products SET productname=%s,priority=%s,expiration=%s WHERE acctid=%s AND productid=%s", $db->quote($name), $db->quote($priority),$db->quote($expiration), $db->quote($GLIST_ACCT_ID), $db->quote($id)))) {
                return $id;
            }
        }
    return NULL;
}

function copy_listitem_from_listitemid($db, $listitemid, $count=1) {
    global $GLIST_ACCT_ID;
    if (!acl_create($GLIST_ACCT_ID))
        return NULL;
    $count = intval($count);
    $query = sprintf("INSERT INTO listitems SELECT NULL,listid,productid,size,units,type,notes,NULL,acctid FROM listitems WHERE acctid=%s AND listitemid=%s LIMIT %s", $db->quote($GLIST_ACCT_ID), $db->quote($listitemid), $count);
    return $db->exec($query);
}

function delete_listitem_by_id($db, $id, $typelist) {
    global $GLIST_ACCT_ID;
    if (!acl_delete($GLIST_ACCT_ID))
        return NULL;
    $query = sprintf("SELECT * FROM listitems WHERE acctid=%s AND listitemid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id));
    $result = $db->query($query)->fetch();
    $uniq = sprintf("productid=%s AND listid=%s AND units=%s AND type IN ('%s') AND acctid=%s", $db->quote($result['productid']), $db->quote($result['listid']), $db->quote($result['units']), implode("','", $typelist), $db->quote($GLIST_ACCT_ID));
    return $db->exec("DELETE FROM listitems WHERE $uniq");
}

function deleteone_listitem_by_productid($db, $id, $listid, $typelist, $size=NULL, $units=NULL) {
    global $GLIST_ACCT_ID;
    if (!acl_delete($GLIST_ACCT_ID))
        return NULL;
    $uniq = sprintf("productid=%s AND listid=%s AND type IN ('%s') AND acctid=%s AND size=%s AND units=%s", $db->quote($id), $db->quote($listid), implode("','", $typelist), $db->quote($GLIST_ACCT_ID), $db->quote($size), $db->quote($units));
    return $db->exec("DELETE FROM listitems WHERE $uniq LIMIT 1");
}

function delete_listitem_by_productid($db, $id, $listid, $typelist) {
    global $GLIST_ACCT_ID;
    if (!acl_delete($GLIST_ACCT_ID))
        return NULL;
    $uniq = sprintf("productid=%s AND listid=%s AND type IN ('%s') AND acctid=%s", $db->quote($id), $db->quote($listid), implode("','", $typelist), $db->quote($GLIST_ACCT_ID));
    return $db->exec("DELETE FROM listitems WHERE $uniq");
}

function delete_list_by_id($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_delete_list($GLIST_ACCT_ID))
        return NULL;
    $db->exec(sprintf("DELETE FROM lists WHERE acctid=%s AND listid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
    $db->exec(sprintf("DELETE FROM listitems WHERE acctid=%s AND listid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
}

function delete_product_by_id($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_delete($GLIST_ACCT_ID))
        return NULL;
    $db->exec(sprintf("DELETE FROM products WHERE acctid=%s AND productid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
    $db->exec(sprintf("DELETE FROM listitems WHERE acctid=%s AND productid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
}

function delete_item_by_id($db, $id) {
    global $GLIST_ACCT_ID;
    if (!acl_delete($GLIST_ACCT_ID))
        return NULL;
    return $db->exec(sprintf("DELETE FROM items WHERE acctid=%s AND itemid=%s", $db->quote($GLIST_ACCT_ID), $db->quote($id)));
}

function delete_similar_listitems_from_listitemid($db, $listitemid, $typelist, $count=1) {
    global $GLIST_ACCT_ID;
    if (!acl_delete($GLIST_ACCT_ID))
        return NULL;
    $count = intval($count);
    $uniq = get_similar_where_from_listitemid($db, $listitemid, $typelist);
    return $db->exec("DELETE FROM listitems WHERE $uniq LIMIT " . $count);
}

function delete_similar_listitems_from_ids($db, $listid, $productid, $typelist) {
    global $GLIST_ACCT_ID;
    if (!acl_delete($GLIST_ACCT_ID))
        return NULL;
    return $db->exec(sprintf("DELETE FROM listitems WHERE productid=%s AND listid=%s AND type IN ('%s') AND acctid=%s", $db->quote($productid), $db->quote($listid), $db->quote(implode("','", $typelist)), $db->quote($GLIST_ACCT_ID)));
}

function dbexist($db, $query) {
	if (dboneshot($db, $query) == 0) {
		return FALSE;
	}
	return TRUE;
}

function dboneshot($db, $query) {
	$tempresult = $db->query($query);
        if (empty($tempresult))
            return NULL;
        if ($tempresult->rowCount() == 0)
            return NULL;
	$result = $tempresult->fetch();
	return $result[0];
}

?>
