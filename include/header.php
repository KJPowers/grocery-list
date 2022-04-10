<?php
########################################################################
#
# Project: Grocery List
# URL: http://sourceforge.net/projects/grocery-list/
# E-mail: neil@nabber.org
#
# Copyright: (C) 2010-2012, Neil McNab
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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/header.php $
# Last Updated: $Date: 2012-02-03 00:26:11 -0500 (Fri, 03 Feb 2012) $
# Author(s): Neil McNab
#
# Description:
#   HTML header code.
#
########################################################################

include_once('site.php');
print '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <title><?php print $TITLE; ?> / <?php print $title; ?></title>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" media="all" href="include/grocery.css" type="text/css" />
  <link rel="stylesheet" media="print" href="include/print.css" type="text/css" />
  <link rel="stylesheet" media="only screen and (max-width: 640px)" href="include/handheld.css" type="text/css" />
  <link rel="stylesheet" media="handheld" href="include/handheld.css" type="text/css" />
  <?php echo $head; ?>
</head>
<body onload="<?php print $onload; ?>">

<div class="menu">
<?php include('menu.php'); ?>
</div>

<div class="content">

<?php
if ($DEMO) {
    print "<p>USING DEMO MODE: CHANGES WILL BE OVERWRITTEN!</p>";
}
?>

<h1><?php print $TITLE; ?></h1>

<p><?php print $title; ?></p>

