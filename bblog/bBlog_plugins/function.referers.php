<?php
/**
 * ./bblog/bBlog_plugins/function.referers.php
 *
 * @package default
 */


/**
 * bBlog Plugin : referers.
 * function.referers.php - Show ( and log ) recent referers
 * based on code by nathan@ncyoung.com
 * usage:
 * in a page template : {referers}.
 * To seperate links other than the default <br> : {referers sep=" | "} to seperate by pipe
 * other paramaters :
 * {referers pages="all"} show referers for all pages.
 * ( the default is to just show the referers for the current page }
 *
 * @return unknown
 */
function identify_function_referers() {
	$help = '
<p>Referers is a Smarty function to be used in templates that captures and lists referers to the current page.
<p>Example usage ( in a template put ) : {referers}
<p>The referer link list is seperate by a &lt;br&gt; by default. To override :<br>
{referers sep=", "} to seperate by a commar.
<p>Other paramaters :<br>
{referers num=10} - show 10 referers. Default is 5<br>
{referers top=TRUE} - show top referes instead of newest<br>
{referers global=TRUE} - show referers for all pages, not just the current one<br>
Those paramaters may be combined : {referers num=10 global=TRUE sep=" | "}';


	return array (
		'name'           =>'referers',
		'type'             =>'function',
		'nicename'     =>'Referers',
		'description'   =>'Captures and list referers',
		'authors'        =>'nathan@ncyoung.com',
		'licence'         =>'Free',
		'help'   => $help
	);


}


/* if you want to use this plugin out side of smarty,
 use this mysql table format :

CREATE TABLE `bB_referers` (
`visitID` int(11) NOT NULL auto_increment,
`visitTime` timestamp(14) NOT NULL,
`visitURL` char(250) default NULL,
`referingURL` char(250) default NULL,
`baseDomain` char(250) default NULL,
PRIMARY KEY  (`visitID`)
) TYPE=MyISAM;

and define your base url :
define('BLOGURL','http://www.example.com/');
*/
define('T_REFERERS', TBL_PREFIX.'referers');

logReferer();


/**
 *
 *
 * @param unknown $params
 * @param unknown $bBlog  (reference)
 * @return unknown
 */
function smarty_function_referers($params, &$bBlog) {
	$num = 5;
	$sep = "<br />";
	$mode = "break";
	$global = "";
	$top = FALSE;
	extract($params);

	if ($top) { $ret=toprefererlist($num, $global); }
	else { $ret=refererlist($num, $global); }

	if ($mode = 'list') { return tolist($ret); }
	else { return implode($sep, $ret); }
}


/**
 *
 *
 * @param unknown $array
 * @return unknown
 */
function tolist($array) {
	$ret="<ul>";

	foreach ($array as $elem) {
		$ret .= "<li>".$elem."</li>";
	}

	$ret.="</ul>";

	return $ret;
}


//problems? suggestions? email the author!
//
//              nathan@ncyoung.com

//get most linked to pages on site
//select count(visitURL) as count, visitURL from referer_visitLog group by visitURL order by count desc

// no need for this as we are already connected
// mysql_connect("dbHost", "dbUser", "dbPass");
// mysql_select_db("dbName");

//if ($refererList){
// $ar = refererList($refererList,"global");
// print join("<BR>",$ar);
//}
//if ($topRefererList){
// print join("<BR>",topRefererList($topRefererList,"global"));
//}


/**
 *
 */
function logReferer() {


	$currentURL = $_SERVER['REQUEST_URI'];
	$fullCurrentURL = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	$ref = getenv('HTTP_REFERER');

	if (!$ref) {
		dbg("no referer");
		return;
	}

	if ($ref != strip_tags($ref)) {
		//then they have tried something funny,
		//putting HTML or PHP into the HTTP_REFERER
		dbg("bad char in referer");
		return;
	}

	$ignore = array(
		BLOGURL,
		'http://www.myelin.co.nz/ecosystem/bot.php',
		'http://radio.xmlstoragesystem.com/rcsPublic/',
		'http://blogdex.media.mit.edu//',
		'http://subhonker6.userland.com/rcsPublic/',
		'http://subhonker7.userland.com/rcsPublic/',
		'mastadonte.com',
		'+++++++++++++'

	);
	foreach ($ignore as $site) {
		if (stristr($ref, $site)) {
			dbg("referer ignored");
			return;
		}
	}

	$doubleCheckReferers = 0;

	if ($doubleCheckReferers) {

		dbg("loading referering page");

		//this is so that the page up until the call to
		//logReferer will get shown before it tries to check
		//back against the refering URL.
		flush();

		$goodReferer = 0;
		$fp = @fopen($ref, "r");
		if ($fp) {
			//timeout after 5 seconds
			socket_set_timeout($fp, 5);
			while (!feof($fp)) {
				$page .= trim(fgets($fp));
			}
			if (strstr($page, $fullCurrentURL)) {
				dbg("found current url in page");
				$goodReferer = 1;
			}
		}

		if (!$goodReferer) {
			dbg("did not find \n\n:$fullCurrentURL:\n in \n\n\n :$page: \n\n\n");
			return;
		}

	}


	$anchor = preg_replace("/http:\/\//i", "", $ref);
	$anchor = preg_replace("/^www\./i", "", $anchor);
	$anchor = preg_replace("/\/.*/i", "", $anchor);


	$sql ="insert into ".T_REFERERS." (referingURL,baseDomain,visitURL) values ('$ref','$anchor','$currentURL')";

	//print $sql;

	mysql_query($sql);

}



