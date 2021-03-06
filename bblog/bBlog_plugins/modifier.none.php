<?php
/**
 * ./bblog/bBlog_plugins/modifier.none.php
 *
 * @package default
 */


/**
 *
 *
 * @return unknown
 */
function identify_modifier_none() {
	return array (
		'name'           =>'none',
		'type'           =>'modifier',
		'nicename'       =>'None',
		'description'    =>'Does nothing at all to your text',
		'authors'         =>'Eaden McKee',
		'licence'         =>'GPL',
		'help'             =>'There is not much to say here... your post will stay exactly as you type it and will not be changed'
	);
}


/**
 *
 *
 * @param unknown $string (reference)
 * @return unknown
 */
function smarty_modifier_none(&$string) {
	return $string;
}


?>
