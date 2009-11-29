<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty indent modifier plugin
 *
 * Type:     modifier<br>
 * Name:     indent<br>
 * Purpose:  indent lines of text
 *
 * @link http://smarty.php.net/manual/en/language.modifier.indent.php
 *          indent (Smarty online manual)
 * @param string
 * @param integer
 * @param string
 * @param unknown $string
 * @param unknown $chars  (optional)
 * @param unknown $char   (optional)
 * @return string
 */
function smarty_modifier_indent($string, $chars=4, $char=" ") {
	return preg_replace('!^!m', str_repeat($char, $chars), $string);
}


?>
