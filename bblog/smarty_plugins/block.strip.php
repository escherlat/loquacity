<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty {strip}{/strip} block plugin
 *
 * Type:     block function<br>
 * Name:     strip<br>
 * Purpose:  strip unwanted white space from text<br>
 *
 * @link http://smarty.php.net/manual/en/language.function.strip.php {strip}
 *       (Smarty online manual)
 * @param array   unused, no parameters for this block
 * @param string  content of {strip}{/strip} tags
 * @param Smarty  clever method emulation
 * @param unknown $params
 * @param unknown $content
 * @param unknown $smarty  (reference)
 * @return string $content stripped of whitespace
 */
function smarty_block_strip($params, $content, &$smarty) {
	/* Reformat data between 'strip' and '/strip' tags, removing spaces, tabs and newlines. */
	$_strip_search = array(
		"![\t ]+$|^[\t ]+!m", // remove leading/trailing space chars
		'%[\r\n]+%m'); // remove CRs and newlines
	$_strip_replace = array(
		'',
		'');
	return preg_replace($_strip_search, $_strip_replace, $content);
}


/* vim: set expandtab: */

?>
