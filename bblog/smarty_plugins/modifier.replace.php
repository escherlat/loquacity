<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty replace modifier plugin
 *
 * Type:     modifier<br>
 * Name:     replace<br>
 * Purpose:  simple search/replace
 *
 * @link http://smarty.php.net/manual/en/language.modifier.replace.php
 *          replace (Smarty online manual)
 * @param string
 * @param string
 * @param string
 * @param unknown $string
 * @param unknown $search
 * @param unknown $replace
 * @return string
 */
function smarty_modifier_replace($string, $search, $replace) {
	return str_replace($search, $replace, $string);
}


/* vim: set expandtab: */

?>
