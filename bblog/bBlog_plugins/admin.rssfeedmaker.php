<?php
// admin.rssfeedmaker.php - easily make custom rss feeds
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

function identify_admin_rssfeedmaker () 
{
  return array (
    'name'           =>'rssfeedmaker',
    'type'             =>'admin',
    'nicename'     =>'RSS Linker',
    'description'   =>'Create custom RSS feeds',
    'template' 	=> 'rssfeedmaker.html',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'help'            => ''
  );
}

function admin_plugin_rssfeedmaker_run(&$bBlog) 
{
	if ((isset($_POST['sub'])) && ($_POST['sub'] == 'y')) 
	{
		$url = BLOGURL.'rss.php?';


		if($_POST['version'] == 2) $url .= 'ver=2';
		elseif($_POST['version'] == 'atom03') $url .= 'ver=atom03';
		else $url .= 'ver=0.92';

		if(is_numeric($_POST['num'])) $url .= '&amp;num='.$_POST['num'];

		if($_POST['sectionid']>0) $url .= '&amp;sectionid='.$_POST['sectionid'];

		if(is_numeric($_POST['year'])) $url .= '&amp;year='.$_POST['year'];
		if(is_numeric($_POST['month'])) $url .= '&amp;year='.$_POST['day'];
		if(is_numeric($_POST['day'])) $url .= '&amp;year='.$_POST['day'];

		$bBlog->assign('results',TRUE);
		$bBlog->assign('feedurl',$url);
	}
	
	$sections = $bBlog->get_sections();
	$sectionlist = '';
	
	foreach ($sections as $section) 
	{
		$sectionlist .= "<option value='{$section->sectionid}'>{$section->nicename}</option>";
	}
	
	$bBlog->assign('sectionlist',$sectionlist);
}
?>
