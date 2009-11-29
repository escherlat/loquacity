<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty count_characters modifier plugin
 *
 * Type:     modifier<br>
 * Name:     count_characteres<br>
 * Purpose:  count the number of characters in a text
 *
 * @link http://smarty.php.net/manual/en/language.modifier.count.characters.php
 *          count_characters (Smarty online manual)
 * @param string
 * @param boolean include whitespace in the character count
 * @param unknown $string
 * @param unknown $include_spaces (optional)
 * @return integer
 */
function smarty_modifier_count_characters($string, $include_spaces = false) {
	if ($include_spaces)
		return strlen($string);

	return preg_match_all("/[^\s]/", $string, $match);
}


/* vim: set expandtab: */

?>
