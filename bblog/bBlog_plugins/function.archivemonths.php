<?php
/*********************************\
ÊÊbbBlog Plugin: 
ÊÊÊBuild Archive Months Listing 
Copyright
ÊÊÊ2004 Chris Boulton 
ÊÊÊ<c.boulton@mybboard.com> 
\*********************************/
function identify_function_archivemonths() {
return array("name" => "archivemonths",
"type" => "function",
"nicename" => "Archive Month Listing",
"description" => "Generates a list of archive months",
"authors" => "c.boulton@mybboard.com");
}
function smarty_function_archivemonths($params, &$bBlog) {
$num = 10;
$sep = "<br />";
$year = "";
$showcount = 0;
extract($params);
if($year) {
$where = " AND YEAR(FROM_UNIXTIME(posttime)) = '$year'";
}
if($num) {
$num = " LIMIT 0, $num";
}
$query = mysql_query("SELECT DISTINCT YEAR(FROM_UNIXTIME(posttime)) AS year, MONTH(FROM_UNIXTIME(posttime)) AS month, COUNT(postid) AS posts FROM ".T_POSTS." $where GROUP BY YEAR(FROM_UNIXTIME(posttime)), MONTH(FROM_UNIXTIME(posttime)) ORDER BY posttime DESC $limit;");
while($month = mysql_fetch_array($query)) {
if($month[month] < 10) {
$month[month] = "0$month[month]";
}
$monthslist .= "<a href=\"archives.php?month=$month[month]&year=$month[year]\">".getmonthfriendlyname($month[month])." $month[year]</a> ";
if($showcount) {
$monthslist .= " <i>$month[posts]</i>";
}
$monthslist .= "$sep";
}
return $monthslist;
}
function getmonthfriendlyname($month) {
$tstamp = mktime(0, 0, 0, $month);
return date("F", $tstamp);
}
?>