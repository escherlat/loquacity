<?php
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

function identify_function_getcontent () {
$help = '
<p>the {getcontent} function is used to retrieve the content page linked to the section.<br /><br />
Your index.html template should include() the result of this function, or proceed with normal blog display if the result is FALSE.</p>
';
  return array (
    'name'           =>'getcontent',
    'type'           =>'function',
    'nicename'       =>'GetContent',
    'description'    =>'Returns the content page linked to the section. Return FALSE if none.<br>This',
    'authors'        =>'Elie `LordWo` BLETON <lordwo_REM_OVE_THIS@laposte.net>',
    'licence'        =>'GPL',
    'help'           => $help
  );
}

function smarty_function_getcontent($params, &$bBlog) {

  // Retrieving data
  $bBlog->get_sections();
  $sections = $bBlog->sections;
  foreach ($sections as $object) {
     $new[$object->sectionid] = $object;
  }
  $sections = $new;
  
  $current_section = $bBlog->get_template_vars("sectionid");
    
  // Return  
  $bBlog->assign("content",$sections[$current_section]->content);
}

?>
