<?php

// Database settings
$DBUSER = 'user';
$DBPASS = 'password';
$DBDSN = 'mysql:host=127.0.0.1;dbname=glist';

// Security settings to prevent filling up the disk
// Limit the number of lists that can be created
$LIST_LIMIT=1000;
$PRODUCT_LIMIT=1000;
$ITEM_LIMIT=1000;
$LISTITEM_LIMIT=10000;
$UPCCACHE_LIMIT=10000;

// time until a UPC is looked up again in the database
$UPCCACHE_TIMEOUT=3600*24*30;

// use demo mode, database contents are reset periodically
$DEMO = FALSE;
$DEMO_RESET_TIME = 3600*3;

// Title to use for application
$TITLE = 'Grocery List';

// Account identifier, lets you have mutiple sets of lists, items, products, etc. in the same database
$GLIST_ACCT_ID = 1;

// email address to send the list to
$GLIST_EMAIL = "";

// email address where the list is sent from
$GLIST_FROM_EMAIL = "";

// use "advanced mode" with inventory options
$GLIST_INVENTORY_MODE = true;

// Set this value to your key for use with isbndb.com
//define('ISBNDBKEY', '');

// Not implemented yet

// Force lock between generic and item names (old behavior)
$GENERIC_LOCK = FALSE;

// Require compatible units between inventory and baseline (and list?)
$CHECK_INVENTORY_UNITS = TRUE;
$CHECK_LIST_UNITS = TRUE;

// Require non zero size for inventory
$INVENTORY_NON_ZERO = TRUE;

// Require non zero size for list
$LIST_NON_ZERO = TRUE;

// Show/hide notes (ability to override for the session)
$SHOW_NOTES = FALSE;

// read custom config options
@include('config.php');

?>
