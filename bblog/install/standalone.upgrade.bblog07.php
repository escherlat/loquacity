<?php
/**
 * Jumbled up by Xushi ... =)
 * 
 * Note, the only difference in the database between 0.7.4
 * and 0.7.6 is just the addition of VERSION in {pfx}config.
 * BUT i know some ppl that are going to upgrade from 0.7.4 and
 * below, so i'll keep the 0.7.5 updates here.. with a check to
 * see if they're installed or not.
 *
**/

	// correct config.php path.
	if(file_exists("../config.php")) {
		include '../config.php';
	} else {
		include 'config.php';
	}
?>
<html>
<header>
<link rel="stylesheet" type="text/css" title="Main" href="../style/admin.css" media="screen" />
</header>
<body>
<body><div id="header">
<h1>bBlog</h1>
<h2>Upgrading</h2>
</div>

<div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 80px;">
<?php
	echo '';
	
	// 0.7.5's old updates.. 
	
	//lets see if CHARSET and DIRECTION are already there..
	echo "Checking to see if 0.7.5's patches are installed...<br />";
	if (defined('C_CHARSET') && defined('C_DIRECTION') ){
		//no updates necessary :)
		echo "<p>Your database looks good and does not need 0.7.5's patches. :)</p>";
	}
	else{
		//we should add 2 values to db
		echo "Not found, patching now.<br />";
		$q = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES
		('', 'CHARSET', 'UTF-8'),
		('', 'DIRECTION', 'LTR')";
		
		//just do it
		$bBlog->query($q);
	}//else	



	// 0.7.6's updates (VERSION)
	
	echo "Now installing 0.7.6 upgrades<br /><br />";
	echo "Checking if VERSION already exists...<br />";
	
	$ver = $bBlog->get_var("select value from ".T_CONFIG." where name='VERSION'");
	$newVer = 0.76;
	if(isset($ver)) {
		// update
		echo "Found a previous version. Updating to 0.7.6 now<br /><br />";
		$bBlog->query("UPDATE ".T_CONFIG." SET VALUE='".$newVer."' WHERE `name`='VERSION'");
	} 
	else {
		// otherwise, write a new one 
		echo "No VERSION value found. Creating 0.76 now...<br /><br />";
		$bBlog->query("INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES
			('', 'VERSION', '$newVer')");
	}
		
		
	
	// All Done.
	
   	echo "<h3>Done.</h3>";
	// All Done
	echo "<p>The upgrade finished successfully.<br /><br />
		<h3><u>Security</u></h3>
		<p>Now, you need to do 3 things to finish off
		<ol>
	    	<li>Delete install.php and the install folder</li>
	    	<li>chmod -rw config.php, so that it is not writable by the webserver</li>
	    	<li>When you have done that, you may <a href='../index.php?b=options'>Login to bBLog.</a></li>	
		</ol></p>";
?>
</div>

<div id="footer">
<a href="http://www.bBlog.com" target="_blank">
bBlog 0.7.6</a> &copy; 2005 <a href="mailto:eaden@eadz.co.nz">Eaden McKee</a> &amp; <a href="index.php?b=about" target="_blank">Many Others</a>
</div>

</body>
</html>