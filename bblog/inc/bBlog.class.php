<?php
/**
 * ./bblog/inc/bBlog.class.php
 *
 * @package default
 */


/*

   '||     '||'''|,  '||`
    ||      ||   ||   ||
    ||''|,  ||;;;;    ||  .|''|, .|''|,
    ||  ||  ||   ||   ||  ||  || ||  ||
   .||..|' .||...|'  .||. `|..|' `|..||
                                     ||
               v0.7.5             `..|'

** bBlog Weblog http://www.bblog.com/
** Copyright (C) 2005  Eaden McKee <email@eadz.co.nz>
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

if (!defined("OBJECT"))     define("OBJECT", "OBJECT", true);
if (!defined("ARRAY_A"))    define("ARRAY_A", "ARRAY_A", true);
if (!defined("ARRAY_N"))    define("ARRAY_N", "ARRAY_N", true);

// main core bBlog code
// extends the smarty class, so $Smarty->things become $bBlog->things
class bBlog extends Smarty {

	var $template;
	var $num_homepage_entries = 20;
	var $templatepage = "index.html";
	// for comments
	var $highestlevel = 0;
	var $com_order_array = array();
	var $com_finalar;
	////
	/**
	 * !bBlog constructor function
	 */
	function bBlog() {


		// initilize smarty by calling the smarty constructor class
		parent::Smarty();

		// connect to database
		$this->db = new db(DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOST);
		$this->num_rows =& $this->db->num_rows;
		$this->insert_id     =& $this->db->insert_id;

		// get config from the database
		$config_rows = $this->get_results('select * from '.T_CONFIG);
		// loop through and define the config
		foreach ($config_rows as $config_row) {
			$const_name = 'C_'.$config_row->name;;
			if (!defined($const_name)) { define($const_name, $config_row->value); }
		}
		$this->assign('blogname', C_BLOGNAME);
		$this->assign('blogdescription', C_BLOG_DESCRIPTION);
		$this->assign('blogurl', BLOGURL);
		$this->assign('bblogurl', BBLOGURL);
		$this->assign('metakeywords', C_META_KEYWORDS);
		$this->assign('metadescription', C_META_DESCRIPTION);
		$this->assign('charset', C_CHARSET);
		$this->assign('direction', C_DIRECTION);

		// initial time from config table, based on last updated stuff.
		// this is just the initial value.
		$this->lastmodified = C_LAST_MODIFIED;
		$this->register_postfilter("update_when_compiled");
		// load up the sections
		$this->get_sections();

		//start the session that we need so much ;)
		if (!session_id()) {
			session_start();
		}

	} // end of function bBlog


	/**
	 * database stuff
	 *
	 * @param unknown $query
	 * @return unknown
	 */
	function query($query) { return $this->db->query($query); }


	/**
	 *
	 *
	 * @param unknown $query
	 * @param unknown $output (optional)
	 * @return unknown
	 */
	function get_results($query, $output=OBJECT) { return $this->db->get_results($query, $output); }


	/**
	 *
	 *
	 * @param unknown $query
	 * @param unknown $output (optional)
	 * @param unknown $y      (optional)
	 * @return unknown
	 */
	function get_row($query, $output=OBJECT, $y=0) { return $this->db->get_row($query, $output, $y); }


	/**
	 *
	 *
	 * @param unknown $query
	 * @param unknown $x     (optional)
	 * @param unknown $y     (optional)
	 * @return unknown
	 */
	function get_var($query, $x=0, $y=0) { return $this->db->get_var($query, $x, $y); }



	////
	// !inserts a new entry
	// returns the new entryid on success
	// error message on fail
	/**
	 * assumes that my_addslashes() has already been applied and data is safe.
	 *
	 * @param unknown $post
	 * @return unknown
	 */
	function new_post($post) {
		$this->modifiednow();
		$now = time();
		$section = '';
		if (sizeof($post->sections)>0) {
			$sections = implode(":", $post->sections);
			// We add an extra ":" at the begging and end
			// of this string to ensure that we can locate
			// the sections properly.
			$section_q = " sections =':$sections:', ";
		}
		if (!isset($post->ownerid)) {
			$post->ownerid = $_SESSION['user_id'];
		}

		if ($post->hidefromhome == 'hide')
			$hidefromhome_q = " hidefromhome='1', ";
		else $hidefromhome_q = " hidefromhome='0', ";

		if ($post->allowcomments == ('allow' or 'disallow' or 'timed'))
			$allowcomments_q = " allowcomments='{$post->allowcomments}', ";

		if (is_numeric($post->autodisabledate))
			$autodisable_q = " autodisabledate='{$post->autodisabledate}', ";

		$q_insert = "INSERT INTO ".T_POSTS." SET
			title		='$post->title',
			body		='$post->body',
            posttime    ='$now',
            modifytime  ='$now',
            status      ='$post->status',
            $section_q
	    $hidefromhome_q
	    $allowcomments_q
	    $autodisable_q
            modifier	='$post->modifier',
            ownerid    ='$post->ownerid'
            ";

		$this->query($q_insert);
		$postid = $this->insert_id;
		if ($postid > 0) return $postid;
		else return false;

	} // end of function new_entry

	/**********************************************************************
	** get_archives
	** Get a list of archives from the db
	**********************************************************************/


	/**
	 *
	 *
	 * @param unknown $opts
	 * @return unknown
	 */
	function get_archives($opts) {
		$where = '';

		switch ($opts['show']) {
		case 'years':
			$archformat = '%Y';
			break;
		case 'months':
			$archformat = '%Y%m';
			break;
		case 'days':
			$archformat = '%Y%m%d';
			break;
		case 'hours':
			$archformat = '%Y%m%d%H';
			break;
		case 'minutes':
			$archformat = '%Y%m%d%H%i';
			break;
		case 'seconds':
			$archformat = '%Y%m%d%H%i%s';
			break;
		default:
			$archformat = '%Y%m';
			break;
		}

		if ($opts['year'] != '') {
			$where .= " AND FROM_UNIXTIME(posttime, '%Y') = '" . addslashes($opts['year']) . "' ";
		}
		if ($opts['month'] != '') {
			$where .= " AND FROM_UNIXTIME(posttime, '%m') = '" . addslashes($opts['month']) . "' ";
		}
		if ($opts['day'] != '') {
			$where .= " AND FROM_UNIXTIME(posttime, '%d') = '" . addslashes($opts['day']) . "' ";
		}
		if ($opts['hour'] != '') {
			$where .= " AND FROM_UNIXTIME(posttime, '%H') = '" . addslashes($opts['hour']) . "' ";
		}
		if ($opts['minute'] != '') {
			$where .= " AND FROM_UNIXTIME(posttime, '%i') = '" . addslashes($opts['minute']) . "' ";
		}
		if ($opts['second'] != '') {
			$where .= " AND FROM_UNIXTIME(posttime, '%s') = '" . addslashes($opts['second']) . "' ";
		}

		if ($opts['sectionid'] != '') {
			$where .= " AND sections LIKE '%:" . addslashes($opts['sectionid']) . ":%' ";
		}


		if ($opts['count'] == true) {
			$stmt = "select DISTINCT FROM_UNIXTIME(posttime, '" . $archformat . "') as archname, count(*) as cnt from ".T_POSTS." where status = 'live' " . $where . " group by archname order by archname";
		} else {
			$stmt = "select DISTINCT FROM_UNIXTIME(posttime, '" . $archformat . "') as archname from ".T_POSTS." where status = 'live' " . $where . " order by archname";
		}
		//echo $stmt;
		$archs = $this->get_results($stmt);

		if ($this->num_rows <= 0) {
			return false;
		}

		$ret = array();

		foreach ($archs as $arch) {
			$year = substr($arch->archname, 0, 4);
			$month = substr($arch->archname, 4, 2);
			$day = substr($arch->archname, 6, 2);
			$hour = substr($arch->archname, 8, 2);
			$minute = substr($arch->archname, 10, 2);
			$second = substr($arch->archname, 12, 2);
			$ts = mktime(
				$hour ? $hour : 0,
				$minute ? $minute : 0,
				$second ? $second : 0,
				$month ? $month : 1,
				$day ? $day : 1,
				$year ? $year : 1970);
			$ret[] = array(
				'archname' => $arch->archname,
				'year' => $year,
				'month' => $month,
				'day' => $day,
				'hour' => $hour,
				'minute' => $minute,
				'second' => $second,
				'ts' => $ts,
				'count' => $arch->cnt
			);
		}

		return $ret;
	}


	/**********************************************************************
	** get_entries
	** Gets blog entries from the db
	** array, $limit ex. " LIMIT 0,20 ", $order ex. " ORDER BY tstamp desc "
	** $sectionid ex = 1
	** Return
	**********************************************************************/
	////
	// !Gets blog entries from the db from a query.
	/**
	 * if apply mods is true, it will apply the modifiers
	 *
	 * @param unknown $q   (optional)
	 * @param unknown $raw (optional)
	 * @return unknown
	 */
	function get_posts($q=FALSE, $raw=FALSE) {
		// to make it easier for development, this function can take no query
		if (!$q) $q = "select posts.*, authors.nickname, authors.email, authors.fullname from ".T_POSTS." as posts left join ".T_AUTHORS." as authors ON posts.ownerid = authors.id where status like 'live' order by posttime desc limit 0,20";

		$posts = $this->get_results($q); // $posts returned as an object

		if ($this->num_rows > 0)
			if ($raw) return $posts;
			else {
				// load required plugins
				foreach ($posts as $post) {
					// this looks a bit wacky, but i think it works well..
					$modifiers[$post->modifier] = $post->modifier;
					//$modifierstest[] = $post->modifier;
				}
				if (sizeof($modifiers) > 0) {
					foreach ($modifiers as $modifier) {
						require_once $this->_get_plugin_filepath('modifier', $modifier);
					}
				}
				$finalposts = array();
				foreach ($posts as $post) {
					$finalposts[] = $this->prep_post($post);
				}
				return $finalposts;
			}

		else
			return array(array("title"=>"No posts found")); //  with $q
		return false;
		// this kind of thing is better done elsewhere.
		// return array(array("title"=>"No posts found"));
	} // end of function get_entries



	////
	// !formats a single post into a useful array suitable for smarty
	// i.e. an associatve array not an object
	// this function is pretty basic at the moment, but all
	// sorts of things will happen in the future.
	/**
	 * it assumes that the required plugin modifiers have been loaded
	 *
	 * @param unknown $post (reference)
	 * @return unknown
	 */
	function prep_post(&$post) {
		// first do the basics

		$npost['id'] = $post->postid;
		$npost['postid'] = $post->postid;

		$npost['permalink'] = $this->_get_entry_permalink($post->postid);
		$npost['trackbackurl'] = $this->_get_post_trackback_url($post->postid);
		$npost['title'] = $post->title;

		// do the body text
		if ($post->modifier != '') {
			// apply a smarty modifier to the body
			// in the future we could have multi modifiers
			// but I decided agains that for now, you can always make a
			// modifier that calls other modifiers if you really want to .
			$mod_func = 'smarty_modifier_'.$post->modifier;
			$npost['body'] = $mod_func($post->body);
			$npost['applied_modifier'] = $post->modifier;
		} else {
			$npost['body'] = $post->body;
			$npost['applied_modifier'] = 'none';
		}

		if (USE_SMARTY_TAGS_IN_POST == TRUE) {
			$this->assign('smartied_post', $npost['body']);
			$tmptemplatedir = $this->template_dir;
			$tmpcompileid = $this->compile_id;
			$this->template_dir = BBLOGROOT.'inc/admin_templates';
			$this->compile_id = 'internal';
			$npost['body'] = $this->fetch('smartypost.html');
			$this->template_dir = $tmptemplatedir;
			$this->compile_id = $tmpcompileid;
		}

		$npost['status'] = $post->status;

		// in the future
		$npost['posttime'] = $post->posttime;
		$npost['modifytime'] = $post->modifytime;

		// what we need here is that the date format
		// is available in the control panel as an option
		// this is only here as a convience, the date_format modifier should be used.
		$npost['posttime_f'] = date("D M j G:i:s T Y", $post->posttime);
		$npost['modifytime_f'] = date("D M j G:i:s T Y", $post->modifytime);
		$npost['sections']   = array();
		switch ($post->commentcount) {
		case 1 : $npost['commenttext'] = "One comment"; break;
		case 0 : $npost['commenttext'] = "Comment"; break;
		default: $npost['commenttext'] = $post->commentcount." comments"; break;
		}
		$npost['commentcount'] = $post->commentcount;
		if ($post->sections != '') {

			// we are assuming that there is at least one section
			// becasue you shouldnt' have ":" or something in there !
			$tmp_sec_ar = explode(":", $post->sections);
			foreach ($tmp_sec_ar as $tmp_sec) {
				// Make sure it isn't the empty section at
				// the beginning and end of each section list.
				if ($tmp_sec != '') {
					// Populate Sections Array
					$npost['sections'][] = array(
						"id"=>$tmp_sec,
						"name"=>$this->sect_by_id[$tmp_sec],
						"nicename"=>$this->sect_nicename[$tmp_sec],
						"url"=>$this->sect_url[$tmp_sec]
					);
				}
			}
		}
		//add the author info
		$npost['author'] = array(
			'id' => $post->ownerid,
			'nickname' => $post->nickname,
			'email' => $post->email,
			'fullname' => $post->fullname
		);
		$npost['hidefromhome'] = $post->hidefromhome;
		$npost['autodisabledate'] = $post->autodisabledate;
		if ($post->allowcomments == 'disallow' or ($post->allowcomments == 'timed' and $post->autodisabledate < time())) {
			$npost['allowcomments'] = FALSE;
		} else {
			$npost['allowcomments'] = TRUE;
		}

		return $npost;
	}





	/**
	 *
	 *
	 * @param unknown $params
	 * @return unknown
	 */
	function make_post_query($params) {
		$skip           = 0;
		$num            = 20;
		$sectionid      = FALSE;
		$postid         = FALSE;
		$wherestart     = " WHERE status='live' ";
		$where          = "";
		$order          = " ORDER BY posttime desc ";
		$what           = "*";

		// overwrite the above defaults with options from the $params array
		extract($params);

		if (!isset($limit))                                 $limit = " LIMIT $skip,$num ";
		if ((isset($postid)) && ($postid != FALSE))         $where .= " AND postid='$postid' ";
		if (isset($year))                                   $where .= " AND FROM_UNIXTIME(posttime,'%Y') = '" . addslashes($year) . "' ";
		if (isset($month))                                  $where .= " AND FROM_UNIXTIME(posttime,'%m') = '" . addslashes($month) . "' ";
		if (isset($day))                                    $where .= " AND FROM_UNIXTIME(posttime,'%D') = '" . addslashes($day) . "' ";
		if (isset($hour))                                   $where .= " AND FROM_UNIXTIME(posttime,'%H') = '" . addslashes($hour) . "' ";
		if (isset($minute))                                 $where .= " AND FROM_UNIXTIME(posttime,'%i') = '" . addslashes($minute) . "' ";
		if (isset($second))                                 $where .= " AND FROM_UNIXTIME(posttime,'%S') = '" . addslashes($second) . "' ";

		// There should be a ":" at the beginning and end of
		// any sections list
		if ((isset($sectionid)) && ($sectionid != FALSE))   $where .= " AND sections like '%:$sectionid:%' ";

		if ($home) $where .= " AND hidefromhome='0' ";
		$q = "SELECT posts.$what, authors.nickname, authors.email, authors.fullname FROM ".T_POSTS." AS posts LEFT JOIN ".T_AUTHORS." AS authors ON posts.ownerid = authors.id $wherestart $where $order $limit ";
		return $q;
	}


	////
	/**
	 * !gets one post
	 *
	 * @param unknown $postid
	 * @param unknown $draftok (optional)
	 * @param unknown $raw     (optional)
	 * @return unknown
	 */
	function get_post($postid, $draftok = FALSE, $raw = FALSE) {
		// this makes it safe for general use.
		// we don't want ppl being able to view drafts.
		if (!$draftok) $draft_q = "AND posts.status='live' ";
		else $draft_q = '';

		// php doesnt have an unless function :
		// unless(is_numeric($postid)) return false
		// so OR does the trick :) ( and it's cleaner. )
		if (!is_numeric($postid)) return false;

		$q = "SELECT posts.*, authors.nickname, authors.email, authors.fullname FROM ".T_POSTS." AS posts LEFT JOIN ".T_AUTHORS." AS authors ON posts.ownerid = authors.id WHERE posts.postid='$postid' $draft_q LIMIT 0,1";
		$post = $this->get_row($q);
		if ($this->num_rows > 0) {
			if ($raw) return $post;
			else {
				require_once $this->_get_plugin_filepath('modifier', $post->modifier);
				return $this->prep_post($post);
			}
		}
		else return FALSE;
	} // end of function get_post

	////
	/**
	 * !deletes a post
	 *
	 * @param unknown $postid
	 * @return unknown
	 */
	function delete_post($postid) {
		if (!is_numeric($postid)) return false;
		$this->modifiednow();
		// delete comments
		$q1 = "DELETE FROM ".T_COMMENTS." WHERE postid='$postid'";
		$this->query($q1);
		// delete post
		$q2 = "DELETE FROM ".T_POSTS." WHERE postid='$postid'";
		$this->query($q2);
		if ($this->rows_affected  == 1) return true;
		else return false;

	}


	////
	/**
	 * !edits a post
	 *
	 * @param unknown $params
	 * @return unknown
	 */
	function edit_post($params) {
		// we're changing a post so the blog has been modified.
		//print_r($params);
		//$this->debugging=TRUE;
		//$this->assign('post_edit',$params);
		$this->modifiednow();
		$now = time();
		extract($params);
		if (!is_numeric($postid)) return false;

		$q = "update ".T_POSTS." set title='$title', body='$body' ";
		$q .= ", modifytime='$now'";
		if ($sections) {
			// We place a ":" at the beginning and end of the sections
			// string to ensure that we can locate the sections
			// properly.
			$q .= ", sections=':$sections:' ";
		} elseif ($edit_sections) {
			$q .=", sections='' ";
		}
		if ($hidefromhome == 'hide') $q .= ", hidefromhome='1'";
		if ($hidefromhome == 'donthide') $q .= ", hidefromhome='0'";

		if ($allowcomments == ('allow' or 'disallow' or 'timed' ))
			$q .= ", allowcomments='$allowcomments'";

		if ($allowcomments == 'timed' && is_numeric($autodisabledate))
			$q .= ", autodisabledate='$autodisabledate'";

		if ($status)   $q .= ", status='$status'";
		if ($modifier) $q .= ",modifier='$modifier'";
		if ($timestamp) $q .= ",posttime='$timestamp'";


		$q .=" where postid='$postid'";
		//$this->assign('post_edit_q',$q);

		$res = $this->query($q);
		return true;
	}


	////
	/**
	 * !check against the user and pass stored in the bB authors table
	 *
	 * @param unknown $user
	 * @param unknown $pass
	 * @param unknown $setcookie (optional)
	 * @return unknown
	 */
	function userauth($user, $pass, $setcookie = FALSE) {
		$query = "SELECT `id` FROM `".T_AUTHORS."` WHERE `nickname`='".my_addslashes($user)."' AND `password`='".my_addslashes($pass)."'";
		if ($user_id = $this->get_var($query)) {
			$_SESSION['user_id'] = $user_id;
			return $user_id;
		} else {
			return false;

		}

	}


	////
	/**
	 * !logs out the admin
	 */
	function admin_logout() {
		$_SESSION['user_id'] = 0;
	}


	////
	/**
	 * !checks if the admin is logged in or not
	 *
	 * @return unknown
	 */
	function admin_logged_in() {
		if ((isset($_SESSION['user_id'])) && ($_SESSION['user_id'] > 0))
			return true;
		else
			return false;
	}


	////
	// !in charge of printing any HTTP headers, and displaying the page
	// via $Smarty->display() and outputting the footer ( html comments ).
	// in the future if gzip is supported, it will happen here too.
	// Nothing should be sent to the browser except by this function!
	/**
	 * and it really should only be called once.
	 *
	 * @param unknown $page
	 * @param unknown $addfooter (optional)
	 */
	function display($page, $addfooter=true) {
		ob_start();
		// we use a relitive path because otherwise we need
		// as many compile directories as template
		// and to make things easy for users we don't want them
		// to have to chmod 777 too many directories.
		parent::display($page);
		$o = ob_get_contents();
		ob_end_clean();
		/* this doesn't work properly yet as the page stays cached in the browser even when things change */
		/* so we'll make it always fresh until this is worked through.
        if(!defined('IN_BBLOG_ADMIN')) {
           $lmdate = gmdate('D, d M Y H:i:s \G\M\T',$this->lastmodified);
           header('Last-Modified: '.$lmdate);
           if ($_SERVER["HTTP_IF_MODIFIED_SINCE"] == $lmdate){
   		 	  header("HTTP/1.1 304 Not Modified");
               exit;
		   }
        } else { // we want the page always to be fresh :)
              // borrowed from wordpress :
               @header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 				// Date in the past
               @header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
               @header("Cache-Control: no-store, no-cache, must-revalidate"); 	// HTTP/1.1
               @header("Cache-Control: post-check=0, pre-check=0", false);
               @header("Pragma: no-cache"); 									// HTTP/1.0

          } */
		echo $o;
		if ($addfooter)
			echo buildfoot();
	}


	////
	// !called once to load up the sections
	/**
	 * and assign them to $sections in the template
	 *
	 * @return unknown
	 */
	function get_sections() {

		$sects = $this->get_results("select * from ".T_SECTIONS." order by name");
		if ($this->num_rows > 0) {
			$nsects = array();
			foreach ($sects as $sect) {
				// we'll make an array just like the one from the database
				// but with URL's
				// make some useful lookup tables
				$this->sect_by_id[$sect->sectionid] = $sect->name;
				$this->sect_by_name[$sect->name] = $sect->sectionid;
				$this->sect_nicename[$sect->sectionid] = $sect->nicename;
				$this->sect_url[$sect->sectionid] = $this->_get_section_link($sect->sectionid);
				$this->sect_rss_url[$sect->sectionid] = $this->_get_section_rss_link($sect->sectionid);
				$nsect = $sect;
				$nsect->url = $this->_get_section_link($sect->sectionid);
				$nsect->rss_url = $this->_get_section_rss_link($sect->sectionid);
				$nsects[] = $nsect;
			}
			// now the section array is available in any template
			$this->assign_by_ref('sections', $nsects);
			// we use $this->sections array a lot.
			$this->sections =& $nsects;
			return  $nsects;
		} else return FALSE;
	}


	////
	/**
	 * !gets the modifiers out of the db
	 *
	 * @return unknown
	 */
	function get_modifiers() {
		$mods = $this->get_results('select * from '.T_PLUGINS.' where type="modifier" order by id');
		$this->modifiers =& $mods;
		$this->assign_by_ref("modifiers", $mods);
		return $mods;
	}


	////
	// !sets the last modified time ( $timestamp is newer )
	// this function takes the modified times of all
	// displayed items and decides if it's modified or not
	/**
	 * I can't think of many cases where you would use this instead of modifiednow()
	 *
	 * @param unknown $timestamp
	 * @return unknown
	 */
	function setmodifytime($timestamp) {
		if ($this->lastmodified < $timestamp && $timestamp <= time()) $this->lastmodified = $timestamp;
		return true;
	}


	////
	// !modifiednow should be called in responce to a direct user action  changing data
	// resulting in the site being modified, e.g. a new post, an editied post,
	/**
	 * new link category, new comment etc
	 */
	function modifiednow() {
		$now = time();
		$this->query("update ".T_CONFIG." set value='$now' where name='LAST_MODIFIED'");
		$this->setmodifytime($now);
	}


	/*
      All links are generated here.
      This is handy becasue it means we can do any thing with the urls in the future,
      even ones like /computers/my_case_mod.html
      // hmm they should be called _get_*_url not get_*_link !
    */

	////
	/**
	 * !Get a link for a category
	 *
	 * @param unknown $sectionid (reference)
	 * @return unknown
	 */
	function _get_section_link(&$sectionid) {
		$sectionname = $this->sect_by_id[$sectionid];
		if (defined('CLEANURLS')) return str_replace('%sectionname%', $sectionname, URL_SECTION);
		return BLOGURL.'?sectionid='.$sectionid;
	}


	////
	/**
	 * !Get a link to the rss file for a category
	 *
	 * @param unknown $sectionid (reference)
	 * @return unknown
	 */
	function _get_section_rss_link(&$sectionid) {
		return BLOGURL.'rss.php?sectionid='.$sectionid;
	}


	////
	/**
	 * !get a permalink to an entry
	 *
	 * @param unknown $postid (reference)
	 * @return unknown
	 */
	function _get_entry_permalink(&$postid) {
		if (defined('CLEANURLS')) return str_replace('%postid%', $postid, URL_POST);
		else return BLOGURL.'?postid='.$postid;
	}


	////
	/**
	 * !get a permalink to a single comment
	 *
	 * @param unknown $postid    (reference)
	 * @param unknown $commentid (reference)
	 * @return unknown
	 */
	function _get_comment_permalink(&$postid, &$commentid) {
		if (defined('CLEANURLS')) return $this->_get_entry_permalink($postid).'#comment'.$commentid;
		return BLOGURL.'?postid='.$postid.'#comment'.$commentid;
	}





	/**
	 *
	 *
	 * @param unknown $sectionname
	 * @return unknown
	 */
	function _get_section_id($sectionname) {
		$sid = $this->sect_by_name[$sectionname];
		if ($sid > 0) return $sid;
		else return false;
	}


	////
	/**
	 * !gets the url to the default rss filr
	 *
	 * @param unknown $sectionid (optional)
	 * @return unknown
	 */
	function _get_rss_url($sectionid=FALSE) {
		// in the future well actuall use $sectionid
		// to return the rss url of just one section
		return BLOGURL.'rss.php';
	}





	/**
	 *
	 *
	 * @param unknown $postid
	 * @return unknown
	 */
	function _get_post_trackback_url($postid) {
		return BBLOGURL.'trackback.php/'.$postid.'/';

	}


	/**
	 *
	 *
	 * @param unknown $postid
	 * @param unknown $commentid
	 * @return unknown
	 */
	function _get_comment_trackback_url($postid, $commentid) {
		return BBLOGURL.'trackback.php/'.$postid.'/'.$commentid.'/';
	}




	// Comments Functions taken from block.comments.php
	// They belong here so they can be used everywhere.



	/**
	 *
	 *
	 * @param unknown $postid
	 * @param unknown $replyto (optional)
	 * @return unknown
	 */
	function get_comments($postid, $replyto=FALSE) {
		$this->com_order_array = array();
		if (is_numeric($replyto)) {
			$commentidq = " AND commentid='$replyto' ";
		}

		$commentids = $this->get_results("select *
  	FROM ".T_COMMENTS."
  	where postid='$postid'
  	$commentidq
   	order by commentid");

		if ($this->num_rows > 0) { // there are coments!
			foreach ($commentids as $row ) {
				$table[$row->parentid][$row->commentid] = $row->commentid;
			}

			// get the actual comments
			//$comments=$bBlog->get_results("SELECT * FROM ".T_COMMENTS." WHERE postid='$postid' $commentidq ");

			// make an array of comments, with the commentid as the key - there must be a better way!
			foreach ($commentids as $comment) {
				$this->com_finalar[$comment->commentid] = $comment;
			}
			// populate $this->com_order_array with the comments in order!
			$this->makethread(0, $table, 0);

			$commentsfinalarray = array();
			// the function that displays comments!
			foreach ($this->com_order_array as $comment) {
				$commentsfinalarray[] = $this->format_comment($comment);
			}
		}
		$this->assign("commentreplytitle", "Re: ".$this->get_var("select title from ".T_POSTS." where postid='$postid'"));
		return $commentsfinalarray;
	}


	// due to some weird bug with the recursive function,
	// there is a bit of duplicated code here for the meantime


	/**
	 *
	 *
	 * @param unknown $postid
	 * @param unknown $replyto (optional)
	 * @param unknown $raw     (optional)
	 * @return unknown
	 */
	function get_comment($postid, $replyto=FALSE, $raw = FALSE) {

		if (is_numeric($replyto)) $commentidq = " AND commentid='$replyto' ";
		$comment['data'] = $this->get_row("select *
                                         FROM ".T_COMMENTS."
                                         where postid='$postid'
                                         $commentidq
                                         order by commentid");

		if ($this->num_rows != 1) return FALSE;
		if ($raw) return $comment['data'];
		$comment['level'] = 0; // not displaying one comment in a thread
		$commentsfinalarray[] = $this->format_comment($comment);
		if ($replyto) {
			if (substr($commentsfinalarray[0]['title'], 0, 3) == 'Re:') {
				$this->assign("commentreplytitle", $commentsfinalarray[0]['title']);
			} else {
				$this->assign("commentreplytitle", "Re: ".$commentsfinalarray[0]['title']);
			}
		}
		return $commentsfinalarray;
	}


	/**
	 * !changes the array type and sets some default values for each comment
	 *
	 * @param unknown $comment
	 * @return unknown
	 */
	function format_comment($comment) {
		$postid = $comment['data']->postid;
		if ($comment['data']->deleted == "true") {
			$commentr['deleted'] = TRUE;
		}

		$commentr['body']     = $comment['data']->commenttext;
		$commentr['posttime'] = $comment['data']->posttime;
		$commentr['posted'] = $comment['data']->posttime;

		$commentr['name'] = $comment['data']->postername;
		$commentr['author'] = $comment['data']->postername;
		$commentr['title'] = $comment['data']->title;
		$commentr['type'] = $comment['data']->type;

		if ($comment['data']->onhold == 1) $commentr['onhold'] =TRUE;

		if ($comment['data']->pubemail > 0) {
			$commentr['email'] = $comment['data']->posteremail;
		}

		if ($comment['data']->pubwebsite > 0) {
			$commentr['website'] = $comment['data']->posterwebsite;
		}



		if ($comment['data']->pubemail > 0 && $comment['data']->posteremail != '') {
			$commentr['emaillink'] = "<a href='mailto:".$comment['data']->posteremail."'>@</a>";
		} else $commentr['emaillink'] = '';


		if ($comment['data']->pubwebsite > 0 && $comment['data']->posterwebsite != '') {
			$commentr['websitelink'] = "<a href='".$comment['data']->posterwebsite."'>www</a>";
		} else $commentr['websitelink'] = '';

		$commentr['websiteurl'] = $comment['data']->posterwebsite;
		$commentr['permalink'] = "<a name='comment{$comment['data']->commentid}'></a>
                      <a href='".$this->_get_comment_permalink($postid, $comment['data']->commentid)."'>#</a>";

		$commentr['permalinkurl'] = $this->_get_comment_permalink($postid, $comment['data']->commentid);

		$commentr['replylinkurl'] = $this->_get_entry_permalink($postid);

		if (substr_count($commentr['replylinkurl'], "?") == 1) {
			$commentr['replylinkurl'] .= "&amp;";
		} else {
			$commentr['replylinkurl'] .= "?";
		}

		$commentr['replylinkurl'] .= "replyto={$comment['data']->commentid}#commentform";

		$commentr['replylink'] = "<a href='".$commentr['replylinkurl']."'>Reply</a>";

		$commentr['commentid'] = $comment['data']->commentid;
		$commentr['postid'] = $postid;

		if ($comment['level'] > 0 ) {
			$commentr['level25'] = $comment['level']*25;
		} else {
			$commentr['level25'] = 1;
		}
		if ($comment['level'] > 0 ) {
			$commentr['level15'] = $comment['level']*15;
		} else {
			$commentr['level25'] = 1;
		}
		if ($comment['level'] > 0 ) {
			$commentr['level10'] = $comment['level']*10;
		} else {
			$commentr['level10'] = 1;
		}

		$commentr['level'] = $comment['level'];

		if ($this->highestlevel == 0 || $comment['level'] == 0) {
			$commentr['levelpercent']  = 0;
			$commentr['levelhalfpercent']  = 0;
		} else {
			$commentr['levelpercent']  = floor(( 100 / $this->highestlevel )*$comment['level']);
			$commentr['levelhalfpercent']  = floor(( 50 / $this->highestlevel )* $comment['level']);
		}

		$commentr['levelpercentremainder'] = 100 - $commentr['levelpercent'];

		$commentr['trackbackurl']  = $this->_get_comment_trackback_url($postid, $comment['data']->commentid);

		return $commentr;

	}


	/**
	 *
	 *
	 * @param unknown $postid
	 * @param unknown $replyto (optional)
	 */
	function new_comment($postid, $replyto = 0) {

		$post = $this->get_post($postid, FALSE, TRUE);
		if (!$post) {
			// this needs to be fixed...

			$this->standalone_message("Error adding comment", "couldn't find post id $postid");
		}
		elseif ($post->allowcomments == ('disallow') or ($post->allowcomments == 'timed' and $post->autodisabledate < time() )) {
			$this->standalone_message("Error adding comment", "Comments have been turned off for this post");
		} else {


			$postername = my_addslashes(htmlspecialchars($_POST["name"]));
			if ($postername == '') $postername = "Anonymous";
			$posteremail = my_addslashes(htmlspecialchars($_POST["email"]));
			$title = my_addslashes(htmlspecialchars($_POST["title"]));
			$posterwebsite = my_addslashes(htmlspecialchars($_POST["website"]));
			if ((substr(strtolower($posterwebsite), 0, 7) != 'http://') && $posterwebsite !='') {
				$posterwebsite = 'http://'.$posterwebsite;
			}
			$comment = my_addslashes($_POST["comment"]);
			if ($_POST["public_email"] == 1) $pubemail = 1; else $pubemail = 0;
			if ($_POST["public_website"] == 1) $pubwebsite = 1; else $pubwebsite = 0;
			if ($_POST["notify"] == 1) $notify = 1; else $notify = 0;
			$now = time();
			$remaddr = $_SERVER['REMOTE_ADDR'];

			if ($_POST['set_cookie']) {
				$value = base64_encode(serialize(array('web' => $posterwebsite, 'mail' => $posteremail, 'name' => $postername)));
				setcookie("bBcomment", $value, time() + (86400 * 360));
			}

			$moderated = FALSE;
			$onhold = '0';
			if (C_COMMENT_MODERATION == 'all') {
				$moderated = TRUE;
			} elseif (C_COMMENT_MODERATION == 'urlonly') {
				if ($comment != preg_replace('!<[^>]*?>!', ' ', $comment)) {
					// found html tags
					$moderated = TRUE;
				}
				if ($comment != preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3" target="_blank">\2://\3</a>', $comment)) {
					$moderated = TRUE;
				}
				if ($comment != preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3" target="_blank">\2.\3</a>', $comment)) {
					$moderated = TRUE;
				}
			}

			if ($moderated == TRUE) $onhold='1';

			if (C_COMMENT_TIME_LIMIT >0) {
				$fromtime = $now - (C_COMMENT_TIME_LIMIT * 60);
				$this->query("select * from ".T_COMMENTS." where ip='$remaddr' and posttime > $fromtime");
				if ($this->num_rows >0) {
					$this->standalone_message("Comment Flood Protection", "Error adding comment. You have tried to make a comment too soon after your last one. Please try again later. This is a bBlog spam prevention mesaure");

				}

			}

			if ($replyto > 0 && is_numeric($replyto)) $parentidq = " parentid='$replyto', ";
			$q = "insert into ".T_COMMENTS."
			set $parentidq
			postid='$postid',
			title='$title',
			posttime='$now',
			postername='$postername',
			posteremail='$posteremail',
			posterwebsite='$posterwebsite',
			posternotify='$notify',
			pubemail='$pubemail',
			pubwebsite='$pubwebsite',
			ip='$remaddr',
			commenttext='$comment',
			onhold='$onhold',
			type='comment'";
			$this->query($q);
			$insid = $this->insert_id;
			if ($insid < 1) {
				$this->standalone_message("Error", "Error inserting comment : ".mysql_error());
			} else {
				// notify
				include_once BBLOGROOT."inc/mail.php";
				$message = htmlspecialchars($postername)." has posted a comment in reply to your blog entry at ".$this->_get_entry_permalink($postid)."\n";
				if ($onhold == 1) $message .= "You have selected comment moderation and this comment will not appear until you approve it, so please visit your blog and log in to approve or reject any comments\n";
				notify_owner("New comment on your blog", $message);

				$newnumcomments = $this->get_var("SELECT count(*) as c FROM ".T_COMMENTS." WHERE postid='$postid' and deleted='false' group by postid");
				$this->query("update ".T_POSTS." set commentcount='$newnumcomments' where postid='$postid'");
				$this->modifiednow();

				// This is used when an alternate location is desired as the result of a successful post.
				if (isset($_POST['return_url'])) {
					$ru = str_replace('%commentid%', $insid, $_POST['return_url']);
					header("Location: ".$ru);
				} else {
					header("Location: ".$this->_get_entry_permalink($postid)."#comment".$insid);
				}
				ob_end_clean(); // or here.. hmm.
				exit;
			}
		}

	} // end function new_comment


	/**
	 *
	 *
	 * @param unknown $parcat
	 * @param unknown $table
	 * @param unknown $level
	 * @return unknown
	 */
	function makethread($parcat, $table, $level) {

		// recursive function! Get your head around this! :
		global $finalar;
		if ($level > $this->highestlevel) $this->highestlevel = $level;
		$list=$table[$parcat];
		while (list($key, $val)=each($list)) {
			array_push($this->com_order_array, array("id"=>$val, "level"=>$level, "data"=>$this->com_finalar[$val]));
			if ((isset($table[$key]))) {
				$this->makethread($key, $table, $level+1);
			}
		}
		return true;
	} // end function makethread


	/**
	 *
	 *
	 * @param unknown $message_title (optional)
	 * @param unknown $message       (optional)
	 * @param unknown $meta_redirect (optional)
	 * @param unknown $http_header   (optional)
	 */
	function standalone_message($message_title=FALSE, $message=FALSE, $meta_redirect=FALSE, $http_header = FALSE) {
		// THIS FUNCTION WILl KILL THE SCRIPT BEFORE ANYTHING GETS TO THE BROWSER.
		$this->template_dir = BBLOGROOT.'inc/admin_templates';
		$this->compile_id = 'admin';
		if (!$message) $this->assign('message', 'No message given!');
		else $this->assign('message', $message);
		if (!$message_title) $this->assign('message_title', '');
		else $this->assign('message_title', $message_title);

		$this->assign('meta_redirect', $meta_redirect);
		ob_end_clean();
		if ($http_header) header($http_header);
		$page = $this->fetch('standalone_message.html');
		echo $page;
		die();
	}


} // end of bBlog class
?>
