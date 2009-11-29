<?php
/**
 * ./bblog/bBlog_plugins/function.getposts.php
 *
 * @package default
 */


// function.getposts.php
//
// Written by Eaden McKee & Reverend Jim
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
function identify_function_getposts() {
	$help = '
<p>the {getposts} function is used to retrieve a blog post or posts. <br />
It takes the following parameters:<br />
<br />
<ul>
<li>assign: variable to assign data to ( defaults to "posts", or "post" if postid is given) e.g. assign="posts"</li>
<li>postid: to get one post e.g. postid=1</li>
<li>archive: to get ascending sorted results ( older posts first )</li>
<li>num: for number of entries to return, e.g. num=10</li>
<li>section: to request recent items in a section, e.g. section="news"</li>
<li>sectionid: to request recent items in a section, by specifing the sectionid instead of the name</li>
<li>skip: number of entries to skip, e.g. {getposts section=news num=10 skip=10} will return 10 posts, but not the first ten but the second ten. for use with paging</li>
<li>home : if this is set with home=true, posts that have the "do not show on homepage" option set will not show</li>
<li>year: year of posts, e.g. year=2003 will only show posts for year 2003</li>
<li>month: month of posts</li>
<li>day: day of posts</li>
<li>hour: hour of posts</li>
<li>minute: minute of posts</li>
<li>second: second of posts</li>
</ul>
<p>For more detailed help, see the bBlog template manual</p>
';

	return array (
		'name'           =>'getposts',
		'type'             =>'function',
		'nicename'     =>'Get Posts',
		'description'   =>'Retrieves blog posts',
		'authors'        =>'Eaden McKee, Reverend Jim',
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
function smarty_function_getposts($params, &$bBlog) {
	$ar = array();
	$opt = array();
	if (is_numeric($params['postid']) && $params['postid'] > 0) {
		$postid = $params['postid'];
	} else {
		$postid = FALSE;
	}
	// If "assign" is not set... we'll establish a default.
	if ($params['assign'] == '') {
		if ($postid) $params['assign'] = 'post';
		else $params['assign'] = 'posts';
	}

	if ($postid) {   //we've been given a post id so we'll just get it and get outta here.
		$q = $bBlog->make_post_query(array("postid"=>$params['postid']));
		$ar['posts'] = $bBlog->get_posts($q);
		// No post.
		if (!is_array($ar['posts'])) {
			return false;
		}
		$ar['posts'][0]['newday'] = 'yes';
		$ar['posts'][0]['newmonth'] = 'yes';
		$bBlog->assign($params['assign'], $ar['posts'][0]);
		return ''; // so if postid is given this is the last line processed
	}

	// If "archive" is set, order them ASCENDING by posttime.
	if ($params['archive']) {
		$opt['order']=" ORDER BY posttime ";
	}


	// If num is set, we'll only get that many results in return
	if (is_numeric($params['num'])) {
		$opt['num'] = $params['num'];
	}

	// If skip is set, we'll skip that many results
	if (is_numeric($params['skip'])) {
		$opt['skip'] = $params['skip'];
	}

	if ($params['section'] != '') {
		$opt['sectionid'] = $bBlog->sect_by_name[$params['section']];
	}

	if ($bBlog->show_section) {
		$opt['sectionid'] = $bBlog->show_section;
	}

	if (is_numeric($params['year'])) {
		if (strlen($params['year']) != 4) {
			$bBlog->trigger_error('getposts: year parameter requires a 4 digit month');
			return '';
		}
		$opt['year'] = $params['year'];
	}

	if (is_numeric($params['month'])) {
		if (strlen($params['month']) != 2) {
			$bBlog->trigger_error('getposts: month parameter requires a 2 digit month');
			return '';
		}
		$opt['month'] = $params['month'];
	}

	if (is_numeric($params['day'])) {
		if (strlen($params['day']) != 2) {
			$bBlog->trigger_error('getposts: day parameter requires a 2 digit day');
			return '';
		}
		$opt['day'] = $params['day'];
	}

	if (is_numeric($params['hour'])) {
		if (strlen($params['hour']) != 2) {
			$bBlog->trigger_error('getposts: hour parameter requires a 2 digit hour');
			return '';
		}
		$opt['hour'] = $params['hour'];
	}

	if (is_numeric($params['minute'])) {
		if (strlen($params['minute']) != 2) {
			$bBlog->trigger_error('getposts: minute parameter requires a 2 digit minute');
			return '';
		}
		$opt['minute'] = $params['minute'];
	}

	if (is_numeric($params['second'])) {
		if (strlen($params['second']) != 2) {
			$bBlog->trigger_error('getposts: second parameter requires a 2 digit second');
			return '';
		}
		$opt['second'] = $params['second'];
	}

	$opt['home'] = $params['home'];

	$q = $bBlog->make_post_query($opt);

	$ar['posts'] = $bBlog->get_posts($q);

	// No posts.
	if (!is_array($ar['posts'])) {
		return '';
	}

	$lastmonth = 0;
	$lastdate = 0;

	foreach ($ar['posts'] as $key => $value) {

		/* check if new day  - used by block.newday.php */
		if (date('Ymd', $ar['posts'][$key]['posttime']) != $lastdate) {
			$ar['posts'][$key]['newday'] = TRUE;
		}
		$lastdate = date('Ymd', $ar['posts'][$key]['posttime']);

		/* check if new month - use by block.newmonth.php */
		if (date('Fy', $ar['posts'][$key]['posttime']) != $lastmonth) {
			$ar['posts'][$key]['newmonth'] = TRUE;
		}
		$lastmonth = date('Fy', $ar['posts'][$key]['posttime']);
	}

	$bBlog->assign($params['assign'], $ar['posts']);

	return '';

}


?>
