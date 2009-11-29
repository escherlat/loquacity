<?php
/**
 * ./bblog/bBlog_plugins/function.getpost.php
 *
 * @package default
 */


// function.getposts.php
//
// Written by Reverend Jim <jim@revjim.net>
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
function identify_function_getpost() {
	$help = '
<p>The {getpost} function is used to retrieve a single post. It takes the following parameters:<br />
<br />
assign: variable to assign data to<br />
postid: to request a SINGLE post';

	return array (
		'name'           =>'getpost',
		'type'             =>'function',
		'nicename'     =>'GetPost',
		'description'   =>'Gets a single blog post',
		'authors'        =>'Reverend Jim <jim@revjim.net>',
		'licence'         =>'GPL',
		'help'   => $help
	);
}


/**
 *
 *
 * @param unknown $params
 * @param unknown $bBlog  (reference)
 * @return unknown
 */
function smarty_function_getpost($params, &$bBlog) {
	$ar = array();

	// If "assign" is not set... we'll establish a default.
	if ($params['assign'] == '') {
		$params['assign'] = 'post';
	}
	if ($params['postid'] == '') {
		$bBlog->trigger_error('postid is a required parameter');
		return '';
	}

	$q = $bBlog->make_post_query(array("postid"=>$params['postid']));

	$ar['posts'] = $bBlog->get_posts($q);

	// No posts.
	if (!is_array($ar['posts'])) {
		return false;
	}

	$ar['posts'][0]['newday'] = 'yes';
	$ar['posts'][0]['newmonth'] = 'yes';

	$bBlog->assign($params['assign'], $ar['posts'][0]);

	return '';

}


?>
