<?php
/**
 * ./bblog/bBlog_plugins/modifier.smartypost.php
 *
 * @package default
 */


// modifier.smartypost.php - processes smarty tags embedded in posts
// Copyright (C) 2003  Mario Delgado <mario@seraphworks.com>
// modifier.smartypost.php - a plug-in written for bBlog Weblog
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
function identify_modifier_smartypost() {

	$help='<br>Use the smartypost modifier on the {$post.body} tag,<br>
           to process any Smarty tags you have embedded in a post.<br><br>
           Example :
           <ul>
               <li>{$post.body|smartypost}</li>
           </ul> Smarty Post can be used with other modifiers.<br><br>
           Example :
           <ul>
               <li>{$post.body|readmore:$post.postid|smartypost}</li>
           </ul>';

	return array (
		'name'          =>'smartypost',
		'type'          =>'smarty_modifier',
		'nicename'      =>'Smarty Post',
		'description'   =>'Processes Smarty tags in a post',
		'authors'       =>'Mario Delgado <mario@seraphworks.com>',
		'licence'       =>'GPL',
		'help'       =>$help
	);

}


/**
 *
 *
 * @param unknown $text
 * @return unknown
 */
function smarty_modifier_smartypost($text) {

	global $bBlog;
	$bBlog->assign('smartied_post', $text);
	// we will store the smartypost template in the inc/admin_template dir, becasue almost noone will need to change it, - reduce clutter in the templates/* directory.
	$tmptemplatedir = $bBlog->template_dir;
	$tmpcompileid = $bBlog->compile_id;
	$bBlog->template_dir = BBLOGROOT.'inc/admin_templates';
	$bBlog->compile_id = 'admin';
	$output = $bBlog->fetch('smartypost.html');
	$bBlog->template_dir = $tmptemplatedir;
	$bBlog->compile_id = $tmpcompileid;


	return $output;

}


?>
