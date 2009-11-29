<?php
/**
 * Smarty plugin
 *
 * @subpackage plugins
 * @package Smarty
 */


/**
 * Smarty {assign} function plugin
 *
 * Type:     function<br>
 * Name:     assign<br>
 * Purpose:  assign a value to a template variable
 *
 * @link http://smarty.php.net/manual/en/language.custom.functions.php#LANGUAGE.FUNCTION.ASSIGN {assign}
 *       (Smarty online manual)
 * @param array   Format: array('var' => variable name, 'value' => value to assign)
 * @param Smarty
 * @param unknown $params
 * @param unknown $smarty (reference)
 */
function smarty_function_assign($params, &$smarty) {
	extract($params);

	if (empty($var)) {
		$smarty->trigger_error("assign: missing 'var' parameter");
		return;
	}

	if (!in_array('value', array_keys($params))) {
		$smarty->trigger_error("assign: missing 'value' parameter");
		return;
	}

	$smarty->assign($var, $value);
}


/* vim: set expandtab: */

?>
