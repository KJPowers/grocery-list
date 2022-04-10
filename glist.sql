-- MySQL dump 10.11
-- Generated with: mysqldump -d --database glist -u root > glist.sql
--
-- Host: localhost    Database: glist
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny2
--
-- Table structure for table `groceries`
--

CREATE TABLE IF NOT EXISTS `upccache` (
  `upc` bigint(13) UNSIGNED NOT NULL,
  `itemname` varchar(255) default NULL,
  `sizeweight` varchar(255) default NULL,
  `timestamp` TIMESTAMP,
  PRIMARY KEY (`upc`)
);

CREATE TABLE IF NOT EXISTS `items` (
  `itemid` int(10) UNSIGNED NOT NULL auto_increment,
  `acctid` int(10) UNSIGNED NOT NULL,
  `upc` bigint(13) UNSIGNED default NULL,
  `productid` int(10) UNSIGNED NOT NULL,
  `itemname` varchar(255) default NULL,
  `size` double UNSIGNED default NULL,
  `units` varchar(255) default NULL,
  `priority` smallint(5) UNSIGNED default 0,
  `timestamp` TIMESTAMP,
  PRIMARY KEY (`itemid`)
);

/* these tables aren't being used yet, we won't add them until they are
CREATE TABLE IF NOT EXISTS `vendors` (
  `acctid` int(10) UNSIGNED NOT NULL,
  `vendorid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendorname` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`vendorid`)
);

CREATE TABLE IF NOT EXISTS `vendormap` (
  `acctid` int(10) UNSIGNED NOT NULL,
  `itemid` int(10) UNSIGNED NOT NULL,
  `vendorid` int(10) UNSIGNED NOT NULL,
  `cost` DECIMAL UNSIGNED default NULL,
  PRIMARY KEY (`itemid`,`vendorid`)
);
*/

CREATE TABLE IF NOT EXISTS `products` (
  `productid` int(10) UNSIGNED NOT NULL auto_increment,
  `productname` varchar(255) NOT NULL,
  `expiration` int(10) UNSIGNED default NULL,
  `timestamp` TIMESTAMP,
  `prioritytype` ENUM('cost','item') NOT NULL DEFAULT 'item',
  `acctid` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`productid`)
);

CREATE TABLE IF NOT EXISTS `lists` (
  `listid` int(10) UNSIGNED NOT NULL auto_increment,
  `listname` varchar(255) NOT NULL,
  `acctid` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`listid`)
);

CREATE TABLE IF NOT EXISTS `listitems` (
  `listitemid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listid` int(10) UNSIGNED NOT NULL,
  `productid` int(10) UNSIGNED NOT NULL,
  `size` double UNSIGNED default NULL,
  `units` varchar(255) default NULL,
  `type` ENUM('tobuy','instock','fullinventory','mininventory','ordered') NOT NULL,
  `notes` varchar(255) default NULL,
  `timestamp` TIMESTAMP,
  `acctid` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`listitemid`)
);
