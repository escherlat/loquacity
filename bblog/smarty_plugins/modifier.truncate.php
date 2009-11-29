<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string.
 *
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param unknown $string
 * @param unknown $length      (optional)
 * @param unknown $etc         (optional)
 * @param unknown $break_words (optional)
 * @return string
 */
function smarty_modifier_truncate($string, $length = 80, $etc = '...',
	$break_words = false) {
	if ($length == 0)
		return '';

	if (strlen($string) > $length) {
		$length -= strlen($etc);
		if (!$break_words)
			$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));

		return substr($string, 0, $length).$etc;
	} else
		return $string;
}


/* vim: set expandtab: */

?>
