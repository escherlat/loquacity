<?php
/**
 * Smarty plugin
 *
 * Type:     modifier<br>
 * Name:     printr<br>
 * Date:     May 26, 2003
 * Purpose:  returnes a printr() of the varible wrapped in a <pre></pre>
 * Input:<br>
 *         - contents = contents to replace
 *         - preceed_test = if true, includes preceeding break tags
 *           in replacement
 * Example:  {$text|printr}
 * @version  1.0
 * @author   Eaden McKee  <email@eadz.co.nz>
 * @param string
 * @return string
 */
function smarty_modifier_printr(&$var)
{
    ob_start();
    print_r($var);
    $o = ob_get_contents();
    ob_end_clean();
    return "<pre>".$o."</pre>";
}

?>
