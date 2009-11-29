<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty strip_tags modifier plugin
 *
 * Type:     modifier<br>
 * Name:     strip_tags<br>
 * Purpose:  strip html tags from text
 *
 * @link http://smarty.php.net/manual/en/language.modifier.strip.tags.php
 *          strip_tags (Smarty online manual)
 * @param string
 * @param boolean
 * @param unknown $string
 * @param unknown $replace_with_space (optional)
 * @return string
 */
function smarty_modifier_strip_tags($string, $replace_with_space = true) {
	if ($replace_with_space)
		return preg_replace('!<[^>]*?>!', ' ', $string);
	else
		return strip_tags($string);
}


/* vim: set expandtab: */

?>