/**
 *
 *
 * @param unknown $howMany  (optional)
 * @param unknown $visitURL (optional)
 * @return unknown
 */
function refererList($howMany=5, $visitURL="") {

	$i=2;

	$ret = array();

	//if no visitURL, will show links to current page.
	//if url given, will show links to that page.
	//if url="global" will show links to all pages
	if (!$visitURL) {

		$visitURL = $_SERVER['REQUEST_URI'];

	}

	if ($visitURL == "global") {
		$sqr_recentReferer = mysql_query("select * from ".T_REFERERS." order by visitID desc");
	}
	else {
		$sqr_recentReferer = mysql_query("select * from ".T_REFERERS." where visitURL = '$visitURL' order by visitID desc");
	}



	while ($result_row = mysql_fetch_array($sqr_recentReferer)) {

		$fullUrl = $result_row['referingURL'];
		$domain = $result_row['baseDomain'];
		if (!$domain) {
			continue;
		}

		if ($last[$domain]) {
			continue;
		}
		$last[$domain] = 1;

		// Fix parameter lists in URL, now conforms to xhtml
		$fullUrl = str_replace("&", "&#38;", $fullUrl);

		$temp = "<a href=\"$fullUrl\">$domain</a>";

		array_push($ret, $temp);

		if ($i++ > $howMany) {
			break;
		}

	}
	return $ret;
}


/**
 *
 *
 * @param unknown $howMany  (optional)
 * @param unknown $visitURL (optional)
 * @return unknown
 */
function topRefererList($howMany=5, $visitURL="") {


	$i=2;

	$ret = array();


	//see refererList() for notes.
	if (!$visitURL) {
		$visitURL = $_SERVER['REQUEST_URI'];
	}

	if ($visitURL == "global") {
		$sqr_recentReferer = mysql_query("select Count(baseDomain) as totalHits, baseDomain from ".T_REFERERS."  group by baseDomain order by totalHits desc limit $howMany");
	}
	else {
		$sqr_recentReferer = mysql_query("select Count(baseDomain) as totalHits, baseDomain from ".T_REFERERS." where visitURL = '$visitURL' group by baseDomain order by totalHits desc limit $howMany");
	}

	while ($result_row = mysql_fetch_array($sqr_recentReferer)) {

		$count = $result_row['totalHits'];
		$domain = $result_row['baseDomain'];

		$uSet = mysql_query("select * from ".T_REFERERS." where baseDomain = '$domain' order by visitID desc");
		$uRow = mysql_fetch_array($uSet);
		$latestUrl = $uRow["referingURL"];

		// Fix parameter lists in URL, now conforms to xhtml
		$latestUrl = str_replace("&", "&#38;", $latestUrl);

		$temp = "<a href=\"$latestUrl\">$domain</a> ($count)";
		array_push($ret, $temp);

		if ($i++ > $howMany) {
			break;
		}

	}
	return $ret;
}


/**
 *
 *
 * @param unknown $string
 */
function dbg($string) {
	// global $bBlog;
	//
}






/*

Usage:

You must include the library in order to use it. Issue the include statement once on each page in which you want to use this library, before you call any of the functions.  A typical include statement would be:

include("refererLib.php");

To log the referers visiting a given page, place this code on the page:

logReferer();


To show a list of 5 pages that link to the current page (ordered by most recent visit) place this code:

$list = refererList(5);
foreach ($list as $link){
	print "$link<BR>";
}

To show a list of the outside links most commonly used to get to the current page:

$list = topRefererList(5);
foreach ($list as $link){
	print "$link<BR>";
}

In both cases, you can ask for a global list, i.e. a list of recent or top referers for all pages on your site that log referers:

$list = refererList(5,"global");
foreach ($list as $link){
	print "$link<BR>";
}

Or:

$list = topRefererList(5,"global");
foreach ($list as $link){
	print "$link<BR>";
}

*/
?>
