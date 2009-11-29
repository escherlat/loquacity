<?php
/**
 * ./bblog/bBlog_plugins/function.calendar.php
 *
 * @package default
 */


/*
    function.calendar.php - calendar plugin
*/


/**
 *
 *
 * @return unknown
 */
function identify_function_calendar() {

	$help = '
	<p>
	This plugin displays the calendar module. It uses calendar.html as a template.<BR />
	it has two parameter: week_start, which allows you two choose what day is the<BR />
	first day a week (default is 1 (Monday). Sunday is 0, Monday is 1, ...) and <BR />
	locale, which can be used to force the script to another locale (ie de_DE for<BR />
	German, etc.). Default locale is whatever your server has been set to use as <BR />
	default.
	</P>';

	return array (
		'name'  => 'calendar',
		'type'  => 'function',
		'nicename' => 'Calendar',
		'description' => 'Makes a calendar of the current month',
		'authors' => 'Tanel Raja',
		'licence' => 'GPL',
		'help'  => $help
	);

}


/**
 *
 *
 * @param unknown $params
 * @param unknown $bBlog  (reference)
 */
function smarty_function_calendar($params, &$bBlog) {

	$date = getdate();

	$today = $date["mday"];
	$month = $date["mon"];
	$year = $date["year"];

	$new_month = $_GET["month"];
	$new_year = $_GET["year"];

	if ($new_month && $new_year) {
		$date = getdate(mktime(0, 0, 0, $new_month, 1, $new_year));
		$show_month = $date["mon"];
		$show_year = $date["year"];
	} else {
		$show_month = $month;
		$show_year = $year;
	}


	$q = $bBlog->make_post_query(
		array(
			"where" => " AND month(FROM_UNIXTIME(posttime)) = $show_month and year(FROM_UNIXTIME(posttime)) = $show_year ",
			"num"=>"999"
		)
	);

	$dayindex = array();
	global $dayindex;
	$posts = $bBlog->get_posts($q);
	if (is_array($posts)) {

		foreach ($posts as $post) {
			$d = date('j', $post['posttime']);
			$dayindex[$d][] = array(
				"id"    => $post['post'],
				"title" => $post['title'],
				"url"   => $bBlog->_get_entry_permalink($post['postid'])
			);
		}

	}


	$left_year = $right_year = $show_year;

	$left_month = $show_month - 1;
	if ($left_month < 1) {
		$left_month = 12;
		$left_year--;
	}
	$right_month = $show_month + 1;
	if ($right_month > 12) {
		$right_month = 1;
		$right_year++;
	}

	$bBlog->assign("left", $_SERVER["PHP_SELF"] . "?month=$left_month&year=$left_year");
	$bBlog->assign("right", $_SERVER["PHP_SELF"] . "?month=$right_month&year=$right_year");

	$bBlog->assign("header", strftime("%B %Y", mktime(0, 0, 0, $show_month, 1, $show_year)));

	$first_date = mktime(0, 0, 0, $show_month, 1, $show_year);
	$date = getdate($first_date);
	$first_wday = $date["wday"];
	$last_date = mktime(0, 0, 0, $show_month + 1, 0, $show_year);
	$date = getdate($last_date);
	$last_day = $date["mday"];

	$wday = "";
	// echo($params["locale"]);
	if ($params["locale"])
		@setlocale(LC_TIME, $params["locale"]);
	$week_start = $params["week_start"];
	if ($week_start < 0 || $week_start > 6) {
		$week_start = 1;
	}

	for ($counter = $week_start; $counter < $week_start + 7; $counter++) {
		if ($counter > 6)
			$wday[] = strftime("%a", mktime(0, 0, 0, 3, $counter - 7, 2004));
		else
			$wday[] = strftime("%a", mktime(0, 0, 0, 3, $counter, 2004));
	}

	$bBlog->assign("wday", $wday);

	$week_array = "";
	$month_array = "";

	$pre_counter = $first_wday - $week_start;
	if ($pre_counter < 0)
		$pre_counter += 7;

	$day = 1;
	while (true) {

		$week_array = "";

		for ($counter = 0; $counter < 7; $counter++) {

			if ($day > $last_day) {
				$week_array[] = array(
					0 => false,
					1 => "&nbsp;",
					2 => false
				);
			} else if ($pre_counter > 0) {
					$week_array[] = array(
						0 => false,
						1 => "&nbsp;",
						2 => false
					);
					$pre_counter--;
				} else {
				getDateLink($day, &$values);

				$week_array[] = array(
					0 => (($dayindex["$day"])?true:false),
					1 => $day,
					2 => (($day == $today && $month == $show_month && $year == $show_year)?true:false)
				);
				$day++;
			}

		}

		$month_array[] = $week_array;

		if ($day > $last_day)
			break;

	}

	$bBlog->assign("month", $month_array);
	$bBlog->assign("values", $values);

	$bBlog->display("calendar.html", FALSE);

}


/**
 *
 *
 * @param unknown $day
 * @param unknown $values
 */
function getDateLink($day, $values) {

	global $dayindex;

	if (!$dayindex[$day]) {
		return;
	} else {
		foreach ($dayindex[$day] as $item) {
			$script .= sprintf("&raquo; <a href='%s'>%s</a><br>", $item['url'], $item['title']);
		}

		$script = str_replace('"', '\"', $script);
		$values .= "cc[$day]=\"$script\";\n";

	}
}


?>
