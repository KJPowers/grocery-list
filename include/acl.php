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
# Filename: $URL: https://grocery-list.svn.sourceforge.net/svnroot/grocery-list/trunk/include/acl.php $
# Last Updated: $Date: 2011-01-28 00:51:52 -0500 (Fri, 28 Jan 2011) $
# Author(s): Neil McNab
#
# Description:
#   Dummy ACL functions.
#
########################################################################

function acl_read($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

function acl_modify($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

function acl_create($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

function acl_delete($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

function acl_delete_list($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

function acl_modify_list($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

function acl_create_list($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

function acl_setpermissions($acctid) {
    if (empty($acctid))
        return FALSE;
    return TRUE;
}

?>
