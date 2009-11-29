<?php
/**
 * ./bblog/bBlog_plugins/function.getrss.php
 *
 * @package default
 */


// function.getrss.php
// Modified inc/init.php
// Libraries

$library_dir = dirname(__FILE__).'/rss/';
require_once $library_dir.'rss.php';


/**
 *
 *
 * @return unknown
 */
function identify_function_getrss() {
	$help = '
<p>
<i>function </i><b>Get Recent Posts</b><br>
</p>
<p><b><i>example: </b></i>{getrss} - select random RSS Feed<br>
<p><b><i>example: </b></i>{getrss id=1} - select defined RSS Feed
<p><b><i>example: </b></i>{getrss id=1 limit=10} - Only show 10 items
</p>';

	return array (
		'name'           =>'getrss',
		'type'             =>'function',
		'nicename'     =>'Get RSS 0.1.2 alpha',
		'description'   =>'Parse RSS to HTML',
		'authors'        =>'Martin Konicek <martin.konicek@atlas.cz>',
		'licence'         =>'GPL',
		'help'   => $help
	);
}


/**
 *
 *
 * @param unknown $params
 * @param unknown $bBlog  (reference)
 * @return unknown
 */
function smarty_function_getrss($params, &$bBlog) {
	$outputcharset='UTF8';
	if (isset($params['id'])) {
		$rssrow = $bBlog->get_row("select * from ".T_RSS." where url<>'' and id='".$params['id']."'");
	} else { // get random one
		$rssrow = $bBlog->get_row("select * from ".T_RSS." where url<>'' order by rand(".time().") limit 0,1");

	}

	if (!isset ($params['limit']))
		$params['limit'] = 20;

	return get_rss($rssrow->url, $rssrow->input_charset, $outputcharset, $params['limit']);

}


?>
