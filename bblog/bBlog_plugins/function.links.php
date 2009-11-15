<?php

// function.links.php - a smarty function for displaying bBlog links
// Copyright (C) 2003  Mario Delgado <mario@seraphworks.com>
// function.links.php - a plug-in written for bBlog Weblog
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

function identify_function_links () {
$help = '
<p>Links is a Smarty function to be used in templates.
<p>Example usage
<ul><li>To return a list of links, one per line :<br>
   {links}</li>
   <li>To return a list, seperated by a # <br>
     {links sep=#}</li>
   <li>To return a list for the category humor <br>
     {links cat=humor}</li>
   <li>To return a list without the category stores <br>
     {links notcat=stores}</li>
   <li>To limit the number of links returned <br>
     {links num=5}</li>
   <li>The default behavior is to return the list in <br>
    or as set in the admin panel. This <br>
     can be changed with one of the following key words <br>
    <br>
     {links ord=nicename} <br>
     {links ord=category}</li>
   <li>To return a list in descending order <br>
     {links desc=TRUE}</li>
   <li>cat and notcat are mutually exclusive and cannot <br>
     be used together. They can both be used with sep, <br>
     ord, num and desc which can all be used together. <br>
     Category names and ord key words are case sensative.</li>
</ul>';

  return array (
    'name'             =>'links',
    'type'             =>'function',
    'nicename'         =>'Links',
    'description'      =>'Make a list of links',
    'authors'          =>'Mario Delgado <mario@seraphworks.com>',
    'licence'          =>'GPL',
    'help'             => $help
  );


}

function smarty_function_links($params, &$bBlog) {

    $markedlinks = '';

    if(!isset($params['sep'])) {
       $sep = "<br />";
    } else {
       $sep = $params['sep'];
    }
    
    if(isset($params['presep'])) $presep = $params['presep']; // use this for lists

    if(isset($params['desc'])) {
       $asde = "DESC";
    } else {
       $asde = "";
    }

    if(isset($params['ord'])) {
       $order = $params['ord'];
    } else {
       $order = "position";
    }

    if(isset($params['num'])) {
       $max = $params['num'];
    } else {
       $max = "20";
    }

    if(isset($params['cat'])) {
       $cat = $bBlog->get_var("select categoryid from ".T_CATEGORIES." where name='".$params['cat']."'");
    }

    if(isset($params['notcat'])) {
       $notcat = $bBlog->get_var("select categoryid from ".T_CATEGORIES." where name='".$params['notcat']."'");
    }

    if ($cat) {
       $links = $bBlog->get_results("select * from ".T_LINKS." where category='".$cat."' order by ".$order." ".$asde." limit ".$max);    
    } elseif ($notcat) {
       $links = $bBlog->get_results("select * from ".T_LINKS." where category !='".$notcat."' order by ".$order." ".$asde." limit ".$max);    
    } else {
       $links = $bBlog->get_results("select * from ".T_LINKS." order by ".$order." ".$asde." limit ".$max);    
    }


    if(!empty($links)) {
      foreach ($links as $link) {
              $url = $link->url;
              $nicename = $link->nicename;
              $markedlinks .= $presep.'<a href="'.$url.'">'.$nicename.'</a>'.$sep;
      }
    }

    return $markedlinks;
}

?>
