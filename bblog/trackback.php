<?php
// trackback.php - Recieves a trackback, and functions for sending a trackback
// trackback.php - author: Eaden McKee <email@eadz.co.nz>
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
// in the MT implimentation, only the URL paramater is required. We also check for the varible $tbpost which is in the urlstring. 

/*
A Note about trackback and bBlog

At the moment we are using get varibles. That means that the bit that goes
The trackback URL for this post is:
http://www.example.com/blog/bblog/trackback.php?tbpost=1234
for a trackback on post 1234. 

Additionally bBlog is the first blog system to enable trackback replies to comments, and comment replies to trackbacks, so this is uncharted waters but it's pretty simple. Basicly, there is a trackback url for every post, and every comment. If a trackback is recieved for a comment it is handled like a reply to that comment in the database so displays threaded. Additionally, you can click reply to a trackback in the blog and rebut the excerpt if you so wish ;)

The trackback url for a comment is simply 
http://www.example.com/blog/bblog/trackback.php?tbpost=1234&cid=4141
 - adding the varible cid = commentid. 

If you so wished, you could use .htaccess or whatever and have trackback urls like : http://www.example.com/blog/trackback/1234 but that would require editing below. 

The future plan for bBlog is to have configurable URLs to the Nth degree, and when that happens this url will be configurable. 

*/

if(!defined('C_USER'))  {
	include_once("./config.php");
}

$tburi_ar = explode('/',$_SERVER['PATH_INFO']);
$tbpost = $tburi_ar[1];
$tbcid  = $tburi_ar[2];

if(isset($_POST['url']) && is_numeric($tbpost)) {
	// incoming trackback ping. 
	// we checked that :
	// a ) url is suplied by POST
	// b ) that the tbpost, suplied by GET, is valid. 
	// GET varibles from the trackback url:
	if(is_numeric($tbcid) && $tbcid > 0) {
		$replyto = $tbcid;
	} else {
		$replyto = 0;
	}
	
	// POST varibles - the trackback protocol no longer supports GET.  
	$tb_url = my_addslashes($_POST['url']);
	$title = my_addslashes($_POST['title']);
	$excerpt = my_addslashes($_POST['excerpt']);
	$blog_name = my_addslashes($_POST['blog_name']);

	// according to MT, only url is _required_. So we'll set some useful defaults. 
	
	// if we got this far, we can assume that this file is not included 
	// as part of bBlog but is being called seperatly. 
	// so we include the config file and therefore have access to the 
	// bBlog object. 
	
	
	$now = time();
	$remaddr = $_SERVER['REMOTE_ADDR'];

	$q = "insert into ".T_COMMENTS."
			set 
			postid='$tbpost',
			parentid='$replyto',
			posttime='$now',
			postername='$blog_name',
			posteremail='',
			posterwebsite='$tb_url',
			posternotify='0',
			pubemail='0',
			pubwebsite='1',
			ip='$remaddr',
			title='$title',
			commenttext='$excerpt',
			type='trackback'";
	$bBlog->query($q);
	$insid = $bBlog->insert_id;
	
	if($insid < 1) { 
		trackback_response(1,"Error adding trackback : ".mysql_error());
	} else {
		// notify owner
		include_once(BBLOGROOT.'inc/mail.php');
		notify_owner("New trackback on your blog","$blog_name ( $tb_url ) has sent a trackback to your post at ".$bBlog->_get_entry_permalink($tbpost)."
");
		// update the commentcount. 
		// now I thought about having a seperate count for trackbacks and comments ( like b2 )
		// , but trackbacks are really comments, so I decided against this. 
		
	        $newnumcomments = $bBlog->get_var("SELECT count(*) as c FROM ".T_COMMENTS." WHERE postid='$tbpost' and deleted='false' group by postid");
		$bBlog->query("update ".T_POSTS." set commentcount='$newnumcomments' where postid='$tbpost'");
	        $bBlog->modifiednow();
		trackback_response(0,"");

	}	

}



// Send a trackback-ping.
function send_trackback($url, $title="", $excerpt="",$t) {
    
    //parse the target-url
    $target = parse_url($t);
    
    if ($target["query"] != "") $target["query"] = "?".$target["query"];
    
    //set the port
    if (!is_numeric($target["port"])) $target["port"] = 80;
     
    //connect to the remote-host  
    $fp = fsockopen($target["host"], $target["port"]);
    
    if ($fp){

        // build the Send String
        $Send = "url=".rawurlencode($url).
                "&title=".rawurlencode($title).
                "&blog_name=".rawurlencode(C_BLOGNAME).
                "&excerpt=".rawurlencode($excerpt);
        
        // send the ping
        fputs($fp, "POST ".$target["path"].$target["query"]." HTTP/1.1\n");
        fputs($fp, "Host: ".$target["host"]."\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
        fputs($fp, "Content-length: ". strlen($Send)."\n");
        fputs($fp, "Connection: close\n\n");
        fputs($fp, $Send);
        
        //read the result
        while(!feof($fp)) {
            $res .= fgets($fp, 128);
        }
        
        //close the socket again  
        fclose($fp);
        
        //return success        
        return true;
    }else{
    
        //return failure
        return false;
    }
    
}

function trackback_response($error = 0, $error_message = '') {
	header("Content-Type: application/xml");    
	if ($error) {
		echo '<?xml version="1.0" encoding="iso-8859-1"?'.">\n";
		echo "<response>\n";
		echo "<error>1</error>\n";
		echo "<message>$error_message</message>\n";
		echo "</response>";
	} else {
		echo '<?xml version="1.0" encoding="iso-8859-1"?'.">\n";
		echo "<response>\n";
		echo "<error>0</error>\n";
		echo "</response>";
	}
	die();
}
?>
