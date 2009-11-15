<?php
// templates.php - Deals with templating functions
// templates.php - author: Eaden McKee <email@eadz.co.nz>

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

////
// !Custom Smarty template handler for use with database templates
function db_get_template ($tpl_name, &$tpl_source, &$smarty_obj) {
    Global $bBlog;
    $tpl_source = $bBlog->get_var("select template from ".T_TEMPLATES." where templatename='$tpl_id'");
    return true;
}

////
// !Get the timestamp of a template from the database
function db_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
    // do database call here to populate $tpl_timestamp.
    Global $bBlog;
    $tpl_timestamp = $bBlog->get_var("select compiletime from ".T_TEMPLATES." where templatename='$tpl_id'");
    return true;
}

function db_get_secure($tpl_name, &$smarty_obj) {
    // assume all templates are secure
    return true;
}

function db_get_trusted($tpl_name, &$smarty_obj){ }// not used


////
// !Make a footer in the html comments
// Make footer containing the page generation time
// and number of database calls and last modified date
function buildfoot() {
	global $bBlog;
    $mtime = explode(" ",microtime());
	$endtime = $mtime[1] + $mtime[0];
	
	$pagetime = round($endtime - $bBlog->begintime,5);
	$foot = "
<!--//
This page took $pagetime seconds to make
and executed {$bBlog->db->querycount} SQL queries.
Last modified: ".gmdate('D, d M Y H:i:s \G\M\T',$bBlog->lastmodified)."
Powered by bBlog : http://www.bBlog.com/
//-->";
	return $foot;
}

?>
