<?php
/**
 * ./bblog/bBlog_plugins/function.email.php
 *
 * @package default
 */


// function.header.php
//
// Written by Tobias Schlottke
//
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
function identify_function_email() {
	$help = 'usage: <br/>
        {email email=\'somone@example.com\' name=\'john doe\'} <br/>
        or just<br/>
        {email email=\'somone@example.com\'} <br/>';

	return array (
		'name'           =>'email',
		'type'             =>'function',
		'nicename'     =>'Email',
		'description'   =>'encodes email addresses to get rid of spam bots',
		'authors'        =>'Tobias Schlottke <tschlottke@virtualminds.de>',
		'licence'         =>'GPL',
		'help'   => $help
	);
}


/**
 *
 *
 * @param unknown $params
 * @param unknown $bBlog  (reference)
 */
function smarty_function_email($params, &$bBlog) {

	extract($params);
	if (!$name) $name = str_replace(".", " dot ", str_replace("@", " at ", $email));
	$email = preg_replace("/\"/", "\\\"", $email);
	$old = "document.write('<a href=\"mailto:$email\">$name</a>')";

	$output = "";
	for ($i=0; $i < strlen($old); $i++) {
		$output = $output . '%' . bin2hex(substr($old, $i, 1));
	}

	echo "<script language=\"JavaScript\" type=\"text/javascript\">eval(unescape('".$output."'))</script>";
}
