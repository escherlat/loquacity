<?php
// function.header.php 
//
// Written by Reverend Jim <jim@revjim.net>
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
function identify_function_header () {
$help = '
<p>the {header} function is used to set arbitrary HTTP headers. It takes the following parameters:<br />
<br />
header: the header to send<br />';

  return array (
    'name'           =>'header',
    'type'             =>'function',
    'nicename'     =>'Header',
    'description'   =>'Sets an arbitrary HTTP header',
    'authors'        =>'Reverend Jim <jim@revjim.net>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}
function smarty_function_header($params, &$bBlog) {

	header($params['header']);

	return '';

}

