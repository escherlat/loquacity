<?php
/**
 * ./bblog/bBlog_plugins/function.getsections.php
 *
 * @package default
 */


// function.getsections.php
//
// Written by Elie `LordWo` BLETON <lordwo_REM_OVE_THIS@laposte.net>
//
/*
** bBlog Weblog http://www.bblog.com/
** Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/**
 *
 *
 * @return unknown
 */
function identify_function_getsections() {
	$help = '
<p>the {getsections} function is used to retrieve all the sections available on the blog, and generate appropriate links.<br /><br />
This function accept a <u>separator</u> parameter, which is a string. Alternatively you can use a <u>before</u> and <u>after</u> parameter. It can be any HTML (or XHTML) code that you want, it will be pasted between each section link. <br /><br />
Another parameter is the <u>limit</u> parameter. If set to "content" it will only list sections that links to a specific content.
If set to "blog", it will only list sections that are bblog-powered. Else, the "both" value will list... both ;) (Default value is "both")
';
	return array (
		'name'           =>'getsections',
		'type'           =>'function',
		'nicename'       =>'GetSections',
		'description'    =>'Retrieves sections and generate links.<br>This doesn\'t use internal bBlog functions, as they crash Apache2/PHP5. Consider this as a workaround.',
		'authors'        =>'Elie `LordWo` BLETON <lordwo_REM_OVE_THIS@laposte.net>',
		'licence'        =>'GPL',
		'help'           => $help
	);
}


/**
 *
 *
 * @param unknown $params
 * @param unknown $bBlog  (reference)
 * @return unknown
 */
function smarty_function_getsections($params, &$bBlog) {

	if (!T_SECTIONS) {
		die("Some problem around the definition of T_SECTION");
	} // Temporary bugtrap to see if i'm right. (lordwo)

	// Default values
	if (!isset($params['limit'])) {
		$params['limit'] = 'both';
	}

	if (isset($params['separator'])) {
		$params['after'] = $params['separator'];
		$params['before'] = '';
	}

	// Retrieving data
	$bBlog->get_sections();

	// Generating links
	$i = 0;
	foreach ($bBlog->sections as $row) {
		if ($params['limit'] == 'both') { $print = TRUE;  }
		elseif (($params['limit'] == 'content') && ($row->content != '')) { $print = TRUE;  }
		elseif (($params['limit'] == 'blog') && ($row->content == '')) { $print = TRUE;  }
		else { $print = FALSE;  }

		if ($print == TRUE) {
			$i++;
			$returned_values .= $params['before']."<a href='?sectionid=".$row->sectionid."'>".$row->nicename."</a>".$params['after'];
		}
	}

	// Return
	return $returned_values;
}


?>
