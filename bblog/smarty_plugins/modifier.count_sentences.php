<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty count_sentences modifier plugin
 *
 * Type:     modifier<br>
 * Name:     count_sentences
 * Purpose:  count the number of sentences in a text
 *
 * @link http://smarty.php.net/manual/en/language.modifier.count.paragraphs.php
 *          count_sentences (Smarty online manual)
 * @param string
 * @param unknown $string
 * @return integer
 */
function smarty_modifier_count_sentences($string) {
	// find periods with a word before but not after.
	return preg_match_all('/[^\s]\.(?!\w)/', $string, $match);
}


/* vim: set expandtab: */

?>
