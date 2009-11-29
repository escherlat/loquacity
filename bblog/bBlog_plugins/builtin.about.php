<?php
/**
 * ./bblog/bBlog_plugins/builtin.about.php
 *
 * @package default
 * @return unknown
 */


function identify_admin_help() {
	return array (
		'name'           =>'about',
		'type'           =>'builtin',
		'nicename'       =>'About',
		'description'    =>'Displays bBlog infomation',
		'authors'         =>'Eaden McKee <email@eadz.co.nz>',
		'licence'         =>'GPL'
	);
}


include BBLOGROOT.'inc/credits.php';
$bBlog->assign('credits', $credits);
$bBlog->assign('title', 'About bBlog '.BBLOG_VERSION);

ob_start();
include BBLOGROOT.'docs/LICENCE.txt';
$bBlog->assign('licence', ob_get_contents());
ob_end_clean();

ob_start();
include BBLOGROOT.'make_bookmarklet.php';
$bBlog->assign('bookmarklet', ob_get_contents());
ob_end_clean();

$bBlog->display("about.html");
?>
