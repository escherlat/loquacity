<?php
/**
 * ./bblog/bBlog_plugins/admin.sections.php
 *
 * @package default
 */


// admin.sections.php - administer sections
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
function identify_admin_sections() {
	$help = '<p>Sections are just a way of organizing posts. This plugin allows you to edit and delete sections.
  When you make or edit a post, you can choose which sections it goes it.';
	return array (
		'name'           =>'sections',
		'type'             =>'admin',
		'nicename'     =>'Sections',
		'description'   =>'Edit Sections',
		'template'  => 'sections.html',
		'authors'        =>'Eaden McKee <eadz@bblog.com>',
		'licence'         =>'GPL',
		'help'            => $help
	);
}


/**
 *
 *
 * @param unknown $bBlog (reference)
 */
function admin_plugin_sections_run(&$bBlog) {
	// Again, the plugin API needs work.
	if (isset($_GET['sectdo'])) { $sectdo = $_GET['sectdo']; }
	elseif (isset($_POST['sectdo'])) { $sectdo = $_POST['sectdo']; }
	else { $sectdo = ''; }

	switch ($sectdo) {
	case 'new' :  // sections are being editied
		$bBlog->query("insert into ".T_SECTIONS."
			set nicename='".my_addslashes($_POST['nicename'])."',
			name='".my_addslashes($_POST['urlname'])."'");
		$insid = $bBlog->insert_id;
		$bBlog->get_sections(); // update the section cache
		break;

	case "Delete" : // delete section
		// have to remove all references to the section in the posts
		$sect_id = $bBlog->sect_by_name[$_POST['sname']];
		if ($sect_id > 0) { //
			$posts_in_section_q = $bBlog->make_post_query(array("sectionid"=>$sect_id));
			$posts_in_section = $bBlog->get_posts($posts_in_section_q, TRUE);
			if ($posts_in_section) {
				foreach ($posts_in_section as $post) {
					unset($tmpr);
					$tmpr = array();
					$tmpsections = explode(":", $post->sections);
					foreach ($tmpsections as $tmpsection) {
						if ($tmpsection != $sect_id) $tmpr[] = $tmpsection;
					}
					$newsects = implode(":", $tmpr);
					// update the posts to remove the section
					$bBlog->query("update ".T_POSTS." set sections='$newsects'
                                	where postid='{$post->postid}'");

				} // end foreach ($post_in_section as $post)
			} // end if($posts_in_section)
			// delete the section
			//$bBlog->get_results("delete from ".T_SECTIONS." where sectionid='$sect_id'");
			$bBlog->query("delete from ".T_SECTIONS." where sectionid='$sect_id'");
			//echo "delete from ".T_SECTIONS." where sectionid='$sect_id'";
			$bBlog->get_sections();
			//$bBlog->debugging=TRUE;

		} // else show error
	case "Save" :
		$sect_id = $bBlog->sect_by_name[$_POST['sname']];
		if ($sect_id < 1) break;
		$bBlog->query("update ".T_SECTIONS
			." set nicename='".my_addslashes($_POST['nicename'])."'
                        where sectionid='$sect_id'");
		$bBlog->get_sections(); // update section cache
		break;

	default : // show form
		break;
	}
	$bBlog->assign('esections', $bBlog->sections);
}


?>
