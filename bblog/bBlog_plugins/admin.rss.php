<?php
/**
 * ./bblog/bBlog_plugins/admin.rss.php
 *
 * @package default
 */


/**
 * admin.rss.php - administer rss
 *
 * @return unknown
 */
function identify_admin_rss() {
	$help = '
<p>
<i>function </i><b>Get RSS</b><br>
</p>
<p><b><i>example: </b></i>{getrss} - select random RSS Feed<br>
<p><b><i>example: </b></i>{getrss id=1} - select defined RSS Feed
</p>';

	return array (
		'name'           =>'rss',
		'type'             =>'admin',
		'nicename'     =>'RSS Fetcher',
		'description'   =>'Edit RSS Feeds',
		'authors'        =>'Martin Konicek <martin.konicek@atlas.cz>',
		'licence'         =>'GPL',
		'template'  => 'rss.html',
		'help'     => $help
	);
}


/**
 *
 *
 * @param unknown $bBlog (reference)
 */
function admin_plugin_rss_run(&$bBlog) {

	$pole = "";
	for ($i=1; $i<10; $i++) {
		if ((isset($_POST['sending'])) && ($_POST['sending']=="true")) {
			$id = $_POST[id.$i];
			$ch = $_POST[ch.$i];
			$update_query = "UPDATE ".T_RSS." SET `url` = '".$id."',`input_charset` = '".$ch."' WHERE `id` = '".$i."' LIMIT 1 ;";
			$bBlog->query($update_query);
		}

		$query = "select * from ".T_RSS." where id=".$i.";";
		$row = $bBlog->get_row($query);
		$rssurl = $row->url;
		$w1250 = "";
		if ($row->input_charset=="W1250") {$w1250=" selected";}
		$utf8 = "";
		if ($row->input_charset=="UTF8") {$utf8=" selected";}

		if ($i / 2 == floor($i /2)) $class = 'high';
		else $class = 'low';

		$pole.='<tr class="'.$class.'"><td>'.$i.'</td><td><input type="text" name="id'.$i.'" size="20" value="'.$rssurl.'" class="text" /></td><td><select name="ch'.$i.'">';
		$pole.='<option>I88592</option>';
		$pole.='<option'.$w1250.'>W1250</option>';
		$pole.='<option'.$utf8.'>UTF8</option>';
		$pole.='</select></td></tr>';
	}

	$bBlog->assign('pole', $pole);
}


?>
