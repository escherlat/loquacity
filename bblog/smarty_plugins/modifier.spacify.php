<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty spacify modifier plugin
 *
 * Type:     modifier<br>
 * Name:     spacify<br>
 * Purpose:  add spaces between characters in a string
 *
 * @link http://smarty.php.net/manual/en/language.modifier.spacify.php
 *          spacify (Smarty online manual)
 * @param string
 * @param string
 * @param unknown $string
 * @param unknown $spacify_char (optional)
 * @return string
 */
function smarty_modifier_spacify($string, $spacify_char = ' ') {
	return implode($spacify_char,
		preg_split('//', $string, -1, PREG_SPLIT_NO_EMPTY));
}


/* vim: set expandtab: */

?>
