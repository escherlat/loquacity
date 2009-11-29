<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty cat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     cat<br>
 * Date:     Feb 24, 2003
 * Purpose:  catenate a value to a variable
 * Input:    string to catenate
 * Example:  {$var|cat:"foo"}
 *
 * @link http://smarty.php.net/manual/en/language.modifier.cat.php cat
 *          (Smarty online manual)
 * @author  Monte Ohrt <monte@ispi.net>
 * @version 1.0
 * @param string
 * @param string
 * @param unknown $string
 * @param unknown $cat
 * @return string
 */
function smarty_modifier_cat($string, $cat) {
	return $string . $cat;
}


/* vim: set expandtab: */

?>
