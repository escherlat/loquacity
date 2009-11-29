<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty capitalize modifier plugin
 *
 * Type:     modifier<br>
 * Name:     capitalize<br>
 * Purpose:  capitalize words in the string
 *
 * @link http://smarty.php.net/manual/en/language.modifiers.php#LANGUAGE.MODIFIER.CAPITALIZE
 *      capitalize (Smarty online manual)
 * @param string
 * @param unknown $string
 * @return string
 */
function smarty_modifier_capitalize($string) {
	return ucwords($string);
}


?>
