<?php
########################################################################
#
# Project: Grocery List
# URL: http://sourceforge.net/projects/grocery-list/
# E-mail: neil@nabber.org
#
# Copyright: (C) 2011-2012, Neil McNab
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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/upcdb.php $
# Last Updated: $Date: 2010-03-07 13:12:00 -0800 (Sun, 07 Mar 2010) $
# Author(s): Neil McNab
#
# Description:
#   Interfaces with www.upcdatabase.com new XML-RPC API to get UPC info.
#
########################################################################


if (!defined('UPCDBXMLKEY')) {
    // set your 40 character hex key here
    // you need to get your key from http://www.upcdatabase.com/
    //define('UPCDBXMLKEY', '');
}

/*

Methods now are passed one struct.  Required parameters are passed as
members of the struct.  Key values are case-sensitive.

The following fields are shared with multiple methods, and are described
here for clarity:

rpc_key: your assigned RPC key
upc: a 12-digit string (not a number/integer)
ean: a 13-digit string (not a number/integer)

You can obtain an RPC key through your "Account Info" page on the
web site.

Return values will always be a struct.  Certain keys will be present in
every response:  The 'status' field will contain a one-word description
of the result, basically indicating success or failure.  The 'message'
field will give more detail about the status of the request.

help
	Show available methods and their parameters.
	(what you're looking at now)

lookup
	Lookup upc database entry
	Required parameters:
		rpc_key
		Either ean or upc, but not both

! may not be complete, max limited to 15 for now
search
	Full text search on item database
	Required parameters:
		rpc_key
		search (string)
	Optional parameters:
		max_results
	Returns 5 results by default, up to 100 max.
	Search terms must be separated by whitespace,
		and must be at least 3 characters each.

! may not be complete, but should be working
writeEntry
	Add or modify an entry in the database.
	Required parameters:
		rpc_key
		Either ean or upc, but not both
		description
	Optional parameters:
		size

calculateCheckDigit
	Return full EAN13 given UPC or EAN without check digit
	Required parameters:
		rpc_key
		Either ean or upc, but not both
	In this case, ean must be 12 digits plus C or X,
		or upc must be 11 digits plus C or X

convertUPCE
	Return expanded UPC and EAN13 given UPC type E.
	Required parameters:
		rpc_key
		upce: an 8-digit string (not a number/integer)

decodeCueCat
	Decode CueCat scan.
	Required parameters:
		rpc_key
		CCstring: string read from a CueCat scanner

*/

define('XMLRPC_URL', 'http://www.upcdatabase.com/xmlrpc');

function upcxml_lookup($Item) {
  if (!defined('UPCDBXMLKEY')) {
      return FALSE;
  }

  $Item = trim($Item);

  if (!function_exists('xmlrpc_encode_request')) {
    print "<p>WARNING: Cannot do UPC lookups because <a href='http://us.php.net/xmlrpc'>XML-RPC</a> is not installed for PHP.</p>";
    return FALSE;
  }

  if (!is_numeric($Item)) {
    return TRUE;
  }
  $request = xmlrpc_encode_request('lookup', array('ean' => sprintf("%013d", intval($Item)), 'rpc_key' => UPCDBXMLKEY));

  $file = upcxml_http_post($request);

  if ($file === FALSE) {
    return FALSE;
  }

  $response = xmlrpc_decode($file);
  if ($response && xmlrpc_is_fault($response)) {
    trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
  } elseif (!$response) {
    print "<p>ERROR parsing response</p>";
  } else {
    return $response;
  }

    return FALSE;
}

function upcxml_http_post($request) {
  $file = FALSE;
  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, XMLRPC_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $file = curl_exec($ch);
    if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) !== 200 ) {
      return FALSE;
      //print 'Bad Data File '. RPC_URL . " " . curl_getinfo($ch,CURLINFO_HTTP_CODE); # . " " . $file);
    }
  } else {
  // PHP built-in method, not secure, using as failback only
    $context = stream_context_create(array('http' => array(
    'method' => "POST",
    'header' => "Content-Type: text/xml",
    'content' => $request
    )));
    $file = file_get_contents(XMLRPC_URL, false, $context);
  }
  return $file;

}

/*  these can be used for debugging

function upcxml_test() {
  if (!function_exists('xmlrpc_encode_request')) {
    print "<p>WARNING: Cannot do UPC lookups because <a href='http://us.php.net/xmlrpc'>XML-RPC</a> is not installed for PHP.</p>";
    return FALSE;
  }

  $request = xmlrpc_encode_request('test', "");

  $file = upcxml_http_post($request);

  if ($file === FALSE) {
    return FALSE;
  }

  $response = xmlrpc_decode($file);
  if ($response && xmlrpc_is_fault($response)) {
    trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
  } elseif (!$response) {
    print "<p>ERROR parsing response</p>";
  } else {
    return $response;
  }

    return FALSE;
}

function upcxml_help() {
  if (!function_exists('xmlrpc_encode_request')) {
    print "<p>WARNING: Cannot do UPC lookups because <a href='http://us.php.net/xmlrpc'>XML-RPC</a> is not installed for PHP.</p>";
    return FALSE;
  }

  $request = xmlrpc_encode_request('help', "");

  $file = upcxml_http_post($request);

  if ($file === FALSE) {
    return FALSE;
  }

  $response = xmlrpc_decode($file);
  if ($response && xmlrpc_is_fault($response)) {
    trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
  } elseif (!$response) {
    print "<p>ERROR parsing response</p>";
  } else {
    return $response['help'];
  }

    return FALSE;
}

print_r(upcxml_test())
print upcxml_help()

*/

//print_r(upcxml_lookup("036000123197"));

?>
