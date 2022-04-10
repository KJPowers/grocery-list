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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/cuecat.php $
# Last Updated: $Date: 2010-04-04 16:35:47 -0400 (Sun, 04 Apr 2010) $
# Author(s): Neil McNab
#
# Description:
#   Cuecat decode to normal UPC functions.
#
########################################################################

/*
http://osiris.978.org/~brianr/cuecat/files/cuecat-0.0.8/SUPPORTED_BARCODES

http://www.accipiter.org/projects/cat.php

http://www.rkgage.net/bobby/cuecat/decode2.htm

*/

include_once('upc.php');

function cuecat_decode($ccstr) {
	$ccparts = explode(".", $ccstr);
	$upcstr = "";
	if ('fHmc' == $ccparts[2]) {
		// decode UPC-A
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 0, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 4, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 8, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 12, 4));
	} elseif ('fHmg' == $ccparts[2]) {
		// decode UPC-E
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 0, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 4, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 8, 2));
                $upcstr = compute_check_digit($upcstr . 'X');
	} elseif ('cGen' == $ccparts[2]) {
		// decode EAN-13
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 0, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 4, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 8, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 12, 4));
		$upcstr .= cuecat_decode_block(substr($ccparts[3], 16, 4));
	}

	return $upcstr;
}

function cuecat_lookup($char) {
    $lookupstr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+-';
    return strpos($lookupstr, $char);
}

function cuecat_decode_block($chars) {
    $retchars = '';
    $one = cuecat_lookup($chars[0]);
    $two = cuecat_lookup($chars[1]);
    $temp = $one << 2;
    $temp2 = $two >> 4;
    $result = $temp | $temp2;
    $result ^= 3;
    $result += 64;
    $result %= 128;
    $retchars .= chr($result);
    if (strlen($chars) > 2) {
        $twoshift = $two << 4;
        $three = cuecat_lookup($chars[2]);
        $threeshift = $three >> 2;
        $result = $twoshift | $threeshift;
        $result ^= 3;
        $result += 64;
        $result %= 128;
        $retchars .= chr($result);
    }
    if (strlen($chars) > 3) {
        $threeshift = $three << 6;
        $four = cuecat_lookup($chars[3]);
        $result = $four | $threeshift;
        $result ^= 3;
        $result += 64;
        $result %= 128;
        $retchars .= chr($result);
    }
    return $retchars;
}

// known test vector = WPT39 
//print cuecat_decode(".C3nZC3nZC3nYD3b6ENnZCNnY.fHmc.fbmxChO.");

//UPC-A
//print cuecat_decode(".C3nZC3nZC3nYD3b6ENnZCNnY.fHmc.C3D1Dxr2C3nZE3n7.");
//UPC-E
//print cuecat_decode(".C3nZC3nZC3nXC3v2Dhz6C3nX.fHmg.C3T0CxrYCW.");
//print cuecat_decode(".C3nZC3nZC3nXC3v2Dhz6C3nX.fHmg.C3bZDhr2CW.");
//EAN-13/ISBN-13
//print cuecat_decode(".C3nZC3nZC3nXC3v2Dhz6C3nX.cGen.ENr7C3n1C3T6Chf7Dq.");

?>
