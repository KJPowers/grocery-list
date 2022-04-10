Grocery List Install instructions


I. Install Apache, MySQL, and PHP
	I have configured PHP with "output_buffering = 4096"
	Be sure that you have PHP XML-RPC support installed or compiled into PHP for UPC support. http://us.php.net/xmlrpc
II. Copy all of the grocery-list *.php files to the apache server's www or htdocs folder.
III. Configure your glist database in mysql
	1. bring up the mysql prompt
	2. type the following command

	mysql> create database glist;

	3. add a new user named 'user' (or whatever you want) with the following command...

	mysql> GRANT ALL PRIVILEGES ON glist.* TO 'user'@'localhost' IDENTIFIED BY 'password';
	
	4. Select the database and build any missing tables.

	mysql> use glist;
	mysql> source glist.sql;

	5. Set the database settings in your own include/config.php file to match step 3.

IV. Navigate your webrowser to where you installed the program.

V. For UPC barcode support. Both http://www.upcdatabase.com/ and http://www.eandata.com require you to register for a key.
Get the key from the website.  In your include/config.php set:

$UPC_DBS = array('upcdatabasexml');
define('UPCDBXMLKEY', 'super-secret-key-goes-here');

// OR //

$UPC_DBS = array('eandata');
define('EANDATAKEY', 'super-secret-key-goes-here');
