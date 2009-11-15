<?php
include "bblog/config.php";

if(is_numeric(@$_GET['postid'])) {
    if($_COOKIE['bBcomment']){
        $cdata = unserialize(base64_decode($_COOKIE['bBcomment']));
        $bBlog->assign('cdata',$cdata);
    }
    $bBlog->assign('postid',(int)$_GET['postid']);
    $bBlog->show_post = (int)$_GET['postid'];
    $bBlog->display('post.html');
}
// Removed the die; and stuck the bottom code
// in an elseif. Flyspray #64
else if(is_numeric(@$_GET['sectionid'])) {
   	$bBlog->assign('sectionid', (int)$_GET['sectionid']);
   	$bBlog->assign('sectionname',$bBlog->sect_by_name[(int)$_GET['sectionid']]);
   	$bBlog->show_section = (int)$_GET['sectionid'];
	$bBlog->display('index.html');
}
else {
	$bBlog->display('index.html');
}
?>
