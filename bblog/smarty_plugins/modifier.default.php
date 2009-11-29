<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty default modifier plugin
 *
 * Type:     modifier<br>
 * Name:     default<br>
 * Purpose:  designate default value for empty variables
 *
 * @link http://smarty.php.net/manual/en/language.modifier.default.php
 *          default (Smarty online manual)
 * @param string
 * @param string
 * @param unknown $string
 * @param unknown $default (optional)
 * @return string
 */
function smarty_modifier_default($string, $default = '') {
	if (!isset($string) || $string === '')
		return $default;
	else
		return $string;
}


/* vim: set expandtab: */

?>
