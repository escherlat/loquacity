<?php
// xmlrpc.php - XML-RPC blogger/metaweblog api server
// xmlrpc.php - author: Eaden McKee <email@eadz.co.nz>

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



include 'config.php';
include BBLOGROOT."libs/rpc.php";
$xmlrpc_methods = array();

$xmlrpc_methods['blogger.getUsersBlogs']  	= 'blogger_getUsersBlogs';
$xmlrpc_methods['blogger.getRecentPosts'] 	= 'blogger_getRecentPosts';
$xmlrpc_methods['blogger.deletePost']		= 'blogger_deletePost';
$xmlrpc_methods['blogger.getPost']		= 'blogger_getPost';
//$xmlrpc_methods['blogger.getTemplate']		= 'blogger_getTemplate';
//$xmlrpc_methods['blogger.setTemplate']		= 'blogger_setTemplate';
$xmlrpc_methods['metaWeblog.newPost']		= 'metaWeblog_newPost';
$xmlrpc_methods['metaWeblog.getPost']		= 'metaWeblog_getPost';
$xmlrpc_methods['metaWeblog.getCategories']	= 'metaWeblog_getCategories';
$xmlrpc_methods['metaWeblog.getRecentPosts']	= 'metaWeblog_getRecentPosts';
$xmlrpc_methods['metaWeblog.editPost']		= 'metaWeblog_editPost';
$xmlrpc_methods['method_not_found']       	= 'XMLRPC_method_not_found';

$xmlrpc_methods['mt.getCategoryList'] = 'mt_getCategoryList';
$xmlrpc_methods['mt.getPostCategories'] = 'mt_getPostCategories';
$xmlrpc_methods['mt.setPostCategories'] = 'mt_setPostCategories';
$xmlrpc_methods['mt.publishPost'] = 'mt_publishPost';


$xmlrpc_request = XMLRPC_parse($HTTP_RAW_POST_DATA);
$methodName = XMLRPC_getMethodName($xmlrpc_request);
$params = XMLRPC_getParams($xmlrpc_request);
define('WEBLOG_XMLPRPC_USERAGENT','bBlog '.BBLOG_VERSION);
if(!isset($xmlrpc_methods[$methodName])){
    $xmlrpc_methods['method_not_found']($methodName);
}else{
    //call the method
    $xmlrpc_methods[$methodName]($params);
}


