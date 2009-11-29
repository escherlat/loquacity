<?php
/**
 * ./bblog/bBlog_plugins/modifier.bbcode.php
 *
 * @package default
 */


// modifier.bbcode.php -  bBlog text formating plugin,
// modifier.bbcode.php - Converts BBCode style tags to HTML
// modifier.bbcode.php - and makes URLs clickable


/**
 *
 *
 * @return unknown
 */
function identify_modifier_bbcode() {
	$help = bblog_modifier_bbcode_help();
	return array (
		'name'           =>'bbcode',
		'type'           =>'modifier',
		'nicename'       =>'BBCode',
		'description'    =>'Converts BBCode style tags to HTML and makes URLs clickable',
		'authors'         =>'André Rabold, Nathan Codding, The PHPBB group',
		'licence'         =>'GPL',
		'help'            => $help
	);
}


/**
 *
 *
 * @return unknown
 */
function bblog_modifier_bbcode_help() {
	// help copied from phpbb (C) the PHPBB GROUP and released under the GPL
	ob_start();
?>
<h4>Introduction</h4>
<a href="#0">What is BBCode?</a><br />
<br />
<h4>Text Formatting</h4>
<a href="#1">How to create bold, italic and underlined text</a><br />
<a href="#2">How to change the text colour or size</a><br />
<a href="#3">Can I combine formatting tags?</a><br />
<h4>Outputting fixed-width text</h4>
<a href="#5">Outputting code or fixed width data</a><br />
<h4>Generating lists</h4>
<a href="#6">Creating an Un-ordered list</a><br />
<a href="#7">Creating an Ordered list</a><br />
<h4>Creating Links</h4>
<a href="#8">Linking to another site</a><br />
<h4>Showing images in posts</h4>
<a href="#9">Adding an image to a post</a><br />
<br /><br />

<h4>Introduction</h4>
<p><a name="0"></a><b>What is BBCode?</b><br />
BBCode is a special implementation of HTML.
You enable or disable BBCode on a per post basis via the posting form.
BBCode itself is similar in style to HTML, tags are enclosed in square braces [ and ] rather than &lt; and &gt; and it offers greater control
over what and how something is displayed.

<del>Depending on the template you are using you may find adding BBCode
to your posts is made much easier through a clickable interface above the message area on the posting form. </del>

Even with this you may find the following guide useful.<br />
<a href="#Top">Back to top</a></p>

<h4>Text Formatting</h4>
<p><a name="1"></a><b>How to create bold, italic and underlined text</b><br />
BBCode includes tags to allow you to quickly change the basic style of your text.
This is achieved in the following ways: <ul><li>To make a piece of text bold
enclose it in <b>[b][/b]</b>, eg. <br /><br /><b>[b]</b>Hello<b>[/b]</b><br /><br />will become
<b>Hello</b></li><li>For underlining use <b>[u][/u]</b>, for example:<br /><br /><b>[u]</b>
Good Morning<b>[/u]</b><br /><br />becomes <u>Good Morning</u></li><li>
To italicise text use <b>[i][/i]</b>, eg.<br /><br />This is <b>[i]</b>Great!<b>[/i]</b><br /><br />
would give This is <i>Great!</i></li></ul><br />
<a href="#Top">Back to top</a></p>

<p><a name="2"></a><b>How to change the text colour or size</b><br />
To alter the color or size of your text the following tags can be used.
Keep in mind that how the output appears will depend on the viewers browser and system:
 <ul><li>Changing the colour of text is achieved by wrapping it in
 <b>[color=][/color]</b>. You can specify either a recognised colour name
  (eg. red, blue, yellow, etc.) or the hexadecimal
  triplet alternative, eg. #FFFFFF, #000000. For example,
  to create red text you could use:<br /><br /><b>[color=red]</b>Hello!<b>[/color]</b><br />
  <br />or<br /><br /><b>[color=#FF0000]</b>Hello!<b>[/color]</b><br />
  <br />will both output <span style="color:red">Hello!</span></li>
  <li>Changing the text size is achieved in a similar way using
  <b>[size=][/size]</b>. This tag is dependent on the
  template you are using but the recommended format is a
   numerical value representing the text size in pixels,
   starting at 1 (so tiny you will not see it) through to 29
   (very large). For example:<br /><br /><b>[size=9]</b>SMALL<b>[/size]</b><br />
   <br />will generally be <span style="font-size:9px">SMALL</span><br />
   <br />whereas:<br /><br /><b>[size=24]</b>HUGE!<b>[/size]</b><br />
   <br />will be <span style="font-size:24px">HUGE!</span></li></ul><br />
<a href="#Top" >Back to top</a></p>

<p><a name="3"></a><b>Can I combine formatting tags?</b><br />
Yes, of course you can, for example to get someones attention you may write:
<br /><br /><b>[size=18][color=red][b]</b>LOOK AT ME!<b>[/b][/color][/size]</b>
<br /><br />this would output <span style="color:red;font-size:18px">
<b>LOOK AT ME!</b></span><br /><br />We don't
recommend you output lots of text that looks like this though!
Remember it is up to you, the poster to ensure
tags are closed correctly. For example the following is
incorrect:<br /><br /><b>[b][u]</b>This is wrong<b>[/b][/u]</b><br />
<a href="#Top" class="gensmall">Back to top</a></p>

<h4>Outputting fixed-width text</h4>

<p><a name="5"></a><b>Outputting code or fixed width data</b><br />
If you want to output a piece of code or in fact anything that requires a
fixed width, eg. Courier type font you should enclose the text in
<b>[code][/code]</b> tags, eg.<br />
<br /><b>[code]</b>echo "This is some code";<b>[/code]</b>
<br /><br />All formatting used within <b>[code][/code]</b>
tags is retained when you later view it.<br />
<a href="#Top">Back to top</a></p>


<h4>Generating lists</h4>
<p><a name="6"></a><b>Creating an Un-ordered list</b><br />
BBCode supports two types of lists, unordered and ordered. They are essentially the same as their HTML equivalents. An unordered list ouputs each item in your list sequentially one after the other indenting each with a bullet character. To create an unordered list you use <b>[list][/list]</b> and define each item within the list using <b>[*]</b>. For example to list your favorite colours you could use:<br /><br /><b>[list]</b><br /><b>[*]</b>Red<br /><b>[*]</b>Blue<br /><b>[*]</b>Yellow<br /><b>[/list]</b><br /><br />This would generate the following list:<ul><li>Red</li><li>Blue</li><li>Yellow</li></ul><br />
<a href="#Top">Back to top</a></p>

<p><a name="7"></a><b>Creating an Ordered list</b><br />
The second type of list, an ordered list gives you control over what is output before each item. To create an ordered list you use <b>[list=1][/list]</b> to create a numbered list or alternatively <b>[list=a][/list]</b> for an alphabetical list. As with the unordered list items are specified using <b>[*]</b>. For example:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Go to the shops<br /><b>[*]</b>Buy a new computer<br /><b>[*]</b>Swear at computer when it crashes<br /><b>[/list]</b><br /><br />will generate the following:<ol type="1"><li>Go to the shops</li><li>Buy a new computer</li><li>Swear at computer when it crashes</li></ol>Whereas for an alphabetical list you would use:<br /><br /><b>[list=a]</b><br /><b>[*]</b>The first possible answer<br /><b>[*]</b>The second possible answer<br /><b>[*]</b>The third possible answer<br /><b>[/list]</b><br /><br />giving<ol type="a"><li>The first possible answer</li><li>The second possible answer</li><li>The third possible answer</li></ol><br />
<a href="#Top">Back to top</a></p>

<h4>Creating Links</h4>

<p><a name="8"></a><b>Linking to another site</b>
BBCode supports a number of ways of creating URIs, Uniform Resource Indicators better known as URLs.<ul><li>The first of these uses the <b>[url=][/url]</b> tag, whatever you type after the = sign will cause the contents of that tag to act as a URL. For example to link to phpBB.com you could use:<br /><br /><b>[url=http://www.phpbb.com/]</b>Visit phpBB!<b>[/url]</b><br /><br />This would generate the following link, <a href="http://www.phpbb.com/" target="_blank">Visit phpBB!</a> You will notice the link opens in a new window so the user can continue browsing the forums if they wish.</li><li>If you want the URL itself displayed as the link you can do this by simply using:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />This would generate the following link, <a href="http://www.phpbb.com/" target="_blank">http://www.phpbb.com/</a></li><li>Additionally phpBB features something called <i>Magic Links</i>, this will turn any syntatically correct URL into a link without you needing to specify any tags or even the leading http://. For example typing www.phpbb.com into your message will automatically lead to <a href="http://www.phpbb.com/" target="_blank">www.phpbb.com</a> being output when you view the message.</li><li>The same thing applies equally to email addresses, you can either specify an address explicitly for example:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />which will output <a href="emailto:no.one@domain.adr">no.one@domain.adr</a> or you can just type no.one@domain.adr into your message and it will be automatically converted when you view.</li></ul>As with all the BBCode tags you can wrap URLs around any of the other tags such as <b>[img][/img]</b> (see next entry), <b>[b][/b]</b>, etc. As with the formatting tags it is up to you to ensure the correct open and close order is following, for example:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />is <u>not</u> correct which may lead to your post being deleted so take care.<br />
<a href="#Top">Back to top</a></p>

<h4>Showing images in posts</h4>

<p><a name="9"></a><b>Adding an image to a post</b><br />
BBCode incorporates a tag for including images in your posts.
Two very important things to remember when using this tag are;
many users do not appreciate lots of images being shown in posts
 and secondly the image you display must already be available
 on the internet (it cannot exist only on your computer for example,
 unless you run a webserver!). There is currently no way of storing
 images locally with phpBB (all these issues are expected to
 be addressed in the next release of phpBB).
 To display an image you must surround the URL pointing to the
 image with <b>[img][/img]</b> tags. For example:<br /><br />
 <b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b>
 <br /><br />As noted in the URL section above you can wrap an image
 in a <b>[url][/url]</b> tag if you wish, eg.<br /><br />
 <b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b>
 <br /><br />would generate:<br />
 <br /><a href="http://www.phpbb.com/" target="_blank"><img src="http://www.phpbb.com/images/phplogo.gif" border="0" alt="" /></a><br /><br />
<a href="#Top" >Back to top</a></p>

<?php
	$o = ob_get_contents();
	ob_end_clean();
	return $o;
}


/*
 * Smarty plugin
 * ------------------------------------------------------------
 * Type:       modifier
 * Name:       bbcode2html
 * Purpose:    Converts BBCode style tags to HTML
 * Author:     André Rabold
 * Version:    1.3c
 * Remarks:    Notice that this function does not check for
 *             correct syntax. Try not to use it with invalid
 *             BBCode because this could lead to unexpected
 *             results ;-)
 * What's new: - Fixed a bug with <li>...</li> tags (thanks
 *               to Rob Schultz for pointing this out)
 *
 *             Version 1.3b
 *             - Added more support for phpBB2:
 *               [list]...[/list:u] unordered lists
 *               [list]...[/list:o] ordered lists
 *
 *             Version 1.3
 *             - added support for phpBB2 like tag identifier
 *               like [b:b6a0cef7ea]This is bold[/b:b6a0cef7ea]
 *               (thanks to Rob Schultz)
 *             - added support for quotes within the quote tag
 *               so [quote="foo"]bar[/quote] does work now
 *               correctly
 *             - removed str_replace functions
 *
 *             Version 1.2
 *             - now supports CSS classes:
 *                  ng_email      (mailto links)
 *                  ng_url        (www links)
 *                  ng_quote      (quotes)
 *                  ng_quote_body (quotes)
 *                  ng_code       (source code)
 *                  ng_list       (html lists)
 *                  ng_list_item  (list items)
 *             - replaced slow ereg_replace() functions
 *             - Alterned [quote] and [code] to use CSS classes
 *               instead of HTML <blockquote />, <hr />, ... tags.
 *             - Additional BBCode tags [list] and [*] to display
 *               nice HTML lists. Example:
 *                 [list]
 *                   [*]first item
 *                   [*]second item
 *                   [*]third item
 *                 [/list]
 *               The [list] tag can have an additional parameter:
 *                 [list]   unorderer list with bullets
 *                 [list=1] ordered list 1,2,3,4,...
 *                 [list=i] ordered list i,ii,iii,iv,...
 *                 [list=I] ordered list I,II,III,IV,...
 *                 [list=a] ordered list a,b,c,d,...
 *                 [list=A] ordered list A,B,C,D,...
 *             - produces well-formed output
 *             - cleaned up the code
 * ------------------------------------------------------------
 */


/**
 *
 *
 * @param unknown $message
 * @return unknown
 */
function smarty_modifier_bbcode($message) {
	$preg = array(
		// Font and text manipulation ( [color] [size] [font] [align] )
		'/\[color=(.*?)(?::\w+)?\](.*?)\[\/color(?::\w+)?\]/si'   => "<span style=\"color:\\1\">\\2</span>",
		'/\[size=(.*?)(?::\w+)?\](.*?)\[\/size(?::\w+)?\]/si'     => "<span style=\"font-size:\\1\">\\2</span>",
		'/\[font=(.*?)(?::\w+)?\](.*?)\[\/font(?::\w+)?\]/si'     => "<span style=\"font-family:\\1\">\\2</span>",
		'/\[align=(.*?)(?::\w+)?\](.*?)\[\/align(?::\w+)?\]/si'   => "<div style=\"text-align:\\1\">\\2</div>",
		'/\[b(?::\w+)?\](.*?)\[\/b(?::\w+)?\]/si'                 => "<b>\\1</b>",
		'/\[i(?::\w+)?\](.*?)\[\/i(?::\w+)?\]/si'                 => "<i>\\1</i>",
		'/\[u(?::\w+)?\](.*?)\[\/u(?::\w+)?\]/si'                 => "<u>\\1</u>",
		'/\[center(?::\w+)?\](.*?)\[\/center(?::\w+)?\]/si'       => "<div style=\"text-align:center\">\\1</div>",
		'/\[code(?::\w+)?\](.*?)\[\/code(?::\w+)?\]/si'           => "<div class=\"ng_code\">\\1</div>",
		// [email]
		'/\[email(?::\w+)?\](.*?)\[\/email(?::\w+)?\]/si'         => "<a href=\"mailto:\\1\" class=\"ng_email\">\\1</a>",
		'/\[email=(.*?)(?::\w+)?\](.*?)\[\/email(?::\w+)?\]/si'   => "<a href=\"mailto:\\1\" class=\"ng_email\">\\2</a>",
		// [url]
		'/\[url(?::\w+)?\]www\.(.*?)\[\/url(?::\w+)?\]/si'        => "<a href=\"http://www.\\1\"  class=\"ng_url\">\\1</a>",
		'/\[url(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'             => "<a href=\"\\1\"  class=\"ng_url\">\\1</a>",
		'/\[url=(.*?)(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'       => "<a href=\"\\1\"  class=\"ng_url\">\\2</a>",
		// [img]
		'/\[img(?::\w+)?\](.*?)\[\/img(?::\w+)?\]/si'             => "<img src=\"\\1\" border=\"0\" />",
		'/\[img=(.*?)x(.*?)(?::\w+)?\](.*?)\[\/img(?::\w+)?\]/si' => "<img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=\"0\" />",
		// [quote]
		'/\[quote(?::\w+)?\](.*?)\[\/quote(?::\w+)?\]/si'         => "<div class=\"ng_quote\">Quote:<div class=\"ng_quote_body\">\\1</div></div>",
		'/\[quote=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/quote(?::\w+)?\]/si'   => "<div class=\"ng_quote\">Quote \\1:<div class=\"ng_quote_body\">\\2</div></div>",
		// [list]
		'/\[\*(?::\w+)?\]\s*([^\[]*)/si'                          => "<li class=\"ng_list_item\">\\1</li>",
		'/\[list(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/si'           => "<ul class=\"ng_list\">\\1</ul>",
		'/\[list(?::\w+)?\](.*?)\[\/list:u(?::\w+)?\]/s'          => "<ul class=\"ng_list\">\\1</ul>",
		'/\[list=1(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/si'         => "<ol class=\"ng_list\" style=\"list-style-type:decimal;\">\\1</ol>",
		'/\[list=i(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type:lower-roman;\">\\1</ol>",
		'/\[list=I(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type:upper-roman;\">\\1</ol>",
		'/\[list=a(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type:lower-alpha;\">\\1</ol>",
		'/\[list=A(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type:upper-alpha;\">\\1</ol>",
		'/\[list(?::\w+)?\](.*?)\[\/list:o(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type:decimal;\">\\1</ol>",
		// the following lines clean up our output a bit
		'/<ol(.*?)>(?:.*?)<li(.*?)>/si'         => "<ol\\1><li\\2>",
		'/<ul(.*?)>(?:.*?)<li(.*?)>/si'         => "<ul\\1><li\\2>"
	);
	$message = preg_replace(array_keys($preg), array_values($preg), $message);

	// make clickable() :
	/**
	 * Rewritten by Nathan Codding - Feb 6, 2001.
	 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
	 *  to that URL
	 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
	 *  to http://www.xxxx.yyyy[/zzzz]
	 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
	 *  to that email address
	 * - Only matches these 2 patterns either after a space, or at the beginning of a line
	 *
	 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
	 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
	 */
	// pad it with a space so we can match things at the start of the 1st line.
	$ret = ' ' . $message;

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, comma, double quote or <
	$ret = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3">\2://\3</a>', $ret);

	// matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// zzzz is optional.. will contain everything up to the first space, newline,
	// comma, double quote or <.
	$ret = preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3">\2.\3</a>', $ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
	$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);

	// Remove our padding..
	$ret = substr($ret, 1);

	return nl2br($ret);
}


?>
