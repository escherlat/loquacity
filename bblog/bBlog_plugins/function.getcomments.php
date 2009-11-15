<?php
// block.comments.php - BBlog comments
// block.comments.php - author: Eaden McKee <email@eadz.co.nz>
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
function identify_function_getcomments () {
$help = '
<p>Provides threaded comments. See the default templates for usage examples.';

  return array (
    'name'           =>'getcomments',
    'type'             =>'function',
    'nicename'     =>'Get Comments',
    'description'   =>'Gets Comments and trackbacks for a post and threads them',
    'authors'        =>'Eaden McKee <eaden@eadz.co.nz>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}
function smarty_function_getcomments ($params, &$bBlog) {
	$assign="comments";
	$postid=$bBlog->show_post;
	$replyto = $_REQUEST['replyto'];
	extract($params);

        // first, assign the hidden fields
        $commentformhiddenfields = '<input type="hidden" name="do" value="submitcomment" />';
        $commentformhiddenfields .='<input type="hidden" name="comment_postid" value="'.$postid.'" />';
        if(is_numeric($replyto)) {
              $commentformhiddenfields .= '<a name="commentform"></a><input type="hidden" name="replytos" value="'.$replyto.'" />';
        }
        $bBlog->assign("commentformhiddenfields",$commentformhiddenfields);
        $bBlog->assign("commentformaction",$bBlog->_get_entry_permalink($postid));
        // are we posting a comment ?
        if($_POST['do'] == 'submitcomment' && is_numeric($_POST['comment_postid'])) { // we are indeed!
            if(is_numeric($_POST['replytos'])) $rt = $_POST['replytos'];
            else $rt =  false;

            $bBlog->new_comment($_POST['comment_postid'],$rt);
        }
                   
        // get the comments.
        
        /* start loop and get posts*/
        $rt = false;
        if(is_numeric($_GET['replyto'])) {
            $rt = $_GET['replyto'];
            $cs = $bBlog->get_comment($postid,$rt);
        } else {
            $cs = $bBlog->get_comments($postid,FALSE);
        }
        


          /* assign loop variable */
        $bBlog->assign($assign, $cs);




}

?>
