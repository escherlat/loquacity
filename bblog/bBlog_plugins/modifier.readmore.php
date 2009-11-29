<?php
/**
 * ./bblog/bBlog_plugins/modifier.readmore.php
 *
 * @package default
 */


/**
 * modifier.readmore.php
 *
 * @param unknown $text
 * @param unknown $postid
 * @param unknown $readmoretext (optional)
 * @param unknown $wordcount    (optional)
 * @return unknown
 */
function smarty_modifier_readmore($text, $postid, $readmoretext="read more", $wordcount=true) {

	$PREG_TAG = '/<!--\s*(\/?read\s*more:?[^-]*)\s*-->/';
	$PREG_READMORE_START = '/^read\s*more/';
	$PREG_READMORE_END = '/^\s*\/read\s*more\s*/';

	global $bBlog;
	$link = $bBlog->_get_entry_permalink($postid);

	$textar = preg_split($PREG_TAG, $text, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
	$text = "";

	$cuttingout = false;
	$i = -1;

	foreach ( $textar as $textbit ) {
		$i++;
		$textbit = trim($textbit);

		if ( preg_match($PREG_READMORE_START, $textbit) ) {

			// check for nested cuts
			if ($cuttingout)
				continue;

			$text .= '<a href="'.$link.'">';
			// fix default string
			if ( !strpos($textbit, ':') ) {
				$text .= $readmoretext;
			} else {
				$text .= substr($textbit, strpos($textbit, ':')+1);
			}
			$text .= '</a>';

			// print wordcount
			if ($wordcount) {
				$text .= '&nbsp;<em>('.count(explode(' ', $textar[$i+1])).' words)</em>';
			}

			$cuttingout = true;
			continue;
		}
		else if ( preg_match($PREG_READMORE_END, $textbit) ) {
				$cuttingout = false;
				continue;
			}
		else if (!$cuttingout) {
				$text.=$textbit;
			}

	} // end foreach

	return $text;
}


/**
 *
 *
 * @return unknown
 */
function identify_modifier_readmore() {
	return array (
		'name'           =>'readmore',
		'type'           =>'smarty_modifier',
		'nicename'       =>'Read More',
		'description'    =>'Chops a post short with a readmore link',
		'authors'        =>'Tim Lucas <t.lucas-toolmantim.com>',
		'licence'        =>'GPL',
		'help'      =>'Usage:<br>
<p>Use the readmore modifier on the {$post.body} tag, to cut off text at the HTML
comment &lt;!-- readmore --&gt; .</p>
<p>There are 4 parameters, <strong>post id</strong> (number), <strong>default text</strong> (string),
<strong>word count</strong> (true/false) and <strong>word count text</strong> (string).</p>
<p><i>postid</i> is the id of the post (used to create the link) - required.</p>
<p><i>default text</i> is the default text used for the readmore link (default is "Read more") - optional.</p>
<p><i>word count</i> is a toggle to print the word count of the cutout text - optional.</p>
<p><i>word count text</i> allows you to localise the word count (default is "words") - optional.</p>
<p>You can cut sections of text by using &lt;!-- readmore --&gt; in conjuction
with &lt!-- /readmore --&gt;.
<p><strong>Example template usage:</strong></p>
<p>{$post.body|readmore:$post.postid}</p>
<p>{$post.body|readmore:$post.postid:"Read more..."}</p>
<p>{$post.body|readmore:$post.postid:"Read more...":false}</p>
<p>{$post.body|readmore:$post.postid:"keep on reading":true:"whispers"}</p>
<p><strong>Example post usage:</strong></p>
<p><i>Cutting the post off and using the default readmore text</i>:</p>
<p>My amazing story<br/>
&lt;!-- readmore --&gt;<br/>
of my amazing story... it\'s amaaazing</p>
<p><i>Cutting a section of text out and replacing with own text link</i>:</p>
<p>My amazing story<br/>
&lt;!-- readmore: read it now! --&gt;<br/>
This is my hidden story<br/>
&lt;!-- /readmore --&gt;<br/>
And back to the post again.</p>');
}


?>
