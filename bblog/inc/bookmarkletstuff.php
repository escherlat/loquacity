<?php
/**
 * ./bblog/inc/bookmarkletstuff.php
 *
 * @package default
 */


?>
<?
// this all borrowed from wordpress(.org), but I think most of this came from b2
# browser detection
$is_lynx = 0; $is_gecko = 0; $is_winIE = 0; $is_macIE = 0; $is_opera = 0; $is_NS4 = 0;
if (!isset($HTTP_USER_AGENT)) {
	$HTTP_USER_AGENT = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
}
if (preg_match('/Lynx/', $HTTP_USER_AGENT)) {
	$is_lynx = 1;
} elseif (preg_match('/Gecko/', $HTTP_USER_AGENT)) {
	$is_gecko = 1;
} elseif ((preg_match('/MSIE/', $HTTP_USER_AGENT)) && (preg_match('/Win/', $HTTP_USER_AGENT))) {
	$is_winIE = 1;
} elseif ((preg_match('/MSIE/', $HTTP_USER_AGENT)) && (preg_match('/Mac/', $HTTP_USER_AGENT))) {
	$is_macIE = 1;
} elseif (preg_match('/Opera/', $HTTP_USER_AGENT)) {
	$is_opera = 1;
} elseif ((preg_match('/Nav/', $HTTP_USER_AGENT) ) || (preg_match('/Mozilla\/4\./', $HTTP_USER_AGENT))) {
	$is_NS4 = 1;
}
$is_IE    = (($is_macIE) || ($is_winIE));
# browser-specific javascript corrections
$wp_macIE_correction['in'] = array(
	'/\%uFFD4/', '/\%uFFD5/', '/\%uFFD2/', '/\%uFFD3/',
	'/\%uFFA5/', '/\%uFFD0/', '/\%uFFD1/', '/\%uFFBD/',
	'/\%uFF83%uFFC0/', '/\%uFF83%uFFC1/', '/\%uFF83%uFFC6/', '/\%uFF83%uFFC9/',
	'/\%uFFB9/', '/\%uFF81%uFF8C/', '/\%uFF81%uFF8D/', '/\%uFF81%uFFDA/',
	'/\%uFFDB/'
);
$wp_macIE_correction['out'] = array(
	'&lsquo;', '&rsquo;', '&ldquo;', '&rdquo;',
	'&bull;', '&ndash;', '&mdash;', '&Omega;',
	'&beta;', '&gamma;', '&theta;', '&lambda;',
	'&pi;', '&prime;', '&Prime;', '&ang;',
	'&euro;'
);
$wp_gecko_correction['in'] = array(
	'/\‘/', '/\’/', '/\“/', '/\”/',
	'/\•/', '/\–/', '/\—/', '/\Ω/',
	'/\β/', '/\γ/', '/\θ/', '/\λ/',
	'/\π/', '/\′/', '/\″/', '/\�/',
	'/\€/', '/\ /'
);
$wp_gecko_correction['out'] = array(
	'&8216;', '&rsquo;', '&ldquo;', '&rdquo;',
	'&bull;', '&ndash;', '&mdash;', '&Omega;',
	'&beta;', '&gamma;', '&theta;', '&lambda;',
	'&pi;', '&prime;', '&Prime;', '&ang;',
	'&euro;', '&#8201;'
);
 $popuptitle = stripslashes($_REQUEST['popuptitle']);
    $text = stripslashes($_REQUEST['text']);

    /* big funky fixes for browsers' javascript bugs */

    if (($is_macIE) && (!isset($IEMac_bookmarklet_fix))) {
        $popuptitle = preg_replace($wp_macIE_correction["in"],$wp_macIE_correction["out"],$popuptitle);
        $text = preg_replace($wp_macIE_correction["in"],$wp_macIE_correction["out"],$text);
    }

    if (($is_winIE) && (!isset($IEWin_bookmarklet_fix))) {
        $popuptitle =  preg_replace("/\%u([0-9A-F]{4,4})/e",  "'&#'.base_convert('\\1',16,10).';'", $popuptitle);
        $text =  preg_replace("/\%u([0-9A-F]{4,4})/e",  "'&#'.base_convert('\\1',16,10).';'", $text);
    }

    if (($is_gecko) && (!isset($Gecko_bookmarklet_fix))) {
        $popuptitle = preg_replace($wp_gecko_correction["in"],$wp_gecko_correction["out"],$popuptitle);
        $text = preg_replace($wp_gecko_correction["in"],$wp_gecko_correction["out"],$text);
    }

    $post_title = $_REQUEST['post_title'];
    if (!empty($post_title)) {
        $post_title =  stripslashes($post_title);
    } else {
        $post_title = $popuptitle;
    }

    $content = $_REQUEST['content'];
    if (!empty($content)) {
        $content =  stripslashes($content);
    } else {
        $content = '<a href="'.$popupurl.'">'.$popuptitle.'</a>'."\n$text";
    }
    $bBlog->assign('title_text',$post_title);
    $bBlog->assign('body_text',$content);
   // print_r($_REQUEST);
?>
