<?php
/**
 * ./bblog/bBlog_plugins/builtin.help.php
 *
 * @package default
 * @return unknown
 */


function identify_admin_help() {
	return array (
		'name'           =>'help',
		'type'           =>'admin',
		'nicename'       =>'Help',
		'description'    =>'Displays Help',
		'authors'         =>'Eaden McKee',
		'licence'         =>'GPL'
	);
}


if (is_numeric($_GET['pid']) or strlen($_GET['mod'])>0) {
	$bBlog->assign('pluginhelp', TRUE);

	if ($_GET['mod']) $pluginrow = $bBlog->get_row("select * from ".T_PLUGINS." where name='".$_GET['mod']."' and type='modifier'");

	else $pluginrow = $bBlog->get_row("select * from ".T_PLUGINS." where id='".$_GET['pid']."'");

	$bBlog->assign("title", "Help: ".$pluginrow->type." : ".$pluginrow->nicename);
	$bBlog->assign("helptext", $pluginrow->help);
	$bBlog->assign("type", $pluginrow->type);
	$bBlog->assign("nicename", $pluginrow->nicename);
	$bBlog->assign("description", $pluginrow->description);
	$bBlog->assign("authors", $pluginrow->authors);
	$bBlog->assign("license", $pluginrow->license);

} elseif ($_GET['modifierhelp']) {
	$bBlog->assign('title', 'Modifier Help');
	$bBlog->assign('inline', TRUE);
	$helptext = "<p>Modifiers are an easy way to enable you to make links and other web features without knowing html. There are a few to choose fshowcloserom, select one to get instructions.</p><ul class='form'>";
	$modifiers = $bBlog->get_results("select * from ".T_PLUGINS." where type='modifier' order by nicename");
	foreach ($modifiers as $mod) {
		$helptext .= "<li><a href='index.php?b=help&amp;inline=true&amp;pid={$mod->id}'>{$mod->nicename}</a> - {$mod->description}</li>";
	}
	$helptext .="</ul>";
	$bBlog->assign('helptext', $helptext);
} else {
	$bBlog->assign("title", "Help");
	$bBlog->assign("helptext", 'Visit the <a href="http://www.bblog.com/docs/" target="_blank">bBlog online documentation</a> or the <a href="http://www.bBlog.com/forum.php" target="_blank">bBlog forum</a> for help.');
}
$bBlog->display("help.html");
?>
