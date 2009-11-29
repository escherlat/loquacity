<?php
/**
 * ./bblog/bBlog_plugins/function.recentposts.php
 *
 * @package default
 */


// function.recentposts.php - main post loop plugin
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
// 11 June 2003
//   * allow to create real unordered lists: all param mode=list
//   * customizeable title length
//   -- Sebastian http://www.sebastian-werner.net/


/**
 *
 *
 * @return unknown
 */
function identify_function_recentposts() {
	$help = '
<p>Recentposts is a function that creates a list of recent posts
<p>Example usage {recentposts}  to create a list of the 5 most recent posts seperated by a &lt;br&gt;<br />
Or {recentposts mode="list"} to make a list using &lt;li&gt;
<p>Other paramaters : <br>
num=10 for 10 posts<br>
skip=10 to skip 10 posts<br/>
sep=" | " to seperate by pipe instead of  &lt;br&gt;';

	return array (
		'name'           =>'recentposts',
		'type'             =>'function',
		'nicename'     =>'Recent Posts',
		'description'   =>'Displays list of most recent posts',
		'authors'        =>'Eaden McKee <eadz@bblog.com>',
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
function smarty_function_recentposts($params, &$bBlog) {
	$num = 5;
	$mode = "br";
	$sep = "<br />";
	$titlelen=30;
	$skip = 0;
	$linkcode = '';
	if (isset($params['sep'])) $sep = $params['sep'];

	if (isset($params['num'])) $num = $params['num'];

	if (isset($params['mode'])) $mode = $params['mode'];

	if (isset($params['skip'])) $skip = $params['skip'];

	if (isset($params['titlelen'])) $titlelen = $params['titlelen'];

	$q = $bBlog->make_post_query(array("num"=>$num, "skip"=>$skip));

	$posts = $bBlog->get_posts($q);

	if ($mode=="list") $linkcode .= "<ul>";

	$i=0;
	if (is_array($posts)) {
		/* <a([^<]*)?href=(\"|')?([a-zA-Z]*://[a-zA-Z0-9]*\.[a-zA-Z0-9]*\.[a-zA-Z]*([^>]*)?)(\"|')?([^>]*)?>([^<]*)</a> */
		// This should match any protocol, any port, any URL, any title. URL's like www.yest.com are supported, and should be treated as HTTP by browsers.
		$regex = "#<a([^<]*)?href=(\"|')?(([a-zA-Z]*://)?[a-zA-Z0-9]*\.[a-zA-Z0-9]*\.[a-zA-Z]*(:[0-9]*)?([^>\"\']*)?)(\"|')?([^>]*)?>([^<]*)</a>#i";

		foreach ($posts as $post) {
			$title = $post["title"];
			$fulltitle = $title;   //wont be cut off

			if (preg_match($regex, $title, $matches) == 1) {
				$title = $matches[9];
			}

			$i++;
			if ($mode=="list") $linkcode .= "<li>";

			// we using arrays in the template and objects in the core..
			$url = $post['permalink'];
			$title = truncate($title, $titlelen, '...', FALSE);
			$linkcode .= "<a href='$url' title='$fulltitle'>$title</a>";

			if ($mode=="br" && $num > $i) $linkcode .= $sep;
			if ($mode=="list") $linkcode .= "</li>";
		}
	}

	if ($mode=="list") $linkcode .= "</ul>";

	return $linkcode;
}


/**
 *
 *
 * @param unknown $string
 * @param unknown $length      (optional)
 * @param unknown $etc         (optional)
 * @param unknown $break_words (optional)
 * @return unknown
 */
function truncate($string, $length = 80, $etc = '...',
	$break_words = false) {
	if ($length == 0)
		return '';

	if (strlen($string) > $length) {
		$length -= strlen($etc);
		if (!$break_words)
			$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));

		return substr($string, 0, $length).$etc;
	} else
		return $string;
}


/* vim: set expandtab: */

?>
