<?php

/* 
    function.nextprev.php - nextprev plugin
*/
function identify_function_nextprev () {

    $help = '
	<p>This plugin displays your indexes and archive pages in a list of pages.</p>
	<p>To call the plugin, simply do a <code>{nextprev}</code>.  Just
	like most of bBlog, this defaults to pages of 20 entries.  If you
	want to tweak this, use the <var>num</var> parameter.  For example,
	if you want pages of 5 entries, do <code>{nextprev num=5}</code>.</p>
	<p>Within your pages, you will need to set the skip parameter to
	<var>$current_offset</var>, such as in
	<code>{getrecentposts num=5 skip=$current_offset assign=posts home=true}</code>
	(For 0.7.3, you need a patch for this and <code>getarchiveposts</code>; see the distribution).</p>
	<p>The link is stored within the variable <var>{$gonext}</var> and
	<var>{$goprev}</var>; If either parameter is not valid, the link
	will be an empty string, hence you will probably want to use this
	in an <code>if</code>, such as:</p>
	<p><code>
	{if $goprev ne ""}<br />
	&lt;a href="{$goprev}"&gt;Previous&lt;/a&gt;<br />
	{/if}<br />
	{if $gonext ne ""}<br />
	&lt;a href="{$gonext}"&gt;Next&lt/a&gt;<br />
	{/if}<br />
	</code></p>
	<p>If you want to use the <code>link</code> tag in your header,
	simply call the <code>{nextprev}</code> before including your
	header, and add the following to your header:</p>
	<p><code>
	&lt;head&gt;<br />
	...<br />
	{if $goprev ne ""}<br />
	&lt;link rel="prev" type="text/html" href="{$goprev}" /&gt;<br />
	{/if}<br />
	{if $gonext ne ""}<br />
	&lt;link rel="next" type="text/html" href="{$gonext}" /&gt;<br />
	{/if}<br />
	</p>
	<p>If you want to allow users to select pages, use the
	<code>{$goprevpages}</code> and <code>{$gonextpages}</code> in
	a <code>foreach</code> loop, such as:</p>
	<p> <code>
	{foreach from=$goprevpages item=page}<br />
   	&nbsp;&lt;a href="{$page.url}"&gt;{$page.page}&lt;/a&gt;&amp;nbsp;<br />
	{/foreach} <br />
	{$current_page}<br />
	{foreach from=$gonextpages item=page}<br />
   	&nbsp;&lt;a href="{$page.url}"&gt;{$page.page}&lt;/a&gt;&amp;nbsp;<br />
	{/foreach} <br />
	</code></p>
	<p>By default, this will show all available pages.  You can set the
	<code>max_pages</code> parameter to limit the number of pages.
	It will show half the number of pages before the current page and
	half of those pages afterwards.</p>
	<h3>Parameters</h3>
	<p><table border="1">
	<tr><th>Parameter</th><th>Description</th></tr>
	<tr><td><var>num</var></td><td>Number of entries per page; The
	default is 20.</td></tr>
	<tr><td><var>max_pages</var></td><td>This is the maximum number
	of pages to list in the <var>goprevpages</var> and
	<var>gonextpages</var> pages.</td></tr>
	<tr><td><var>query</var></td><td>This parameter allows you to
	assign more query parameters if desired.</td></tr>
	<tr><td><var>link</var></td><td>To add some parameters to the
	generated link, use this variable.  This form is
	<code>&amp;<var>variable</var>=<var>value</var></code>.</td></tr>
	<tr><td><var>posts</var></td><td>Instead of this plugin querying
	the database to count entries, it will count these entries
	instead.  This invalidates the query parameter.</td></tr>
	<tr><td><var>adjust</var></td><td>When <var>posts</var> is used,
	this parameter will adjust the array according to the parameters.  If it
	is not set, this will need to be adjusted externally, and I am not sure
	how.</td></tr>
	</table></p>
	<h3>Variables Assigned</h3>
	<p><table border="1">
	<tr><th>Variable</th><th>Description</th></tr>
	<tr><td><code>current_offset</code></td><td>This is the current
	index of the entry at the top of the page.  In other words, this
	is <code><var>current_page</var> * <var>entries per page</var>
	</code></td></tr>
	<tr><td><code>current_page</code></td><td>This is the index of
	the current page (starting at 1).</td></tr>
	<tr><td><code>gofirstpage</code><br /><code>golastpage</code></td>
	<td>When using the parameter <var>max_pages</var>, this is the
	index of the first/last page in the
	<var>goprevpages</var>/<var>gonextpages</var>.</td></tr>
	<tr><td><code>gonum_pages</code></td><td>This is the total
	number of pages that could be displayed.</td></tr>
	<tr><td><code>goprevpages</code><br /><code>gonextpages</code></td>
	<td>This is an array of pages before and after, respectively, the
	<var>current_page</var>.  The array contains the <var>page</var>
	(index of) and <var>url</var> (URL to display the page).</td></tr>
	<tr><td><code>goprev</code><br /><code>gonext</code></td><td>
	This is the link to the previous and next page, respectively.</td></tr>
	</table></p>
	<p>This is version 0.4.1.  For the latest version, see:
	<a href="http://www.eyt.ca/Software">http://www.eyt.ca/Software</a></p>';

     return array (
	'name'		=> 'nextprev',
	'type'		=> 'function',
	'nicename'	=> 'NextPrev',
	'description'	=> 'Adds a previous/next button on your indexes',
	'authors'	=> 'Eric Y. Theriault',
	'licence'	=> 'GPL',
	'help'		=> $help
    );
    
}

