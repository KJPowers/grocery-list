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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/upcdata.php $
# Last Updated: $Date: 2010-03-07 15:23:54 -0800 (Sun, 07 Mar 2010) $
# Author(s): Neil McNab
#
# Description:
#   Interface to isbndb.com web service.
#
########################################################################

if (!defined('ISBNDBKEY')) {
    // set your key here
    //define('ISBNDBKEY', '');
}

function isbndb_lookup($upc) {
    $result = array();

    if (!defined('ISBNDBKEY')) {
        return FALSE;
    }

    $url = sprintf("http://isbndb.com/api/books.xml?access_key=%s&index1=isbn&value1=%s", ISBNDBKEY, $upc);

    $xml = simplexml_load_string(isbndb_http_get($url));
    $book = $xml->BookList->BookData;
     foreach($book->attributes() as $a => $b) {
        if ($a == 'isbn13') {
            $result['upc'] = strval($b);
        }
    }
    $result['description'] = strval($book->Title);

    return $result;
}

function isbndb_http_get($request) {
  $file = FALSE;
  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $file = curl_exec($ch);
    if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) !== 200 ) {
      return FALSE;
      //print 'Bad Data File '. RPC_URL . " " . curl_getinfo($ch,CURLINFO_HTTP_CODE); # . " " . $file);
    }
  } else {
  // PHP built-in method, not secure, using as failback only
    $file = file_get_contents($request);
  }
  return $file;
}


?>


