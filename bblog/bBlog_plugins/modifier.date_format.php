<?php
/**
 * ./bblog/bBlog_plugins/modifier.date_format.php
 *
 * @package default
 */


/**
 * modifier.date_format.php - smarty modifier to format a timestamp
 *
 * @param unknown $date
 * @param unknown $format (optional)
 * @return unknown
 */
function smarty_modifier_date_format($date, $format="%F %j, %Y, %g:%i %a") {
	if ($date < 1 ) return '';
	// locale should be defined in a config file, not case by case
	define('C_LOCALE', 'en_GB');
	setlocale(LC_TIME, C_LOCALE);

	switch ($format) {
	case "full": return strftime("%A, %d. %B %Y, %H:%M", $date);
		break;

	case "date": return strftime("%A, %d. %B %Y", $date);
		break;

	case "europe": return strftime("%d.%m.%Y", $date);
		break;

	case "shortdate": return strftime("%x", $date);
		break;

	case "month": return strftime("%B", $date);
		break;

	case "year": return strftime("%Y", $date);
		break;

	case "monthyear": return strftime("%B %Y", $date);
		break;

	case "time": return strftime("%H:%M", $date);
		break;

	case "s1" : return date("F j, Y, g:i a", $date);
		break;

	case "s2" : return date("F j, Y", $date);
		break;

	case "atom" : return date('Y-m-d\TH:i:s\Z', $date);
		break;

	case "rss20" : return strftime("%a, %d %b %Y %H:%M:%S %Z", $date);
		break;

	case "rss92" : return strftime("%a, %d %b %Y %H:%M:%S %Z", $date);
		break;

	case "suffix" : return date("S", $date);
		break;

		// a clever little hack to make date() return a ISO 8601 standard date string for use in RSS 1.0
	case "rss10" : return substr(date("Y-m-d\Th:i:sO", $date), 0, 22).":".substr(date("O", $date), 3);
		break;

		// called Jim in reference to RevJim ( revjim.net ) who first used this format ( afict )
	case "jim" : return since($date)." on ".date("F j, Y", $date);
		break;

	case "since" : return since($date);
		break;

	default:
		//default should behave like the original smarty date_format
		//see if there is at least one % in the date. then we go for new format
		if (substr_count("$format", '%') > 0) {
			return strftime($format, $date);
		}
		//else we go the old date() way for backward compatibility
		else {
			return date($format, $date);
		}
		break;
	}//switch

}//function


/**
 *
 *
 * @return unknown
 */
function identify_modifier_date_format() {
	return array (
		'name'           =>'date_format',
		'type'           =>'smarty_modifier',
		'nicename'       =>'Date Format',
		'description'    =>'Date format takes a timestamp, and turns it into a nice looking date',
		'authors'         =>'Dean Allen, Eaden McKee, Tobias Schlottke',
		'licence'         =>'Textpattern'
	);
}


/**
 *
 */
function bblog_modifier_date_format_help() {
?>
<p>Date format takes a timestamp, and turns it into a nice looking date.
<br />It is used as a modifier inside a template. For example, if you are in a
 <span class="tag">{post} {/post}</span> loop, you will have the varible {$post.dateposted}
 set which will contain a timestamp of when the post was made,
 and you will apply the date_format modifier to this tag.</p>
<p>Examples :<br />
<span class="tag">{$post.dateposted|date_format}</span> will return a date like May 26, 2003, 2:29 pm<br />
<span class="tag">{$post.dateposted|date_format:since}</span> will return Posted 7 hours, 3 minutes ago<br />
<span class="tag">{$post.dateposted|date_format:"F j, Y"}</span> will return May 26, 2003. The "F j, Y" is in php date() format, for more infomation see <a href="http://www.php.net/date">php.net/date</a></p>


<?php
}


/**
 *
 *
 * @param unknown $sum1
 * @param unknown $desc1
 * @param unknown $sum2
 * @param unknown $desc2
 * @return unknown
 */
function formatsince($sum1, $desc1, $sum2, $desc2) {
	if ($sum1 == 1 && $sum2 == 0) {
		$diff = "1 $desc1";
	}elseif ($sum1 == 1 && $sum2 == 1) {
		$diff = "1 $desc1, 1 $desc2";
	}elseif ($sum1 == 1 && $sum2 > 1) {
		$diff = "1 $desc1, $sum2 {$desc2}s";
	}elseif ($sum1 >1 && $sum2 == 1) {
		$diff = "$sum1 {$desc1}s, 1 $desc2";
	}elseif ($sum1 > 1 && $sum2 > 1) {
		$diff = "$sum1 {$desc1}s, $sum2 {$desc2}s";
	}else {
		return false;
	}

	return $diff;

}





/**
 *
 *
 * @param unknown $tstamp
 * @return unknown
 */
function since($tstamp) {
	$seconds = time() - $tstamp;

	$minutes = intval($seconds/60);
	$seconds = $seconds % 60;

	$hours = intval($minutes/60);
	$minutes = $minutes % 60;

	$days = intval($hours/24);
	$hours = $hours % 24;

	$weeks = intval($days/7);
	$days = $days % 7;

	$months = intval($weeks/4);
	$weeks = $weeks % 4;

	$years = intval($months/12);
	$months = $months % 12;

	if ($diff = formatsince($years, "year", $months, "month")) {

	}elseif ($diff = formatsince($months, "month", $days, "day")) {

	}elseif ($diff = formatsince($weeks, "week", $days, "day")) {

	}elseif ($diff = formatsince($days, "day", $hours, "hour")) {

	}elseif ($diff = formatsince($hours, "hour", $minutes, "minute")) {

	}elseif ($diff = formatsince($minutes, "minute", $seconds, "second")) {

	}else {
		$diff = "some seconds";
	}
	return "Posted ".$diff. " ago";
}


?>
