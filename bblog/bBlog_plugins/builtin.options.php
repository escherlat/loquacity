<?php
/**
 * ./bblog/bBlog_plugins/builtin.options.php
 *
 * @package default
 */


/*
** bBlog Weblog http://www.bblog.com/
** Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/**
 *
 *
 * @return unknown
 */
function identify_admin_options() {
	return array (
		'name'           =>'options',
		'type'           =>'builtin',
		'nicename'       =>'Options',
		'description'    =>'Allows you to change options',
		'authors'         =>'Eaden McKee',
		'licence'         =>'GPL',
		'help'            =>''
	);
}


/**
 *
 *
 * @return unknown
 */
function get_options() {
	$options = array();

	$options = array(
		array(
			"name"  => "EMAIL",
			"label" => "Email Address",
			"value" => C_EMAIL,
			"type"  => "email"
		),

		array(
			"name" => "BLOGNAME",
			"label" => "Blog Name",
			"value" => C_BLOGNAME,
			"type"  => "text"
		),

		array(
			"name" => "BLOG_DESCRIPTION",
			"label" => "Blog Description",
			"value" => C_BLOG_DESCRIPTION,
			"type"  => "text"
		),

		array(
			"name" => "TEMPLATE",
			"label" => "Template",
			"value" => C_TEMPLATE,
			"type"  => "templateselect"
		),

		array(
			"name" => "DEFAULT_MODIFIER",
			"label" => "Default Modifier",
			"value" => C_DEFAULT_MODIFIER,
			"type" => "modifierselect"
		),

		array(
			"name" => "DEFAULT_STATUS",
			"label" => "Default Post Status",
			"value" => C_DEFAULT_STATUS,
			"type" => "statusselect"
		),
		array(
			"name"  => "CHARSET",
			"label" => "Character Set",
			"value" => C_CHARSET,
			"type"  => "charsetselect"
		),

		array(
			"name"  => "DIRECTION",
			"label" => "Writing/reading direction",
			"value" => C_DIRECTION,
			"type"  => "directionselect"
		),

		array(
			"name"  => "PING",
			"label" => "Notify websites of new posts. seperate with comma, e.g. weblogs.com/RPC2,www.bblog.com/ping.php,blo.gs/",
			"value" => C_PING,
			"type"  => "text"
		),

		array(
			"name"  => "COMMENT_MODERATION",
			"label" => "Require your approval before comments appear",
			"value" => C_COMMENT_MODERATION,
			"type"  => "commentmoderation"
		),

		array(
			"name"  => "NOTIFY",
			"label" => "Send notifications via email for new comments",
			"value" => C_NOTIFY,
			"type"  => "truefalse"
		),
		array(
			"name"  => "META_KEYWORDS",
			"label" => "META Keywords for search engines",
			"value" => C_META_KEYWORDS,
			"type"  => "text"
		),
		array(
			"name"  => "META_DESCRIPTION",
			"label" => "META Description for search engines",
			"value" => C_META_DESCRIPTION,
			"type"  => "text"
		),
		array(
			"name"  => "COMMENT_TIME_LIMIT",
			"label" => "Comment Flood Protection ( minutes ) Set to 0 to disable.",
			"value" => C_COMMENT_TIME_LIMIT,
			"type"  => "text"
		)


	);

	return $options;
}


$bBlog->get_modifiers();

$optionformrows = array();

$options = get_options();

