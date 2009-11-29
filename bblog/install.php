<?php
/**
 * install.php - bBlog installer
 * install.php - author: Eaden McKee <email@eadz.co.nz>
 *
 * bBlog Weblog http://www.bblog.com/
 * Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package default
 * */


// using sessions becasue it makes things easy
session_start();

// start install all over, forget everything.
if (isset($_GET['reset'])) {
	unset($config);
	unset($step);
	@session_destroy();
	header("Location: install.php");
	exit;
}

$step =& $_SESSION['step'];
$config =& $_SESSION['config'];

if (!isset($_SESSION['step'])) $step=0;

// provide some useful defaults, and prevents undefined indexes.
if (!isset($config['path'])) $config['path'] = dirname(__FILE__).'/';
if (!isset($config['url'])) $config['url'] = 'http://'.$_SERVER['HTTP_HOST'].str_replace('bblog/install.php', '', $_SERVER['REQUEST_URI']);
if (!isset($config['mysql_host'])) $config['mysql_host'] = 'localhost';
if (!isset($config['username'])) $config['username'] = 'admin';
if (!isset($config['table_prefix'])) $config['table_prefix'] = 'bB_';
if (!isset($config['password'])) $config['password'] = "";
if (!isset($config['secondPassword'])) $config['secondPassword'] = "";
if (!isset($config['email'])) $config['email'] = "";
if (!isset($config['fullname'])) $config['fullname'] = "";
if (!isset($config['mysql_username'])) $config['mysql_username'] = "";
if (!isset($config['mysql_password'])) $config['mysql_password'] = "";
if (!isset($config['mysql_database'])) $config['mysql_database'] = "";
if (!isset($config['blogname'])) $config['blogname'] = "";
if (!isset($config['blogdescription'])) $config['blogdescription'] = "";

$config['version'] = "0.7.6";

include './libs/ez_sql.php';
include './install/steps.php';
include './install/header.php';

if ($step > 2) {
	$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
}

