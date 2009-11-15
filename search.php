<?php
include "bblog/config.php";
$bBlog->assign('string', $_GET['string']);
$encoded = urlencode($_GET['string']);
$bBlog->assign('encodedstring', $encoded);
$bBlog->display('search.html');
?>