////
// !blogger.getUsersBlogs  	= blogger_getUsersBlogs
// gets a list of the users blogs.
// but at the moment bBlog is a single-blog thing
// so we just return the config details
function blogger_getUsersBlogs ($params) {
	Global $bBlog;
	$blogr=array();
	if($bBlog->userauth($params[1],$params[2])) {
		$blog['url'] 		= C_BLOGURL;
		$blog['blogName'] 	= C_BLOGNAME;
		$blog['blogid'] 	= 1;
		$blogr[]=$blog;
		XMLRPC_response(XMLRPC_prepare($blogr),WEBLOG_XMLPRPC_USERAGENT);	
	}	else {
		XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
}

////
// !blogger.deletePost - delete a post
// Parameters: String appkey, String postid, String username, String password, boolean publish

function blogger_deletePost ($params) {
	global $bBlog;
	if($bBlog->userauth($params[2],$params[3])) {
		// password accepted
			$bBlog->delete_post($params[1]);
			// lets just assume it worked for now.
			XMLRPC_response(XMLRPC_prepare("1"),WEBLOG_XMLPRPC_USERAGENT);
			//} else {
			//	XMLRPC_error("302", "There was an error deleting the post. Sorry...", WEBLOG_XMLRPC_USERAGENT);	
			//}
	} else {
			XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
}


// -------------------------------------------------------------------
// blogger.getUserInfo    	= blogger_getUserInfo
// Params:

//function blogger_getUserInfo ($params) {
	
//}

// -------------------------------------------------------------------
// blogger.getRecentPosts 	= blogger_getRecentPosts
// Params :

function blogger_getPost ($params) {
	global  $bBlog;	
	$postid = $params[4];
	if(!is_numeric($postid)) {
		XMLRPC_error("401", "PostID not numeric", WEBLOG_XMLRPC_USERAGENT);
	}
	        
		
	if($bBlog->userauth($params[2],$params[3],FALSE)) {
		// password accepted
		$entry = $bBlog->get_post($postid,TRUE,TRUE);
		$entriesar=array();
			$entryrt['userid'] = 1;
			$entryrt['postid'] = $entry->postid;
			$entryrt['content']= $entry->body;
			$entryrt['title']= $entry->title;
			$entryrt['dateCreated'] = XMLRPC_convert_timestamp_to_iso8601($entry->tstamp);
			$entriesar[] = $entryrt;
			
		XMLRPC_response(XMLRPC_prepare($entriesar),WEBLOG_XMLPRPC_USERAGENT);
		
	} else {
		XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
	
}


function blogger_getRecentPosts ($params) {
	global  $bBlog;	
	$numposts = $params[4];
	if($numposts < 1 || $numposts > 20) { $numposts = 20 ; }
	
	if($bBlog->userauth($params[2],$params[3],FALSE)) {
		// password accepted
		$entries = $bBlog->get_posts($bBlog->make_post_query(array("num"=>$numposts)),TRUE);
		
		foreach ($entries as $entry) {
			$entryrt['userid'] = 1;
			$entryrt['postid'] = $entry->postid;
			$entryrt['content']= $entry->body;
			$entryrt['title']= $entry->title;
			$entryrt['dateCreated'] = XMLRPC_convert_timestamp_to_iso8601($entry->tstamp);
			$entriesar[] = $entryrt;
		}
			
		XMLRPC_response(XMLRPC_prepare($entriesar),WEBLOG_XMLPRPC_USERAGENT);
		
	} else {
		XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
	
}

////
// !metaweblog.getRecentPosts, get recent posts - not working at the moment
// Params : blogid, username, password, numposts
function metaweblog_getRecentPosts ($params) {
	global $bBlog;
	$numposts = $params[3];
	if($numposts < 1 || $numposts > 20) { $numposts = 20 ; }
	
	if($bBlog->userauth($params[1],$params[2])) {
		// password accepted
		$q = $bBlog->make_post_query(array("num"=>$numposts));
		$posts = $bBlog->get_posts($q,TRUE);
		foreach ($posts as $post) {
			$entryrt['userid'] = 1;
			$entryrt['postid'] = $post->postid;
			$entryrt['dateCreated'] = XMLRPC_convert_timestamp_to_iso8601($post->posttime);
			$entryrt['description']= $post->body;
			$entryrt['title'] 	= $post->title;
			$entryrt['link'] 	= $bBlog->_get_entry_permalink($post->postid);
			$entriesar[] = $entryrt;
			
		}
		XMLRPC_response(XMLRPC_prepare($entriesar),WEBLOG_XMLPRPC_USERAGENT);
		
	} else {
		XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
}


// -------------------------------------------------------------------
// blogger.getTemplate		= blogger_getTemplate
// Params: 
/*
function blogger_getTemplate ($params) {
	Global $db;
	
	$gbuserid = gb_user_auth($params[2],$params[3]);
	if($gbuserid) {
		// password accepted
		if(is_owner_of_blog($parmas[2],$params[1])) {
			$templateid = $db->get_var("select templateid from blogs where blogid=$params[1]");
			$template = stripslashes($db->get_var("select template from templates where id=$templateid"));
			XMLRPC_response(XMLRPC_prepare($template),WEBLOG_XMLPRPC_USERAGENT);
		} else {
			XMLRPC_error("302", "You do not have edit access to the template of this blog.", WEBLOG_XMLRPC_USERAGENT);	
		}
	} else {
			XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
}


// -------------------------------------------------------------------
// blogger.setTemplate		= blogger_setTemplate
// Params:

function blogger_setTemplate ($params) {
	Global $db;
	
	$gbuserid = gb_user_auth($params[2],$params[3]);
	if($gbuserid) {
		// password accepted
		if(is_owner_of_blog($parmas[2],$params[1])) {
			$tpl =addslashes($params[4]);
			$templateid = $db->get_var("select templateid from blogs where blogid=$params[1]");
			$db->query("update templates set template='$tpl', tpltimestamp=NOW() where id=$templateid");
			XMLRPC_response(XMLRPC_prepare("1"),WEBLOG_XMLPRPC_USERAGENT);
		} else {
			XMLRPC_error("302", "You do not have edit access to the template of this blog.", WEBLOG_XMLRPC_USERAGENT);	
		}
	} else {
			XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
}
*/
// -------------------------------------------------------------------
// metaWeblog.newPost		= metaWeblog_newPost
// Params :

function metaWeblog_newPost ($params) {
  Global $bBlog;
  if($bBlog->userauth($params[1],$params[2])) {
    // password accepted
    $sectionid = $bBlog->_get_section_id($params[3]['categories'][0]);
    
    $newpost->title = addslashes(stripslashes($params[3]['title']));
    $newpost->body = addslashes(stripslashes($params[3]['description']));
    if($sectionid > 0) $newpost->sections = array(0=>$sectionid);
    $newpost->status   = C_DEFAULT_STATUS; // in the future this will be the draft/live thing from the "post" or "post and publish"
    $newpost->modifier = C_DEFAULT_MODIFIER;
    $postid = $bBlog->new_post($newpost);
    if($postid > 0) {
	XMLRPC_response(XMLRPC_prepare($postid),WEBLOG_XMLPRPC_USERAGENT);
	// Ping weblogs.com
	/*
        if(PINGWEBLOGSCOM) {
        	$weblogsparams[0] = BLOGNAME;// blog name
				$weblogsparams[1] = BLOGURL;// blog url
				$weblogsresponce = XMLRPC_request("rpc.weblogs.com", "/RPC2", "weblogUpdates.ping", $weblogsparams, WEBLOG_XMLRPC_USERAGENT );
			}
		} else {
			ob_start();
			print_r($bBlog->debug);
			$o = ob_get_contents();
			ob_end_clean();
			XMLRPC_error("312", "Something went wrong adding an entry : $o : $entryid", WEBLOG_XMLRPC_USERAGENT);	

		}
        */
    } else {
       XMLRPC_error("500", "something went wrong adding entry.", WEBLOG_XMLRPC_USERAGENT);	

    }
  } else {
  XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
  }
}

// -------------------------------------------------------------------
// metaWeblog.editPost		= metaWeblog_editPost
// metaWeblog.editPost (postid, username, password, struct, publish) returns true

function metaWeblog_editPost ($params) {
	global $bBlog;
	
	if($bBlog->userauth($params[1],$params[2])) { // password accepted
		//ob_start();
		//print_r($params);
		//$o = ob_get_contents();
		//ob_end_clean();
		$eparams = array('postid'=>$params[0],
			'body'=>my_addslashes($params[3]['description']),
			'title'=>my_addslashes($params[3]['title']));
		$bBlog->edit_post($eparams);
		XMLRPC_response(XMLRPC_prepare(1),WEBLOG_XMLPRPC_USERAGENT);
			
		
	} else { // password not accepted
		XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
	
}

// -------------------------------------------------------------------
// metaWeblog.getPost']		= metaWeblog_getPost
// Params: String postid, String username, String password
/*
Return value: on success, struct containing 
	String userid, 
	ISO.8601 dateCreated, 
	String postid, 
	String description, 
	String title, 
	String link, 
	String permaLink, 
	on failure, fault
*/
function metaWeblog_getPost ($params) {
	global  $bBlog;	
	if ($bBlog->userauth($params[1],$params[2])) {
		$postid = $params[0];
		$entryrow = $bBlog->get_post($postid,TRUE,TRUE);
		$entry['userid'] = 1;
		$entry['dateCreated'] = XMLRPC_convert_timestamp_to_iso8601($entryrow->posttime);
		$entry['postid'] = $entryrow->postid;
		$entry['description'] = $entryrow->body;
		$entry['title']		= $entryrow->title;
		$entry['link']		= $bBlog->_get_entry_permalink($entryrow->postid);
		//$entryar =array();
		//$entryar[]=$entry;
		//ob_start();
		//print_r($entryar);
		//echo "entry row :";
		//print_r($entryrow);
		//$o = ob_get_contents();
		//ob_end_clean();
		XMLRPC_response(XMLRPC_prepare($entry),WEBLOG_XMLPRPC_USERAGENT);
	} else {
		XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
	}
}

////
// !metaWeblog.getCategories get SECTIONS, we call them SECTIONS!
function metaWeblog_getCategories ($params) {
  global  $bBlog;	
  if($bBlog->userauth($params[1],$params[2])) {
    // password accepted
    $blogcats = array();
    $blogname = C_BLOGNAME;
    $defaultblog['description'] = "Default";
    $defaultblog['htmlUrl']	= C_BLOGURL;
    $defaultblog['rssUrl']	= $bBlog->_get_rss_url();
    $blogcats[] = $defaultblog;
    foreach($bBlog->sections as $section) {
        $catr['description']= $section->name;
	$catr['htmlUrl']	= $section->url;
	$catr['rssUrl']		= $section->rss_url;
	$blogcats[]=$catr;
    }
    ob_start();
    XMLRPC_response(XMLRPC_prepare($blogcats),WEBLOG_XMLPRPC_USERAGENT);
  } else {
    XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
  }
}


function mt_getCategoryList($params) {
    global $bBlog;	
    if($bBlog->userauth($params[1],$params[2]))
    {
        // password accepted
        $blogcats = array();
        foreach($bBlog->sections as $section)
        {
            $catr['categoryName']= $section->name;
            $catr['categoryId']	= $section->sectionid;
            $blogcats[] = $catr;
        }
        ob_start();
        XMLRPC_response(XMLRPC_prepare($blogcats),WEBLOG_XMLPRPC_USERAGENT);
    }
    else
    {
        XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
    }
}

function mt_getPostCategories($params) {
    global $bBlog;	
    if($bBlog->userauth($params[1],$params[2]))
    {
        // password accepted
        $postid = $params[0];
	$entryrow = $bBlog->get_post($postid,TRUE,TRUE);
	$categories = array();
		
        if($entryrow->sections != '')
        {
            // we are assuming that there is at least one section
            // becasue you shouldnt' have ":" or something in there !
            $tmp_sec_ar = explode(":",$entryrow->sections);
            $firstCategory = 1;
            foreach ($tmp_sec_ar as $tmp_sec)
            {
                // Make sure it isn't the empty section at
                // the beginning and end of each section list.
                if($tmp_sec != '')
                {
                    // Populate Sections Array
                    $categories[] = array("categoryId" => $tmp_sec,
                                          "categoryName" => $bBlog->sect_by_id[$tmp_sec],
                                          "isPrimary" => $firstCategory);
                    $firstCategory -= 1;
                    if ($firstCategory < 0)
                        $firstCategory = 0;
                }
            }
        }


        ob_start();
        XMLRPC_response(XMLRPC_prepare($categories),WEBLOG_XMLPRPC_USERAGENT);
    }
    else
    {
        XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
    }
}

function mt_setPostCategories($params) {
    global $bBlog;	
    if($bBlog->userauth($params[1],$params[2]))
    {
        // password accepted
        $postid = $params[0];
        $post = $bBlog->get_post($postid,TRUE,TRUE);

        $sections = array();
        foreach ($params[3] as $section)
        {
            $sections[] = $section['categoryId'];
        }
        $sections = implode(":", $sections);

        $result = $bBlog->edit_post(array('title'=>my_addslashes($post->title),
                                          'body'=>my_addslashes($post->body),
                                          'postid'=>$params[0], 
                                          'sections'=>$sections,
                                          'edit_sections'=>1));
        ob_start();
        XMLRPC_response(XMLRPC_prepare($result), WEBLOG_XMLPRPC_USERAGENT);
    }
    else
    {
        XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
    }
}


function mt_publishPost($params) {
    global $bBlog;	
    if($bBlog->userauth($params[1],$params[2]))
    {
        // password accepted
        ob_start();
        XMLRPC_response(XMLRPC_prepare(true), WEBLOG_XMLPRPC_USERAGENT);
    }
    else
    {
        XMLRPC_error("301", "The username and password you entered was not accepted. Please try again.", WEBLOG_XMLRPC_USERAGENT);	
    }
}

?>