if (isset($config['upgrade_from'])) {
	if (file_exists('./install/upgrade.'.$config['upgrade_from'].'.php')) {
		include './install/upgrade.'.$config['upgrade_from'].'.php';
	} else {
		echo "<h3>Error</h3>";
		echo "<p>You have chosen an upgrade option, but the upgrade file (  install/upgrade.".$config['upgrade_from'].".php ) is missing";
		include 'install/footer.php';
		exit;
	}
}
switch ($step) {
case 0:
?>
			<h3>Welcome to the bBlog installer</h3>
			<br />
			<?php echo $message; ?>
			<h4>Introduction</h4>
			<p>Welcome to the bBlog installer. If you get stuck, please see www.bblog.com.<br />One thing to note: this installer uses sessions, so if you have disabled cookies, please re-enable them.</p>
			<h4>Licence Agreement</h4>
			<p>First things first, the licence agreement:</p>
			<textarea rows="8" cols="80" style="border: 2px dotted #333; background: #f0f0f0; font-size:10px;" readonly><?php include 'docs/LICENCE.txt'; ?></textarea>
			<p><input type="checkbox" class="checkbox" name="agree" value="1"> I agree to these terms </p>
			<h4>Install Type</h4>
			<ul class="form">

			<li><input type="radio" class="radio" name="install_type" value="fresh" checked="checked" onClick=" document.forms.install.elements['upgrade_from'].disabled = true;" /> Fresh Install</li>
			<li><input type="radio" class="radio" name="install_type" value="upgrade" onClick=" document.forms.install.elements['upgrade_from'].disabled = false;" /> Upgrade from

			<select name="upgrade_from" id="upgrade_from" disabled>
			<option value="bblog07">bBlog 0.7</option>
			<!--
			<option value="textpattern">Textpattern 0.6</option>
			<option value="b2_061">b2 0.61</option>
			<option value="mt">Movable Type (using mysql)</option>
			<option value="wordpress">Wordpress 0.71</option>
			<option value="nucleus">Nucleus</option>
			-->
			</select>
			</ul>

			<div class='frame'><input type="submit" value="Next &gt;" name="submit" /></div>

			<?php
	break;

	// Case 1: Find out if the user is installing a new version,
	// or upgrading from another one.

case 1:
	if ((isset($config['install_type'])) && ($config['install_type'] == 'upgrade')) {
		echo "<h3>Upgrading</h3>";
		$intro_func = 'upgrade_from_'.$config['upgrade_from'].'_intro';
		if (function_exists($intro_func)) $intro_func();
	}
?>
			<h3>File Permissions</h3>
			<p>bBlog need to be able to write to disk to store it's cache of templates, and if you want to use the blo.gs favorites functionality.</p>
			<p>We will now check the permissions of the 'cache' folder, the 'compiled_templates' folder, and the 'cache/favorites.xml' file. They all need to be writable by the webserver. This will involve chmodding the folders and files with your ftp client ( if you're not using ftp you probally know what do do here ). Permissions should either be 775. If that doesn't work, 777 will. </p>
			<p>Additionally, ./config.php should be writable during the install. At the end of the install when the config file is written to disk, you should change the permissions back so it is not writable by the webserver.</p>
			<?php

	$test = check_writable();
	if ($test) echo "<p>Great, all working. <input type='submit' name='continue' value='Click here to continue' /></p>";
	else echo "<p>Please fix above errors, then <input type='submit' name='continue' value='Click here to try again' /></p>";
	break;

	//Case 2, If user is installing from scratch,
	// provide the DB & Blog settings page
case 2:
?>
			<h3>Database and blog settings</h3>
			<?php
	if (isset($message)) {
		echo $message;
	}
?>
			<p>Please fill in the config settings below</p>

			<table border="0" class='list' cellpadding="4" cellspacing="0">
			<tr>
  <td colspan="3"><h4>General Config</h4></td>
</tr>
<tr bgcolor="#ddd">
  <td width="33%">Blog Name</td>
  <td width="200"><input type="text" name="blogname" value="<?php echo $config['blogname']; ?>" /></td>
  <td  width="33%" class='si'>A short name for your blog, e.g. "My Blog"</td>
</tr>
<tr bgcolor="#eeeeee">
  <td width="33%" bgcolor="#eeeeee">Blog Description</td>
  <td width="200"><input type="text" name="blogdescription" value="<?php echo $config['blogdescription']; ?>"/>
  </td>
  <td  width="33%" class='si'>A short descriptive subtitle e.g. "A blog about fish"</td>
</tr>
<tr bgcolor="#ddd">
  <td width="33%" bgcolor="#eee">Full Name
    </td>
  <td><input type="text" name="fullname" value="<?php echo $config['fullname']; ?>"/></td>
  <td class='si'>The owners full name </td>
</tr>
<tr bgcolor="#ddd">
  <td width="33%" bgcolor="#eeeeee">Username</td>
  <td width="200"><input type="text" name="username" value="<?php echo $config['username']; ?>"/>
  </td>
  <td width="33%" class='si'>The username you want to use to log in to bBlog</td>
</tr>
<tr bgcolor="#eeeeee">
  <td width="33%" bgcolor="#eeeeee">Password</td>
  <td width="200"><input type="password" name="password" value="<?php echo $config['password']; ?>"/></td>
  <td width="33%" class='si'>The password you want to use to log in to bBlog</td>
</tr>
<tr bgcolor="#ddd">
  <td width="33%" bgcolor="#eee">Re-enter Password</td>
  <td width="200"><input type="password" name="secondPassword" value="<?php echo $config['secondPassword']; ?>"/></td>
  <td width="33%" class='si'>Please re-enter the password.</td>
</tr>
<tr bgcolor="#eee">
  <td width="33%" bgcolor="#eeeeee">Email Address</td>
  <td width="200"><input type="text" name="email" value="<?php echo $config['email']; ?>"/></td>
  <td width="33%" class='si'>Where to send notifications of comments</td>
</tr>
<tr>
  <td colspan="3"><h4>MySQL Settings</h4></td>
</tr>
</td>
<tr bgcolor="#ddd">
  <td width="33%">MySQL Username</td>
  <td width="200"><input type="text" name="mysql_username" value="<?php echo $config['mysql_username']; ?>"/></td>
  <td width="33%" class='si'>Your MySQL username</td>
</tr>
<tr bgcolor="#eeeeee">
  <td width="33%">MySQL Password</td>
  <td width="200"><input type="password" name="mysql_password" value="<?php echo $config['mysql_password']; ?>" /></td>
  <td width="33%" class='si'>Your MySQL password</td>
</tr>
<tr bgcolor="#ddd">
  <td width="33%">MySQL Database Name</td>
  <td width="200"><input type="text" name="mysql_database" value="<?php echo $config['mysql_database']; ?>"/></td>
  <td width="33%" class='si'>Your MySQL database name<br>( usually the same as your username )</td>
</tr>
<tr bgcolor="#eeeeee">
  <td width="33%">Mysql Host</td>
  <td width="200"><input type="text" name="mysql_host" value="<?php echo $config['mysql_host']; ?>" /></td>
  <td width="33%" class='si'>The MySQL host name is usually 'localhost'</td>
</tr>
<tr bgcolor="#ddd">
  <td width="33%">Table Prefix</td>
  <td width="200"><input type="text" name="table_prefix" value="<?php echo $config['table_prefix']; ?>" /></td>
  <td width="33%" class='si'>Prefix of tables ( usually bB_ )</td>
</tr>
<tr>
  <td colspan="3"><h4>Server Settings</h4></td>
</tr>
<tr bgcolor="#ddd">
  <td width="33%">Url to your blog</td>
  <td width="200"><input type="text" name="url" value="<?php echo $config['url']; ?>" /></td>
  <td width="33%" class='si'>The full URL to your blog</td>
</tr>
<tr bgcolor="#eeeeee">
  <td width="33%">Path to bBlog</td>
  <td width="200"><input type="text" name="path" value="<?php echo $config['path']; ?>" />
  </td>
  <td width="33%" class='si'>The full UNIX path to the bblog directory</td>
</tr>
</table>
<p><input type="submit" name="submit" value="Next &gt;" />
<?php
	break;



	// Case 3: However, if user is upgrading from a
	// previous install, then run the upgrade script.

case 3:

	// add section here to extract details from the DB,
	// since they already exist and we don't need to
	// ask the user about them allover again.

	//$config['blogname'] = ...
	// on hold.. its pointless to have this here now...

	// Execute the upgrading function
	$func = 'upgrade_from_'.$config['upgrade_from'].'_pre';
	if (function_exists($func)) {
		$func();
	} else {
		// this is really an error
		$step=5;
		echo "<p>Nothing to see here, <input type='submit' name='submit' value='Next &gt;' /></p>";
	}
	// upgrade.
	// if tables need to be created, such as MT or wordpress converstion, after this step go to step 4.
	// otherwise, in the case of a bBlog upgrade where tables _dont_ need to be created, go to step 5.
	break;




	// Case 4: create the new tables, based on a fresh
	// install of bblog.

case 4:
	// do sql.

	$q = array();
	/* Creating Tables */
	$pfx = $config['table_prefix'];
	$q[]="CREATE TABLE `{$pfx}comments` (
  `commentid` int(10) unsigned NOT NULL auto_increment,
  `parentid` int(10) unsigned NOT NULL default '0',
  `postid` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `type` enum('comment','trackback') NOT NULL default 'comment',
  `posttime` int(11) default NULL,
  `postername` varchar(100) NOT NULL default '',
  `posteremail` varchar(100) NOT NULL default '',
  `posterwebsite` varchar(255) NOT NULL default '',
  `posternotify` tinyint(1) NOT NULL default '0',
  `pubemail` tinyint(1) NOT NULL default '0',
  `pubwebsite` tinyint(1) NOT NULL default '0',
  `ip` varchar(16) NOT NULL default '',
  `commenttext` text NOT NULL,
  `deleted` enum('true','false') NOT NULL default 'false',
  `onhold` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`commentid`),
  FULLTEXT KEY `commenttext` (`commenttext`)
) TYPE=MyISAM;";

	$q[]="CREATE TABLE `{$pfx}config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";


	$q[]="CREATE TABLE `{$pfx}plugins` (
