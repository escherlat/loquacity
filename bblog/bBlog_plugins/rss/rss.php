<?php
/**
 * ./bblog/bBlog_plugins/rss/rss.php
 *
 * @package default
 */


/*
**	get_rss (RSS_URL, INPUT_CHARSET, OUTPUT_CHARSET)
**
**	example:
**		require_once('rss.php');
**		echo get_rss('http://www.root.cz/rss/','I88592','UTF8');
**
**	charsets: I88592, W1250, UTF8
**
**	Martin Konicek <martin.konicek@atlas.cz> (c) 2003
**	Licence: GNU/GPL
*/


// Libraries
$library_dir = dirname(__FILE__).'/library/';
require_once $library_dir.'rss_fetch.inc';
require_once $library_dir.'cls_convertor.inc';


/**
 * Main Function
 *
 * @param unknown $url
 * @param unknown $inputch
 * @param unknown $outputch
 * @param unknown $limit
 * @return unknown
 */
function get_rss($url, $inputch, $outputch, $limit) {
	if ( $url ) {
		$rss = fetch_rss( $url );
		// Generating HTML Code
		$rsscontent= '<b class="rss-channel">'. $rss->channel['title'] .'</b><p>';
		$rsscontent.= "<ul>";
		$counter = 1;
		foreach ($rss->items as $item) {
			$href = $item['link'];
			$title = $item['title'];
			$rsscontent.= "<li><a href=$href>$title</a></li>";

			if ($counter >= $limit)
				break;
			else
				$counter++;
		}
		$rsscontent.= "</ul>";
		// Charset Conversion
		$conv = new convertor;
		$conv->convertor($inputch, $outputch, $rsscontent);
		$rssconv=($conv->pull());
	}
	return $rssconv;
}


?>
