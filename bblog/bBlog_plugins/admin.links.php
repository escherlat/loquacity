<?php
/**
 * ./bblog/bBlog_plugins/admin.links.php
 *
 * @package default
 */


// admin.links.php - administer links
// Copyright (C) 2003  Mario Delgado <mario@seraphworks.com>
// admin.links.php - a plug-in written for bBlog Weblog
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
function identify_admin_links() {

	$help = '<p>Links is just a way of managing links. This plugin allows you to add, edit and delete links.';

	return array (
		'name'           =>'links',
		'type'           =>'admin',
		'nicename'       =>'Links',
		'description'    =>'Edit bBlog Links',
		'template'       =>'links.html',
		'authors'        =>'Mario Delgado <mario@seraphworks.com>, Eaden McKee <email@eadz.co.nz>',
		'licence'        =>'GPL',
		'help'           => $help
	);
}


/**
 *
 *
 * @param unknown $bBlog (reference)
 */
function admin_plugin_links_run(&$bBlog) {

	if (isset($_GET['linkdo'])) { $linkdo = $_GET['linkdo']; }
	elseif (isset($_POST['linkdo'])) { $linkdo = $_POST['linkdo']; }
	else { $linkdo = ''; }

	switch ($linkdo) {

	case "New" :  // add new link
		$maxposition = $bBlog->get_var("select position from ".T_LINKS." order by position desc limit 0,1");
		$position = $maxposition + 10;
		$bBlog->query("insert into ".T_LINKS."
            set nicename='".my_addslashes($_POST['nicename'])."',
            url='".my_addslashes($_POST['url'])."',
            category='".my_addslashes($_POST['category'])."',
	    position='$position'");
		break;

	case "Delete" : // delete link
		$bBlog->query("delete from ".T_LINKS." where linkid=".$_POST['linkid']);
		break;

	case "Save" : // update an existing link
		$bBlog->query("update ".T_LINKS."
            set nicename='".my_addslashes($_POST['nicename'])."',
            url='".my_addslashes($_POST['url'])."',
            category='".my_addslashes($_POST['category'])."'
            where linkid=".$_POST['linkid']);
		break;
	case "Up" :
		$bBlog->query("update ".T_LINKS." set position=position-15 where linkid=".$_POST['linkid']);
		reorder_links();

		break;

	case "Down" :
		$bBlog->query("update ".T_LINKS." set position=position+15 where linkid=".$_POST['linkid']);
		reorder_links();
		break;
	default : // show form
		break;
	}

	if (isset($_GET['catdo'])) { $catdo = $_GET['catdo']; }
	elseif (isset($_POST['catdo'])) { $catdo = $_POST['catdo']; }
	else { $catdo = ''; }

	switch ($catdo) {
	case "New" :  // add new category
		$bBlog->query("insert into ".T_CATEGORIES."
            set name='".my_addslashes($_POST['name'])."'");
		break;

	case "Delete" : // delete category
		// have to remove all references to the category in the links
		$bBlog->query("update ".T_LINKS."
            set linkid=0 where linkid=".$_POST['categoryid']);
		// delete the category
		$bBlog->query("delete from ".T_CATEGORIES." where categoryid=".$_POST['categoryid']);
		break;

	case "Save" : // update an existing category
		$bBlog->query("update ".T_CATEGORIES."
            set name='".my_addslashes($_POST['name'])."'
            where categoryid=".$_POST['categoryid']);
		break;

	default : // show form
		break;
	}

	$bBlog->assign('ecategories', $bBlog->get_results("select * from ".T_CATEGORIES));
	$bBlog->assign('elinks', $bBlog->get_results("select * from ".T_LINKS." order by position"));


}


/**
 *
 */
function reorder_links() {
	global $bBlog;
	$i = 20;
	$links = $bBlog->get_results("select * from ".T_LINKS." order by position");
	foreach ($links as $link) {
		$bBlog->query("update ".T_LINKS." set position='$i' where linkid='{$link->linkid}'");
		$i += 10;
	}
}


?>
