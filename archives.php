<?php
/**
 * ./archives.php
 *
 * @package default
 */


include "bblog/config.php";

// xushi: This fix is so that the archives.html in
// kubrik and relaxation displays only the required
// month, and not all posts.
$bBlog->assign('year', $_GET['year']);
$bBlog->assign('month', $_GET['month']);
$bBlog->assign('day', $_GET['day']);

// Move on to the template's archive
$bBlog->display('archives.html');

?>
