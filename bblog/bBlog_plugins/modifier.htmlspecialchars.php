<?php
/**
 * ./bblog/bBlog_plugins/modifier.htmlspecialchars.php
 *
 * @package default
 */


/*
** bBlog Weblog http://www.bblog.com/
** Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/**
 *
 *
 * @return unknown
 */
function identify_modifier_htmlspecialchars() {
	return array (
		'name'           =>'htmlspecialchars',
		'type'             =>'smarty_modifier',
		'nicename'     =>'HTML Special Chars',
		'description'   =>'Converts HTML Special Chars to form-friendly entities',
		'authors'        =>'',
		'licence'         =>'',
		'help'     => ''
	);
}


/**
 *
 *
 * @param unknown $in
 * @return unknown
 */
function smarty_modifier_htmlspecialchars($in) {
	return htmlspecialchars($in);
}


?>