function smarty_function_nextprev($params, &$bBlog) {
    // Initialize default values...
    $skip = 0;
    $num = 20;
    $max_pages = 0;
    $pages_before = 0;

    // Set the num parameter
    if ( is_numeric( $params[ 'num' ] ) ) {
        $num = $params[ 'num' ];
    }

    // Set the max_pages parameter
    if ( is_numeric( $params[ 'max_pages' ] ) ) {
        $max_pages = $params[ 'max_pages' ];
        $pages_before = (int)( $max_pages / 2 );
    }

    // Acquire the page skip count; if set, snag it.
    $newSkip = $_GET[ "pageskip" ];
    if ( $newSkip ) {
        $skip = $newSkip;
    }
    $sectionid = $_GET[ "sectionid" ];
    $QuerySection = '';
    $ExtraParams = '';
    if ( $sectionid ) {
        $QuerySection .= " AND sections like '%:$sectionid:%'";
        $ExtraParams .= "&sectionid=$sectionid";
    }
    else {
       // This is for the case of Clean URLS
       $sectionid = $bBlog->get_template_vars("sectionid");
       if ( $sectionid ) {
          $QuerySection .= " AND sections like '%:$sectionid:%'";
       }
    }
    $query_params = $params[ 'query' ];
    if ( $query_params ) {
       $QuerySection .= " AND $query_params";
    }
    $link_params = $params[ 'link' ];
    if ( $link_params ) {
       $ExtraParams .= $link_params;
    }
    $posts = $params[ 'posts' ];

    // Calculate the new offset
    $offset = $skip * $num;
    $nextoffset = $offset + $num;
    $bBlog->assign( "current_offset", $offset );

    // Get number of entries...
    if ( $posts ) {
       $entryCount = count( $posts );
       if ( $params[ 'adjust' ] ) {
          $bBlog->assign( 'posts', array_slice( $posts, $offset, $num ) );
       }
    }
    else {
       // invariant: Need to query the database and count
       $countArray = $bBlog->get_results( "select count(*) as nElements from ".T_POSTS." where status = 'live' $QuerySection;" );
       if ( $bBlog->num_rows <= 0 ) {
          $entryCount = 0;
       } else {
          foreach ( $countArray as $cnt ) {
              $entryCount = $cnt->nElements;
          }
       }
    }

    // Create the previous pages...
    $i = 0;
    $current_page = $skip;
    if ( $max_pages != 0 ) {
       $i = $current_page - $pages_before;
       if ( $i < 0 ) {
          $i = 0;
       }
    }
    $bBlog->assign( "current_page", $current_page + 1 );
    $bBlog->assign( "gofirstpage", $i + 1 );
    while ( $i < $current_page ) {
       $cp = $i + 1;
       $prevpages[] = array( 'page' => $cp, 'url' => $_SERVER["PHP_SELF"] . "?pageskip=$i$ExtraParams" );
       ++ $i;
    }
    $bBlog->assign( "goprevpages", $prevpages );

    // Create the next pages
    $i = $current_page + 1;
    $numberOfPages = (int) ( $entryCount / $num );
    $pages = $numberOfPages;
    if ( ($pages * $num) < $entryCount ) {
       $pages ++;
       $numberOfPages ++;
    }
    if ( $max_pages != 0 ) {
       $pages = $i + $pages_before;
       if ( $pages > $numberOfPages ) {
          $pages = $numberOfPages;
       }
    }
    $bBlog->assign( "golastpage", $pages );
    $bBlog->assign( "gonum_pages", $numberOfPages );
    while ( $i < $pages ) {
       $nextpages[] = array( 'page' => $i+1, 'url' => $_SERVER["PHP_SELF"] . "?pageskip=$i$ExtraParams" );
       ++ $i;
    }
    $bBlog->assign( "gonextpages", $nextpages );

    // Get the previous count...
    if ( $offset == 0 ) {
       $previous = 0;
       $bBlog->assign( "goprev", "" );
    } else {
       $previous = $skip - 1;
       $bBlog->assign( "goprev", $_SERVER["PHP_SELF"] . "?pageskip=$previous$ExtraParams" );
    }

    // Get the next count...
    if ( $nextoffset < $entryCount ) {
       $next = $skip + 1;
       $bBlog->assign( "gonext", $_SERVER["PHP_SELF"] . "?pageskip=$next$ExtraParams" );
    } else {
       $next = 0;
       $bBlog->assign( "gonext", "" );
    }
}
?>