if ((isset($_POST['submit'])) && ($_POST['submit'] == 'Save Options')) { // saving options..
	$updatevars = array();
	foreach ($options as $option) {

		if (!isset($_POST[$option['name']])) break;

		switch ($option['type']) {
		case "text"  :
		case "email" :
		case "url"   :
			$updatevars[] = array(
				"name" =>$option['name'],
				"value" => my_addslashes($_POST[$option['name']])
			);
			break;
		case "password" :
			if ($_POST[$option['name']] != '')

				$updatevars[] = array(
					"name" => $option['name'],
					"value" => md5($_POST[$option['name']])
				);
			break;

		case "templateselect" :
			// make sure we're not being poked.
			if (ereg('^[[:alnum:]]+$', $_POST[$option['name']])) {
				$updatevars[] = array(
					"name" => $option['name'],
					"value" => strtolower($_POST[$option['name']])
				);

			}
			break;

		case "statusselect" :
			if ($_POST[$option['name']] == 'live')
				$updatevars[]= array(
					"name" => $option['name'],
					"value" => 'live'
				);

			if ($_POST[$option['name']] == 'draft')
				$updatevars[]= array(
					"name" => $option['name'],
					"value" => 'draft'
				);
			break;

		case "charsetselect" :
			//check all charsets
			foreach ($charsets as $charset) {
				//if submitted is one of our valid charsets
				if ( $_POST[$option['name']] == $charset['value'] ) {

					$updatevars[] = array(
						"name" => $option['name'],
						"value" => $charset['value']
					);

				}//if
			}//foreach
			break;

		case "directionselect":
			if ($_POST[$option['name']] == 'LTR')
				$updatevars[]= array(
					"name" => $option['name'],
					"value" => 'LTR'
				);
			if ($_POST[$option['name']] == 'RTL')
				$updatevars[]= array(
					"name" => $option['name'],
					"value" => 'RTL'
				);

			break;


		case "commentmoderation" :
			if ($_POST[$option['name']] == 'none')
				$updatevars[]= array(
					"name" => $option['name'],
					"value" => 'none'
				);

			if ($_POST[$option['name']] == 'all')
				$updatevars[]= array(
					"name" => $option['name'],
					"value" => 'all'
				);
			if ($_POST[$option['name']] == 'urlonly')
				$updatevars[]= array(
					"name" => $option['name'],
					"value" => 'urlonly'
				);
			break;

		case "modifierselect" :
			if (ereg('^[[:alnum:]]+$', $_POST[$option['name']]))
				$updatevars[] = array(
					"name"=>$option['name'],
					"value"=>$_POST[$option['name']]
				);

			break;
		case "truefalse" :
			$updatevars[] = array(
				"name"=>$option['name'],
				"value"=>$_POST[$option['name']]
			);
			break;
		default: break;


		} // switch
	} // foreach


} // if
if ((isset($_POST['submit'])) && ($_POST['submit'] == 'Save Options')) {
	foreach ($updatevars as $update) {
		$sql = "UPDATE ".T_CONFIG." SET VALUE='".$update['value']."' WHERE `name`='".$update['name']."'";
		/*echo "<pre>";
   var_dump($sql);
   //var_dump($update);
   echo "</pre>";*/
		$bBlog->query($sql);
	} // foreach
	$bBlog->assign("showmessage", TRUE);
	$bBlog->assign("showoptions", 'no');
	$bBlog->assign("message_title", "Options Updated");
	$bBlog->assign("message_content", "Your changes have been saved.<br><a href='index.php?b=options&r=".rand(20, 214142124)."'>Click here to continue</a>");

} else {

	foreach ($options as $option) {
		$formleft = $option['label'];
		switch ($option['type']) {
		case "text"  :
		case "email" :
		case "url"   :
			$formright = '<input type="text" name="'.$option['name'].'"
		                    class="bf" value="'.$option['value'].'">';
			break;

		case "password" :
			$formright = '<input type="password" name="'.$option['name'].'"
                                    class="bf" value="'.$option['value'].'">';
			break;

		case "templateselect" :
			$formright = '<select name="'.$option['name'].'" class="bf">';
			$d = dir("templates");
			while (false !== ($entry = $d->read())) {
				if (ereg("^[a-z]{3,20}$", $entry)) {
					$formright .= "<option value=\"$entry\"";
					if ($option['value'] == $entry) $formright .=" selected";
					$formright .= ">$entry</option>";
				}
			}
			$d->close();
			$formright .= '</select>';
			break;

		case "charsetselect":
			$formright = '<select name="'.$option['name'].'" class="bf">';
			foreach ($charsets as $charset) {
				$formright .='<option value="'.$charset[value].'" ';
				if ($charset[value] == C_CHARSET) $formright .='selected';
				$formright .='>'.$charset[description].'</option>';
			}
			$formright .= '</select>';
			break;

		case "directionselect":
			$formright = '<select name="'.$option['name'].'" class="bf">';
			$formright .= '<option value="LTR"';
			if (C_DIRECTION == 'LTR') $formright .= 'selected';
			$formright .= '>LTR (default)</option>';

			$formright .='<option value="RTL"';
			if (C_DIRECTION == 'RTL') $formright .= 'selected';
			$formright .='>RTL (if supported by template)</option>';
			$formright .='</select>';

			break;


		case "statusselect" :
			$formright = '<select name="'.$option['name'].'" class="bf">';
			$formright .= '<option value="live" ';
			if (C_DEFAULT_STATUS == 'live') $formright .= 'selected';
			$formright .= '>Live'.'</option>';
			$formright .= '<option value="draft" ';
			if (C_DEFAULT_STATUS == 'draft') $formright .= 'selected';
			$formright .= '>Draft'.'</option>';
			$formright .= '</select>';
			break;

		case "truefalse" :
			$formright = '<select name="'.$option['name'].'" class="bf">';
			$formright .= '<option value="true" ';
			if ($option['value'] == 'true') $formright .= 'selected';
			$formright .= '>Yes'.'</option>';
			$formright .= '<option value="false" ';
			if ($option['value'] == 'false') $formright .= 'selected';
			$formright .= '>No'.'</option>';
			$formright .= '</select>';
			break;

		case "modifierselect" :
			$formright = '<select name="'.$option['name'].'" class="bf">';
			foreach ($bBlog->modifiers as $mod) {
				$formright .= '<option value="'.$mod->name.'" ';
				if (C_DEFAULT_MODIFIER == $mod->name) $formright .= 'selected';
				$formright .= '>'.$mod->nicename.'</option>';
			}
			$formright .= '</select>';
			break;

		case "commentmoderation" :
			$formright = '<select name="'.$option['name'].'" class="bf">';

			$formright .= '<option value="none" ';
			if (C_COMMENT_MODERATION == 'none') $formright .= 'selected';
			$formright .= '>No Moderation (not recommended!)</option>';

			$formright .= '<option value="urlonly" ';
			if (C_COMMENT_MODERATION == 'urlonly') $formright .= 'selected';
			$formright .= '>Only for comments with links (recommended)</option>';

			$formright .= '<option value="all" ';
			if (C_COMMENT_MODERATION == 'all') $formright .= 'selected';
			$formright .= '>Moderate All Comments</option>';


			$formright .= '</select>';
			break;

		default: $formright = ''; break;

		}
		$optionrows[] = array("left" => $formleft, "right" => $formright);
		// have help here too someday :)


	}
	$bBlog->assign("optionrows", $optionrows);
} // end of else
$bBlog->display("options.html");








?>
