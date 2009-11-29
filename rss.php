<?php
/**
 * ./rss.php
 *
 * @package default
 */


if (!defined('CUSTOMRSS')) {
	// so for example you could use this file but include it instead of calling it directly..
	include "bblog/config.php";
	$ver = @$_GET['ver'];
	$num = @$_GET['num'];
	$sectionid = @$_GET['sectionid'];
	$section = @$_GET['section'];
	$year = @$_GET['year'];
	$month = @$_GET['month'];
	$day = @$_GET['day'];
}
$p = array();
if (is_numeric($num)) $p['num'] = $num;
else $p['num'] = 10;

if (is_numeric($sectionid)) $p['sectionid'] = $sectionid;

if (strlen($sectionname) >0) {
	$sid = $bBlog->sect_by_name[$sectionname];
	if (is_numeric($sid) && $sid>0)
		$p['sectionid'] = $sid;
}

if (is_numeric($year)) $p['year'] = $year;
if (is_numeric($year)) $p['month'] = $month;
if (is_numeric($year)) $p['day'] = $day;

$posts = $bBlog->get_posts($bBlog->make_post_query($p));
$bBlog->assign('posts', $posts);

$bBlog->template_dir = BBLOGROOT.'inc/admin_templates';
$bBlog->compile_id = 'admin';

// Format last modification date for use in the header.
$last_modified = gmdate("D, d M Y H:i:s \G\M\T", C_LAST_MODIFIED);
$last_modified_hash = (isset($last_modified) ? md5($last_modified) : '');

// Set the Last-Modified and Etag headers.
header("Last-Modified: {$last_modified}", true);
header("Etag: {$last_modified_hash}", true);

switch ($ver) {
case '2.0':
	header("Content-Type: application/rss+xml", true);
	$bBlog->display('rss20.html', false);
	break;
case '1.0':
	header("Content-Type: application/rss+xml", true);
	$bBlog->display('rss10.html', false);
	break;

case 'atom03':
	header('Content-type: application/atom+xml', true);
	$bBlog->display('atom.html', false);
	break;

default:
	header("Content-Type: text/xml", true);
	$bBlog->display('rss092.html', false);
	break;

}
?>
