<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty plugin
 *
 * Type:     modifier<br>
 * Name:     none<br>
 * Date:     May 26, 2003
 * Purpose:  nothing at all!
 * Input:<br>
 *         - contents = contents to replace
 *         - preceed_test = if true, includes preceeding break tags
 *           in replacement
 * Example:  {$text|none}
 * @version  1.0
 * @author   Eaden McKee  <email@eadz.co.nz>
 * @param string
 * @return string
 */

function smarty_modifier_nohtml(&$string)
{
    return htmlspecialchars($string);
}

?>
