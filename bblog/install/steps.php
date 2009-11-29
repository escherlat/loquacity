<?php
/**
 * ./bblog/install/steps.php
 *
 * @package default
 */


// can you get to level 6?
switch ($step) {
case 0:
	// tests to get to next level :
	// 1 - agree to terms
	if (!((isset($_POST['agree']) && ($_POST['agree'] == '1')))) {
		if ((isset($_POST['submit'])) && ($_POST['submit'])) {
			$message = "<p style='color:red;'>You must agree to the terms</p>";
			break;
		}
	}

	// Check and see if user wants to install, or upgrade.
	if (isset($_POST['install_type'])) {
		$config['install_type'] = $_POST['install_type'];

		if ($_POST['install_type'] == 'upgrade') {
			$config['upgrade_from'] = $_POST['upgrade_from'];
		}
	} else break;

	$step=1;
	break;

case 1:
	// tests to get to next level
	// 1 - things need to be writable
	ob_start(); // we don't want any errors
	if (check_writable()) $step = 2;
	ob_end_clean();
	break;
case 2:
	// tests :
	// 1 - mysql connects
	// 2 - everything is set
	$allfilled=TRUE;

	if (isset($_POST['blogname'])) {
		$config['blogname'] = $_POST['blogname'];
	} else {
		$missing_fields = "'Blog name' ";
	}


	if (isset($_POST['blogdescription'])) {
		$config['blogdescription'] = $_POST['blogdescription'];
	} else {
		$missing_fields .= "'Blog description' ";
	}

	if (isset($_POST['username'])) {
		$config['username'] = $_POST['username'];
	} else {
		$missing_fields .= "'Username' ";
	}

	if (isset($_POST['password'])) {
		$config['password'] = $_POST['password'];
	} else {
		$missing_fields .=  "'Password' ";
	}

	//second password field
	if (isset($_POST['secondPassword'])) {
		$config['secondPassword'] = $_POST['secondPassword'];
	}

	//Test first to see if the passwords both match
	if ($config['password'] != $config['secondPassword']) {
		$missing_fields .= "Passwords mismatched.";
	}

	if (isset($_POST['email'])) {
		$config['email'] = $_POST['email'];
	} else {
		$missing_fields .= "'E-Mail' ";
	}

	if (isset($_POST['fullname'])) {
		$config['fullname'] = $_POST['fullname'];
	} else {
		$missing_fields .="'Full Name' ";
	}

	if (isset($_POST['mysql_username'])) {
		$config['mysql_username'] = $_POST['mysql_username'];
	} else {
		$missing_fields .= "'MySQL Username' ";
	}

	if (isset($_POST['mysql_password'])) {
		$config['mysql_password'] = $_POST['mysql_password'];
	} else {
		$missing_fields .= "'MySQL Password' ";
	}

	if (isset($_POST['mysql_database'])) {
		$config['mysql_database'] = $_POST['mysql_database'];
	} else {
		$missing_fields .= "'MySQL Database' ";
	}

	if (isset($_POST['mysql_host'])) {
		$config['mysql_host'] = $_POST['mysql_host'];
	} else {
		$missing_fields .= "'MySQL Host' ";
	}

	if (isset($_POST['table_prefix'])) {
		$config['table_prefix'] = $_POST['table_prefix'];
	} else {
		$missing_fields .= "'MySQL table prefix' ";
	}


	if (isset($missing_fields)) {
		$message = "<p style='color:red;'>You must fill all the fields. Following fields are missing<br />$missing_fields</p>";
		break;
	}

	// try to connect to db
	$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
	if (is_array($EZSQL_ERROR)) {
		$message = $EZSQL_ERROR[0]['error_str'];

		break;
	}
	$func = 'upgrade_from_'.$config['upgrade_from'].'_pre';
	if ($config['install_type'] == 'upgrade') {
		$step = 3;
	} else {
		$step = 4;
	}
	break;

case 3:


	break;

}

?>
