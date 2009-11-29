<?php
/**
 * ./bblog/bBlog_plugins/builtin.archives.php
 *
 * @package default
 */


// admin.archives.php - handles showing a list of entries to edit/delete
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
 * now it may be an idea to do a if(!defined('IN_BBLOG')) die "hacking attempt" type thing but
 * i'm not sure it's needed, as without this file being included it hasn't connected to the
 * database, and all the functions it calls are in the $bBlog object.
 *
 * @return unknown
 */
function identify_admin_archives() {
	return array (
		'name'           =>'archives',
		'type'           =>'builtin',
		'nicename'       =>'Archives Admin',
		'description'    =>'Edit archives',
		'authors'         =>'Eaden McKee, Tobias Schlottke',
		'licence'         =>'GPL'
	);
}


$bBlog->assign('form_type', 'edit');
$bBlog->get_modifiers();

if (isset($_GET['delete']) or isset($_POST['delete'])) {
	if ($_POST['confirm'] == "cd".$_POST['delete'] && is_numeric($_POST['delete'])) {
		$res = $bBlog->delete_post($_POST['delete']);
		$bBlog->assign('showmessage', TRUE);
		$bBlog->assign('message_title', 'Message Deleted');
		$bBlog->assign('message_content', 'The message you selected has now been deleted'); // -1 Redundant  ;)
	}
	else {
		$bBlog->assign('showmessage', TRUE);
		$bBlog->assign('message_title', 'Are you sure you want to delete it?');
		$bBlog->assign('message_content', "
            <form action='index.php' method='POST'>
            <input type='hidden' name='b' value='archives'>
            <input type='hidden' name='confirm' value='cd".$_POST['delete']."'>
            <input type='hidden' name='delete' value='".$_POST['delete']."'>
            <center><input type='submit' class='bf' name='submit' value='Delete it'></center>
            </form>
        ");
	}
}

if (isset($_POST['edit']) && is_numeric($_POST['edit'])) {
	$epost = $bBlog->get_post($_POST['edit'], TRUE, TRUE);
	$bBlog->assign('title_text', htmlspecialchars($epost->title));
	$bBlog->assign('body_text', htmlspecialchars($epost->body));
	$bBlog->assign('selected_modifier', $epost->modifier);
	$bBlog->assign('editpost', TRUE);
	$bBlog->assign('showarchives', 'no');
	$bBlog->assign('postid', $_POST['edit']);
	$bBlog->assign('timestampform', timestAmpform($epost->posttime));

	// to hide a post from the homepage
	if ($epost->hidefromhome == 1) $bBlog->assign('hidefromhomevalue', " checked='checked' ");

	// to disable comments either now or in the future
	if ($epost->allowcomments == 'timed') $bBlog->assign('commentstimedvalue', " checked='checked' ");
	elseif ($epost->allowcomments == 'disallow') $bBlog->assign('commentsdisallowvalue', " checked='checked' ");
	else $bBlog->assign('commentsallowvalue', " checked='checked' ");


	if ($epost->status == 'draft') $bBlog->assign('statusdraft', 'checked="checked"');
	else $bBlog->assign('statuslive', 'checked="checked"');

	$_post_secs = explode(":", $epost->sections);

	if (is_array($_post_secs)) {
		foreach ($_post_secs as $_post_sec) {
			$editpostsections[$_post_sec] = TRUE;
		}
		$bBlog->assign('editpostsections', $editpostsections);
	}

	$sects = $bBlog->sections;
	$nsects = array();

	foreach ($sects as $sect) {
		if (isset($editpostsections[$sect->sectionid])) $sect->checked = TRUE;
		$nsects[] = $sect;
	}

	$bBlog->assign("sections", $nsects);
	$bBlog->assign_by_ref("sections", $nsects);
}

if ((isset($_POST['postedit'])) && ($_POST['postedit'] == 'true')) {
	// a post to be editited has been submitted
	if ((isset($_POST['postedit'])) && (!is_numeric($_POST['postid']))) {
		echo "Provided PostID value is not a Post ID. (Fatal error)";
		die;
	}

	$newsections = '';

	if ((isset($_POST['sections'])) && (sizeof($_POST['sections']) > 0)) {
		$newsections = implode(":", $_POST['sections']);
	}

	if ((isset($_POST['edit_timestamp'])) && ($_POST['edit_timestamp'] == 'TRUE')) {
		// the timestamp will be changed.
		if (!isset($_POST['ts_day'])) { $_POST['ts_day']      = 0;    }
		if (!isset($_POST['ts_month'])) { $_POST['ts_month']    = 0;    }
		if (!isset($_POST['ts_year'])) { $_POST['ts_year']     = 0;    }
		if (!isset($_POST['ts_hour'])) { $_POST['ts_hour']     = 0;    }
		if (!isset($_POST['ts_minute'])) { $_POST['ts_minute']   = 0;    }

		$timestamp = maketimestamp($_POST['ts_day'], $_POST['ts_month'], $_POST['ts_year'], $_POST['ts_hour'], $_POST['ts_minute']);
	}
	else {
		$timestamp = FALSE;
	}

	if ($_POST['hidefromhome'] == 'hide') $hidefromhome='hide';
	else $hidefromhome='donthide';
	// there is a reason for not using booleans here.
	// is because the bBlog->edit_post function needs to know if to change it or not.

	$disdays = (int)$_POST['disallowcommentsdays'];
	$time = (int)time();
	$autodisabledate = $time + $disdays * 3600 * 24;


	$params = array(
		"postid"    => $_POST['postid'],
		"title"     => my_addslashes($_POST['title_text']),
		"body"      => my_addslashes($_POST['body_text']),
		"modifier"  => my_addslashes($_POST['modifier']),
		"status"    => my_addslashes($_POST['pubstatus']),
		"edit_sections" => TRUE,
		"hidefromhome" => $hidefromhome,
		"allowcomments" => my_addslashes($_POST['commentoptions']),
		"autodisabledate" => $autodisabledate,
		"sections"  => $newsections,
		"timestamp" => $timestamp
	);

	$bBlog->edit_post($params);

	if ((isset($_POST['send_trackback'])) && ($_POST['send_trackback'] == "TRUE")) {
		// send a trackback
		include "./trackback.php";

		if (!isset($_POST['title_text'])) { $_POST['title_text']  = ""; }
		if (!isset($_POST['excerpt'])) { $_POST['excerpt']     = ""; }
		if (!isset($_POST['tburl'])) { $_POST['tburl']       = ""; }
		send_trackback($bBlog->_get_entry_permalink($_POST['postid']), $_POST['title_text'], $_POST['excerpt'], $_POST['tburl']);
	}
}

if ((isset($_POST['filter'])) && ($_POST['filter'] == 'true')) {
	if ((isset($_POST['shownum'])) && (is_numeric($_POST['shownum']))) {
		$num = $_POST['shownum'];
	}
	else {
		$num=20;
	}

	$searchopts['num'] = $num;
	$searchopts['wherestart'] = ' WHERE 1 ';

	if (is_numeric($_POST['showsection'])) {
		$searchopts['sectionid'] = $_POST['showsection'];
	}

	if ($_POST['showmonth'] != 'any') {
		$searchopts['month'] = substr($_POST['showmonth'], 0, 2);
		$searchopts['year']  = substr($_POST['showmonth'], 3, 4);
	}
	//print_r($searchopts);
	$q = $bBlog->make_post_query($searchopts);
	//echo $q;
	$archives = $bBlog->get_posts($q);
}
else {
	$searchopts['wherestart'] = ' WHERE 1 ';
	$q = $bBlog->make_post_query($searchopts);
	$archives = $bBlog->get_posts($q); // ,TRUE);
}

$bBlog->assign('postmonths', get_post_months());
$bBlog->assign_by_ref('archives', $archives);
$bBlog->display('archives.html');


/**
 *
 *
 * @return unknown
 */
function get_post_months() {
	global $bBlog;
	$months_tmp = $bBlog->get_results("SELECT FROM_UNIXTIME(posttime,'%Y%m') yyyymm,  posttime from ".T_POSTS." group by yyyymm order by yyyymm");
	$months=array();
	foreach ($months_tmp as $month) {
		$nmonth['desc'] = date('F Y', $month->posttime);
		$nmonth['numeric'] = date('m-Y', $month->posttime);
		$months[]  = $nmonth;
	}
	return $months;
}


/**
 *
 *
 * @param unknown $ts
 * @return unknown
 */
function timestampform($ts) {
	$day = date('j', $ts);
	$month = date('m', $ts);
	$year = date('Y', $ts);
	$hour = date('H', $ts);
	$minute = date('i', $ts);
	$o  = "<span class='ts'>Day</span> /
	       <span class='ts'>Month</span> /
	       <span class='ts'>Year</span> @
	       <span class='ts'>24hours</span> :
	       <span class='ts'>Minutes</span><br />
	       <input type='text' name='ts_day' value='$day' class='ts' size='5'/> /
	       <input type='text' name='ts_month' value='$month' class='ts' size='5'/> /
	       <input type='text' name='ts_year' value='$year' class='ts' size='7'/> @
           <input type='text' name='ts_hour' value='$hour' class='ts' size='5'/> :
           <input type='text' name='ts_minute' value='$minute' class='ts' size='5'/>
           ";
	return $o;
}


/**
 *
 *
 * @param unknown $day
 * @param unknown $month
 * @param unknown $year
 * @param unknown $hour
 * @param unknown $minute
 * @return unknown
 */
function maketimestamp($day, $month, $year, $hour, $minute) {
	// make timestamp format of YYYYMMDDHHMMSS
	$string = $year.$month.$day.$hour.$minute.'00';
	$timestamp = mktime(substr($string, 8, 2), substr($string, 10, 2), substr($string, 12, 2), substr($string, 4, 2), substr($string, 6, 2), substr($string, 0, 4));
	return $timestamp;
}


?>
