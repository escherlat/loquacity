<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Replace nocache-tags by results of the corresponding non-cacheable
 * functions and return it
 *
 * @param unknown $params
 * @param unknown $smarty (reference)
 * @return string
 */
function smarty_core_process_compiled_include($params, &$smarty) {
	$_cache_including = $smarty->_cache_including;
	$smarty->_cache_including = true;

	$_return = $params['results'];
	foreach ($smarty->_cache_serials as $_include_file_path=>$_cache_serial) {
		$_return = preg_replace_callback('!(\{nocache\:('.$_cache_serial.')#(\d+)\})!s',
			array(&$smarty, '_process_compiled_include_callback'),
			$_return);
	}
	$smarty->_cache_including = $_cache_including;
	return $_return;
}


?>