`id` int(11) NOT NULL auto_increment,
`type` varchar(50) NOT NULL default 'admin',
`name` varchar(60) NOT NULL default '',
`ordervalue` decimal(3,2) NOT NULL default '50.00',
`nicename` varchar(127) NOT NULL default '',
`description` text NOT NULL,
`template` varchar(100) NOT NULL default '',
`help` mediumtext NOT NULL,
`authors` varchar(255) NOT NULL default '',
`licence` varchar(50) NOT NULL default '',
PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

	$q[]="CREATE TABLE `{$pfx}posts` (
  `postid` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `body` mediumtext NOT NULL,
  `posttime` int(11) NOT NULL default '0',
  `modifytime` int(11) NOT NULL default '0',
  `status` enum('live','draft') NOT NULL default 'live',
  `modifier` varchar(30) NOT NULL default '',
  `sections` varchar(255) NOT NULL default '',
  `ownerid` int(10) NOT NULL default '0',
  `hidefromhome` tinyint(1) NOT NULL default '0',
  `allowcomments` enum('allow','timed','disallow') NOT NULL default 'allow',
  `autodisabledate` int(11) NOT NULL default '0',
  `commentcount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`postid`),
  KEY ownerid (ownerid)
) TYPE=MyISAM;";


	$q[] = "CREATE TABLE `{$pfx}sections` (
  `sectionid` int(11) NOT NULL auto_increment,
  `nicename` varchar(255) NOT NULL default '',
  `name` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`sectionid`)
) TYPE=MyISAM ;";



	$q[] = "CREATE TABLE `{$pfx}referers` (
 `visitID` int(11) NOT NULL auto_increment,
 `visitTime` timestamp(14) NOT NULL,
 `visitURL` char(250) default NULL,
 `referingURL` char(250) default NULL,
 `baseDomain` char(250) default NULL,
 PRIMARY KEY  (`visitID`)
) TYPE=MyISAM;";

	$q[] = "CREATE TABLE {$pfx}authors (
  id int(10) NOT NULL auto_increment,
  nickname varchar(20) NOT NULL default '',
  email varchar(100) NOT NULL default '',
  password varchar(20) NOT NULL default '',
  fullname varchar(50) NOT NULL default '',
  url varchar(50) NOT NULL default '',
  icq int(10) unsigned NOT NULL default '0',
  profession varchar(30) NOT NULL default '',
  likes text NOT NULL,
  dislikes text NOT NULL,
  location varchar(25) NOT NULL default '',
  aboutme text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";


	$q[] = "
CREATE TABLE `{$pfx}rss` (
  `id` int(11) NOT NULL auto_increment,
  `url` text NOT NULL,
  `input_charset` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ";

	$q[] = "
CREATE TABLE {$pfx}links (
  linkid int(11) NOT NULL auto_increment,
  nicename varchar(255) NOT NULL,
  url varchar(255) NOT NULL default '',
  category int(11) NOT NULL,
  position int(8) NOT NULL default '10',
  PRIMARY KEY  (linkid)) TYPE=MyISAM";

	$q[] = "
CREATE TABLE {$pfx}categories (categoryid int(11) NOT NULL auto_increment
, name varchar(60) NOT NULL, PRIMARY KEY  (categoryid)) TYPE=MyISAM
";

	/* inserting data */
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (9, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (8, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (7, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (6, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (5, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (4, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (3, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (2, '', '')";
	$q[]= "INSERT INTO `{$pfx}rss` VALUES (1, 'http://www.bblog.com/rdf.php', 'I88592')";

	$q[]="INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES
('', 'EMAIL', '".$config['email']."'),
('', 'BLOGNAME', '".$config['blogname']."'),
('', 'TEMPLATE', 'lines'),
('', 'DB_TEMPLATES', 'false'),
('', 'DEFAULT_MODIFIER', 'simple'),
('', 'CHARSET', 'UTF-8'),
('', 'VERSION', '0.76'),
('', 'DIRECTION', 'LTR'),
('', 'DEFAULT_STATUS', 'live'),
('', 'PING','bblog.com/ping.php'),
('', 'COMMENT_TIME_LIMIT','1'),
('', 'NOTIFY','false'),
('', 'BLOG_DESCRIPTION', '".$config['blogdescription']."'),
('', 'COMMENT_MODERATION','urlonly'),
('', 'META_DESCRIPTION','Some words about this blog'),
('', 'META_KEYWORDS','work,life,play,web design'),
('', 'LAST_MODIFIED', UNIX_TIMESTAMP());";
	$q[] = "INSERT INTO {$pfx}categories VALUES (1,'Navigation');";
	$q[] = "INSERT INTO {$pfx}categories VALUES (2,'Blogs I read');";
	$url = $config['url'];
	$q[]= "INSERT INTO {$pfx}links VALUES (1,'Home','{$url}',1,20);";
	$q[]= "INSERT INTO {$pfx}links VALUES (2,'Archives','{$url}archives.php',1,30);";
	$q[]= "INSERT INTO {$pfx}links VALUES (3,'RSS 2.0 Feed','{$url}rss.php?ver=2',1,40);";
	$q[]="INSERT INTO {$pfx}links VALUES (4,'bBlog Dev','http://dev2.bblog.com/',2,50);";
	$q[]="INSERT INTO {$pfx}links VALUES (5, 'Eadz::Blog','http://www.eadz.co.nz/blog/',2,60);";



	$q[]="INSERT INTO `{$pfx}authors` (`nickname`,`password`,`email`,`fullname`) VALUES
('".$config['username']."','".$config['password']."','".$config['email']."','".$config['fullname']."');";


	$q[] = "INSERT INTO `{$pfx}posts` (`postid`, `title`, `body`, `posttime`, `modifytime`, `status`, `modifier`, `sections`, `commentcount`,`ownerid`) VALUES (1, 'First Post', '[b]This is the first post of bBlog.[/b]\r\n\r\nYou may delete this post in the admin section. Make sure you have deleted the install file and changed the admin password. \r\n\r\nBe sure to visit the [url=http://www.bblog.com/forum.php]bBlog forum[/url] if you have any questions, comments, bug reports etc. \r\n\r\nHappy bBlogging!', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'live', 'bbcode', '', 0, 1);";

	$q[] = "INSERT INTO `{$pfx}sections` (`sectionid`, `nicename`, `name`) VALUES (1, 'News', 'news'),
(2, 'Work', 'work'),
(3, 'Play', 'play');";



	$i=0;
	echo "<h3>Creating tables</h3><p>";
	$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
	foreach ($q as $q2do) {
		$i++;
		echo $i." ";
		//echo "<pre>$q2do</pre>";
		$db->query($q2do);
	}
	echo ' done.<input type="submit" name="submit" value="Next &gt;" /></p>';
	$step = 5;

	break;



	// Case 5: Scan and update all the plugins

case 5:
	echo "<h3>Loading Plugins</h3>";
	$newplugincount = 0;
	$newpluginnames = array();
	$plugin_files=array();
	$dir="./bBlog_plugins";
	$dh = opendir( $dir ) or die("couldn't open directory");
	while ( ! ( ( $file = readdir( $dh ) ) === false ) ) {
		if (substr($file, -3) == 'php') $plugin_files[]=$file;
	}
	closedir( $dh );
	echo "<table border='0' class='list'>";
	foreach ($plugin_files as $plugin_file) {
		$far = explode('.', $plugin_file);
		$type = $far[0];
		$name = $far[1];
		if ($type != 'builtin') {
			include_once './bBlog_plugins/'.$plugin_file;
			$func = 'identify_'.$type.'_'.$name;
			if (function_exists($func)) {
				$newplugin = $func();

				if (!isset($newplugin['template'])) { $newplugin['template'] = ""; }

				$q = "insert into ".$config['table_prefix']."plugins set
					`type`='".$newplugin['type']."',
					`name`='".$newplugin['name']."',
					nicename='".$newplugin['nicename']."',
					description='".addslashes($newplugin['description'])."',
					template='".$newplugin['template']."',
					help='".addslashes($newplugin['help'])."',
					authors='".addslashes($newplugin['authors'])."',
					licence='".$newplugin['licence']."'";
				$db->query($q);
				echo '<tr><td>'.$newplugin['nicename'].'</td><td>..........Loaded</td></tr>';

			} // end if function exists
		} // end if
	} // end foreach
	echo "</table>";
	echo '<p>Done. <input type="submit" name="submit" value="Next &gt;" />';
	$func = 'upgrade_from_'.$config['upgrade_from'].'_post';
	if ($config['install_type'] == 'upgrade' && function_exists($func)) $step = 6;
	else $step = 7;
	break;



	// Case 6: post-install upgrade stuff,
	// such as getting config to write, or giving hints.

case 6:
	$func = 'upgrade_from_'.$config['upgrade_from'].'_post';
	$func();
	break;



	// Case 7 : Finally, create and write the config.php file.

case 7:
	// Write config!
	echo "<h3>Writing config.php file</h3>";

	if (!isset($config['extra_config'])) $config['extra_config'] = '';

	$config_file = "<?php
/*

   '||     '||'''|,  '||`
    ||      ||   ||   ||
    ||''|,  ||;;;;    ||  .|''|, .|''|,
    ||  ||  ||   ||   ||  ||  || ||  ||
   .||..|' .||...|'  .||. `|..|' `|..||
                                     ||
          .7                      `..|'

** bBlog Weblog Software http://www.bblog.com/
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

/* login details are stored in the database */



/* MySQL details */

// MySQL database username
define('DB_USERNAME','".$config['mysql_username']."');

// MySQL database password
define('DB_PASSWORD','".$config['mysql_password']."');

// MySQL database name
define('DB_DATABASE','".$config['mysql_database']."');

// MySQL hostname
define('DB_HOST','".$config['mysql_host']."');

// prefix for table names if you're installing
// more than one copy of bblog on the same database
// don't change this unless you know what you're doing.
define('TBL_PREFIX','".$config['table_prefix']."');

/* file and paths */

// Full path of the directory where you've installed bBlog
// ( i.e. the bblog folder )
define('BBLOGROOT','".$config['path']."');

/* URL config */

// URL to your blog ( one folder below the 'bBlog' folder )
// e.g, if your bBlog folder is at www.example.com/blog/bblog, your
// blog will be at www.example.com/blog/
define('BLOGURL','".$config['url']."');

// URL to the bblog folder via the web.
// Becasue if you're using clean urls and news.php as your BLOGURL,
// we can't automatically append bblog to it.
define('BBLOGURL',BLOGURL.'bblog/');

// Clean or messy urls ? ( READ README-URLS.txt ! )
//define('CLEANURLS',TRUE);
//define('URL_POST','".$config['url']."item/%postid%/');
//define('URL_SECTION','".$config['url']."section/%sectionname%/');


".$config['extra_config']."

// ---- end of config ----
// leave this line alone
include BBLOGROOT.'inc/init.php';
?>";
	$fp = fopen('./config.php', 'w');
	fwrite($fp, $config_file);
	fclose($fp);
	echo '<p>Config file written. <input type="submit" name="continue" value="Next &gt;" /></p>';
	$step = 8;
	break;



	// Case 8: Print out a few good messages to the user :)

case 8:
	echo "<h3>All Done!</h3>";
	echo "<p>Install finished, almost....
		<h3>Security</h3>
		<p>Now, you need to do 3 things to finish off
<ol>
		    <li>Delete install.php</li>
		    <li>delete the install folder</li>
		    <li>Chmod the config.php so that it is not writable by the webserver</li>
		    <li>When you have done that, you may <a href='index.php?b=options'>Login to bBLog. Be sure to visit the Options page to set your email address and other options.</a></li>
</ol></p>";
	break;

}

include 'install/footer.php';


/**
 *
 *
 * @return unknown
 */
function check_writable() {
	$ok = TRUE;
	if (is_writable("./cache")) {
		echo "./cache is writeable<br />";
	} else {
		echo "<span style='color:red;'>./cache is NOT writable</span><br />";
		$ok = FALSE;
	}

	if (is_writable("./compiled_templates")) {
		echo "./compiled_templates is writeable<br />";
	} else {
		echo "<span style='color:red;'>./compiled_templates is NOT writable</span><br />";
		$ok = FALSE;
	}

	if (is_writable("./cache/favorites.xml")) {
		echo "./cache/favorites.xml is writeable<br />";
	} else {
		echo "<span style='color:red;'>./cache/favorites.xml is NOT writable</span><br />";
		$ok = FALSE;
	}

	if (is_writable("./config.php")) {
		echo "./config.php is writeable<br />";
	} else {
		echo "<span style='color:red;'>./config.php is NOT writable</span><br />";
		$ok = FALSE;
	}

	return $ok;

}


?>
