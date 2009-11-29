<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty wordwrap modifier plugin
 *
 * Type:     modifier<br>
 * Name:     wordwrap<br>
 * Purpose:  wrap a string of text at a given length
 *
 * @link http://smarty.php.net/manual/en/language.modifier.wordwrap.php
 *          wordwrap (Smarty online manual)
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param unknown $string
 * @param unknown $length (optional)
 * @param unknown $break  (optional)
 * @param unknown $cut    (optional)
 * @return string
 */
function smarty_modifier_wordwrap($string, $length=80, $break="\n", $cut=false) {
	return wordwrap($string, $length, $break, $cut);
}


?>
