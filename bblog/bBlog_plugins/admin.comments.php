<?php
// admin.comments.php - administer comments
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

function identify_admin_comments () {
    return array (
    'name'           =>'comments',
    'type'             =>'admin',
    'nicename'     =>'Comments',
    'description'   =>'Remove, Approve or Edit comments',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'template' 	=> 'comments_admin.html',
    'help'    	=> ''
  );
}

function admin_plugin_comments_run(&$bBlog) {
  // Again, the plugin API needs work.
  $commentAmount = 50;
  if(isset($_GET['commentdo']))  { $commentdo = $_GET['commentdo']; }
  elseif (isset($_POST['commentdo'])) { $commentdo = $_POST['commentdo']; }
  else { $commentdo = ""; }
  
  switch($commentdo) {
    case "Delete" : // delete comments
        if(is_array($_POST['commentid'])){
          foreach($_POST['commentid'] as $key=>$val){
            deleteComment(&$bBlog, $val);
        }
      }
      break;
    case "Edit" :
      $commentid = intval($_GET['editComment']);
      $postid = intval($_GET['postid']);
      editComment(&$bBlog, $commentid, $postid);
      break;
    case "editsave" :
      saveEdit(&$bBlog);
      break;
    case "Approve":
      if(is_array($_POST['commentid'])){
        foreach($_POST['commentid'] as $key=>$val)
          $bBlog->query("UPDATE ".T_COMMENTS." SET onhold='0' WHERE commentid='".intval($val)."'");
      }
      break;
    case "25":
    case "50":
    case "100":
    case "150":
    case "200":
      $commentAmount = intval($commentdo);
      break;
    default : // show form
      break;
    }
    
    retrieveComments(&$bBlog, $commentAmount);
    populateSelectList(&$bBlog);
    
}

function deleteComment(&$bBlog, $id){
  $id = intval($id);
  $postid = $bBlog->get_var('select postid from '.T_COMMENTS.' where commentid="'.$id.'"');
  $childcount = $bBlog->get_var('select count(*) as c from '.T_COMMENTS .' where parentid="'.$id.'" group by commentid');
  if($childcount > 0) { // there are replies to the comment so we can't delete it.
    $bBlog->query('update '.T_COMMENTS.' set deleted="true", postername="", posteremail="", posterwebsite="", pubemail=0, pubwebsite=0, commenttext="Deleted Comment" where commentid="'.$val.'"');
  } else { // just delete the comment
    $bBlog->query('delete from '.T_COMMENTS.' where commentid="'.$id.'"');
  }
  $newnumcomments = $bBlog->get_var('SELECT count(*) as c FROM '.T_COMMENTS.' WHERE postid="'.$postid.'" and deleted="false" group by postid');
  $bBlog->query('update '.T_POSTS.' set commentcount="'.$newnumcomments.'" where postid="'.$postid.'"');
  $bBlog->modifiednow();
}

function editComment(&$bBlog, $commentid, $postid){
  $rval = true;
  if(!(is_numeric($commentid) && is_numeric($postid)))
    $rval = false;
  $comment = $bBlog->get_comment($postid,$commentid);
  if(!$comment)
    $rval = false;
  if($rval === true){
    $bBlog->assign('showeditform',TRUE);
    $bBlog->assign('comment',$comment[0]);
  }
  return $rval;
}

function saveEdit(&$bBlog){
  $rval = true;
  if(!(is_numeric($_POST['commentid'])))
    $rval = false;
  $title = my_addslashes($_POST['title']);
  $author = my_addslashes($_POST['author']);
  $email  = my_addslashes($_POST['email']);
  $websiteurl = my_addslashes($_POST['websiteurl']);
  $body = my_addslashes($_POST['body']);
  if($rval === true){
    $q = "update ".T_COMMENTS." set title='$title', postername='$author', posterwebsite='$websiteurl', posteremail='$email', commenttext='$body' where commentid='{$_POST['commentid']}'";
    if($bBlog->query($q) === true)
      $bBlog->assign('message', 'Comment <em>'.$title.'</em> saved');
  }
  return $rval;
}

function retrieveComments(&$bBlog, $amount){
  if ((isset($_POST['post_comments'])) && (is_numeric($_POST['post_comments']))) {
    $post_comments_q = "SELECT * FROM `".T_COMMENTS."` , `".T_POSTS."` WHERE `".T_POSTS."`.`postid`=`".T_COMMENTS."`.`postid` and deleted='false' and `".T_COMMENTS."`.`postid`='".$_POST['post_comments']."' order by `".T_COMMENTS."`.`posttime` desc";
    $bBlog->assign('comments',$bBlog->get_results($post_comments_q));
    $bBlog->assign('message','Showing comments for PostID '.$_POST['post_comments']); //.'.<br /><a href="index.php?b=plugins&amp;p=comments">Click here to show 50 most recent comments</a>.');
  } else {
    //$bBlog->assign('message','Showing '.$amount.' most recent comments across all posts. ');
    $bBlog->assign('comments',$bBlog->get_results("SELECT * FROM `".T_COMMENTS."` , `".T_POSTS."` WHERE `".T_POSTS."`.`postid`=`".T_COMMENTS."`.`postid` and deleted='false' order by `".T_COMMENTS."`.`posttime` desc limit 0,".$amount));
    $bBlog->assign('commentAmount', $amount);
  }
}

function populateSelectList(&$bBlog){
  $posts_with_comments_q = "SELECT ".T_POSTS.".postid, ".T_POSTS.".title, count(*) c FROM ".T_COMMENTS.",  ".T_POSTS." 	WHERE ".T_POSTS.".postid = ".T_COMMENTS.".postid GROUP BY ".T_POSTS.".postid ORDER BY ".T_POSTS.".posttime DESC ";

// previously function populateSelectList(&$bBlog){
//  $posts_with_comments_q = "SELECT ".T_POSTS.".postid, ".T_POSTS.".title, count(*) c FROM ".T_COMMENTS.",  ".T_POSTS." 	WHERE ".T_POSTS.".postid = ".T_COMMENTS.".postid GROUP BY ".T_POSTS.".postid ORDER BY ".T_POSTS.".posttime DESC  LIMIT 0 , 30 ";  
//removed the LIMIT parameter as it was unnecessary
  
  $posts_with_comments = $bBlog->get_results($posts_with_comments_q,ARRAY_A);
  $bBlog->assign("postselect",$posts_with_comments);
}
?>
