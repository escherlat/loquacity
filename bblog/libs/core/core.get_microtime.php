<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Get seconds and microseconds
 *
 * @param unknown $params
 * @param unknown $smarty (reference)
 * @return double
 */
function smarty_core_get_microtime($params, &$smarty) {
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = (double)($mtime[1]) + (double)($mtime[0]);
	return $mtime;
}


/* vim: set expandtab: */

?>
