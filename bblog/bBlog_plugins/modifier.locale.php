<?php
/**
 * ./bblog/bBlog_plugins/modifier.locale.php
 *
 * @package default
 */


/**
 * modifier.locale.php - smarty modifier to set locale
 *
 * @param unknown $stream
 * @param unknown $locale
 * @return unknown
 */
function smarty_modifier_locale($stream, $locale) {
	setlocale("LC_ALL", $locale);
	setlocale("LC_TIME", $locale);
	setlocale("LC_LANG", $locale);

	return $stream;
}


/**
 *
 *
 * @return unknown
 */
function identify_modifier_locale() {
	return array (
		'name'           =>'locale',
		'type'           =>'smarty_modifier',
		'nicename'       =>'Set Locale',
		'description'    =>'Set locale and return unmodified input data',
		'authors'         =>'Sebastian Werner',
		'licence'         =>'GPL'
	);
}


/**
 *
 */
function bblog_modifier_locale_help() {
?>
<p>Set Locale</p>
<pre>
{$post.posttime|locale:"de_DE"|data_format:"date"}
</pre>
<?php
}


?>
