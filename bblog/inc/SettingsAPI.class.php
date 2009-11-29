<?php
/**
 * ./bblog/inc/SettingsAPI.class.php
 *
 * @package default
 */


/*
   '||     '||'''|,  '||`
    ||      ||   ||   ||
    ||''|,  ||;;;;    ||  .|''|, .|''|,
    ||  ||  ||   ||   ||  ||  || ||  ||
   .||..|' .||...|'  .||. `|..|' `|..||
                                     ||
                                  `..|'

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



** Class Initially Designed by Elie `woe` BLETON (October 2003)
** Contact via lordwo@sourceforge.net
**
** Developpers who might want to use this API (wherever they want) should read the GPL
** license.
**
** BBLog plugin developers, consult documentation. I tried to comment some functions, so read the source !
** As always, __functions are not designed for external calls. They might prove useful, but are maily here to avoid
** code redundancy and to have a readable sourcecode.
**
** Little TODO:
** - Add addslashes()/stripslashes() everywhere it's required to support nifty setting name / setting values.
*/

class SettingsAPI {

	var $pBlog;                     // Reference to the bBlog object.
	var $db;                        // Reference to bBlog->db
	var $settings_table;            // Settings_table
	var $buffered_mode;             // Get all the settings on __construct(), and update the all thingie on __destruct()
	var $buffer = null;           // The settings buffer

	var $buffer_includes_defaults = FALSE;   // Optimizations for non-admin use.
	var $buffer_includes_descriptions = FALSE;  // Optimizations for non-admin use.

	//*************************************************************************
	// Constructor / Destructor / Properties handlers / Misc handlers
	//
	//*************************************************************************


	/**
	 *
	 *
	 * @param unknown $bBlog (reference)
	 */
	function SettingsAPI(&$bBlog) {
		$this->pBlog = &$bBlog;
		$this->db = &$bBlog->db;
		$this->settings_table = T_SETTINGS;
		$this->buffered_mode = FALSE;
	}





	/**
	 *
	 */
	function __destruct() {
		if ($this->buffered_mode == TRUE) {
			$this->UploadInternalBuffer();
		}
	}





	/**
	 *
	 */
	function CloseSettingsAPI() {
		// Dirty alias for PHP5 __destruct()
		// Must be called before closing the main script, or all changes will be lost if BUFFERED_MODE = TRUE
		// PLEASE THIS FUNCTION IF PHP VERSION IS < PHP5 ! (and please do not call __destruct directly - eventual PHP4 fixes will go here)
		$this->__destruct();
	}





	/**
	 *
	 *
	 * @param unknown $boolean
	 */
	function BufferedMode($boolean) {

		if (($this->buffer != null) && ($boolean == FALSE)) {
			// Switching from buffered to unbuffered mode after buffer loading : need to update before switching.
			// Buffer is cleared after operation.
			$this->UploadInternalBuffer();
			$this->buffer = null;
		}
		elseif (($this->buffer == null) && ($boolean == TRUE)) {
			// Enabling buffered mode. Need to init $this->buffer to array() structure for internal reasons.
			$this->buffer = array();
		}

		$this->buffered_mode = $boolean;
	}





	/**
	 *
	 *
	 * @param unknown $query
	 * @return unknown
	 */
	function SQL_Query($query) {
		// Small handler to the bBlog EZSQL Object.
		// (Won't be difficult to update if this were to change)
		return $this->pBlog->db->query($query);
	}


	//*************************************************************************
	// Buffer management
	//
	// Flags: (flag_REMOVE, flag_CREATE, flag_UPDATE)
	//
	// Architecture summary: $buffer[PLUGIN_GUID][SETTING_NAME] = array(
	//         "Value"   = array(KEY_VALUE, KEY_UPDATE),  | KEY_VALUE returns the setting current value
	//         "DefaultValue" = array(KEY_VALUE, KEY_UPDATE),  | KEY_VALUE returns the setting default_value
	//         "Description" = array(KEY_VALUE, KEY_UPDATE),  | KEY_VALUE returns the setting description
	//        "flag_REMOVE"          | TRUE if the whole setting is to be deleted from DB
	//        "flag_CREATE"          | TRUE if the whole setting is missing from DB
	//        "flag_UPDATE"          | TRUE if the entry requires an update
	//
	// KEY_UPDATE is a boolean that indicate, if true,
	// that the KEY_VALUE is desync from the one in the database.
	//
	// Please don't try to update the buffer manually, use the functions instead.
	// If you have a really good reason to do so, or if you are really so stubborn, you ARE on your own.
	// (Backup your database !)
	//
	//*************************************************************************




	/**
	 *
	 *
	 * @param unknown $with_default_values (optional)
	 * @param unknown $with_descriptions   (optional)
	 */
	function FillInternalBuffer($with_default_values = FALSE, $with_descriptions = FALSE) {
		// Retrieves every setting in database then add them to the buffer.
		// DEFAULT_VALUE and DESCRIPTION fields are not fetched unless specified. This is because they are not needed except in the Administration panel.

		$this->buffer_includes_defaults = $with_default_values;
		$this->buffer_includes_descriptions = $with_descriptions;


		// Deals with modifications of the SQL query because of DEFAULT_VALUE and/or DESCRIPTIONS flags

		$additional_select_text = "";

		if ($with_default_values == TRUE) {
			$additional_select_text .= ", `SettingDefaultValue`";
		}
		if ($with_descriptions == TRUE) {
			$additional_select_text .= ", `Description`";
		}

		$sql = "SELECT `HostPluginGUID`, `SettingName`, `SettingValue`" . $additional_select_text . " FROM `{$this->settings_table}`";

		// Fetch results from database

		$result = $this->SQL_Query($sql);

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$this->buffer[$row["HostPluginGUID"]][$row["SettingName"]] = array
			(
				"Value"         => array("key_value" => $row["SettingValue"],    "key_update" => FALSE),
				"flag_REMOVE" => FALSE,
				"flag_CREATE" => FALSE,
				"flag_UPDATE" => FALSE
			);

			if ($with_default_values == TRUE) {
				$this->buffer[$row["HostPluginGUID"]][$row["SettingName"]]["DefaultValue"] = array("key_value" => $row["SettingDefaultValue"],  "key_update" => FALSE);
			}

			if ($with_descriptions == TRUE) {
				$this->buffer[$row["HostPluginGUID"]][$row["SettingName"]]["Description"] = array("key_value" => $row["Description"],    "key_update" => FALSE);
			}

		}
	}





	/**
	 *
	 */
	function UploadInternalBuffer() {
		// Iterate through buffer and generate a full list of SQL statements that will be executed in one time.
		// Changes the update/remove/create flags to update the buffer as if it were freshly updated : calling the function twice will result in NO database extra work.
		// This will work efficiently with a relatively medium amount of settings to update/create/drop.
		// Actually, the class does its best to minimize statements to do here while in buffered mode. But there's surely a bit of optimization to make.
		//
		//
		// Feel free to contribute :)

		$sql_buffer = "";

		foreach ($this->buffer as $buffer_guid_entry => $buffer_settings_array) {
			foreach ($buffer_settings_array as $setting_name => $setting_array) {
				if ($setting_array["flag_CREATE"] == TRUE) {
					if (!isset($setting_array["DefaultValue"]["key_value"])) { $setting_array["DefaultValue"]["key_value"] = ""; }
					if (!isset($setting_array["Description"]["key_value"])) { $setting_array["Description"]["key_value"] = ""; }
					$sql_buffer .= $this->__InstallSettingSQL($buffer_guid_entry, $setting_name, $setting_array["Value"]["key_value"], $setting_array["DefaultValue"]["key_value"], $setting_array["Description"]["key_value"]);
				}
				elseif ($setting_array["flag_REMOVE"] == TRUE) {
					$sql_buffer .= $this->__UninstallSettingSQL($buffer_guid_entry, $setting_name);
				}
				elseif ($setting_array["flag_UPDATE"] == TRUE) {
					$keys = "";
					if ($setting_array["Value"]["key_update"] == TRUE) { $keys["Value"] = $setting_array["Value"]["key_value"]; }
					if ($setting_array["DefaultValue"]["key_update"] == TRUE) { $keys["DefaultValue"] = $setting_array["DefaultValue"]["key_value"]; }
					if ($setting_array["Description"]["key_update"] == TRUE) { $keys["Description"] = $setting_array["Description"]["key_value"]; }

					$sql_buffer .= $this->__SetSettingMultiSQL($buffer_guid_entry, $setting_name, $keys);
				}
				else {
					// Nothing to do with this entry
					// I leave this here just in case..
				}
			}
		}
	}


	//*************************************************************************
	// Table Install / Uninstall        (cannot be buffered)
	//
	// I advise everyone to provide such install functions in order to lighten
	// effectively installation scripts. This will be easier to maintain.
	//*************************************************************************


	/**
	 *
	 */
	function InstallSettingsTable() {
		$sql = "
            CREATE TABLE `{$this->settings_table}`
            (
                `SettingID` int(10) unsigned NOT NULL auto_increment,
                `HostPluginGUID` varchar(255) NOT NULL default '',
                `SettingName` varchar(255) NOT NULL default '',
                `SettingValue` varchar(255) NOT NULL default '',
                `SettingDefaultValue` varchar(255) NOT NULL default '',
                `Description` text NOT NULL,
                PRIMARY KEY  (`SettingID`),
                KEY `HostPluginGUID` (`HostPluginGUID`)
            )
            TYPE=MyISAM AUTO_INCREMENT=2 ;
        ";


	}





	/**
	 *
	 */
	function ClearSettingsTable() {
		$sql = "DELETE FROM `{$this->settings_table}`;";
	}





	/**
	 *
	 */
	function DropSettingsTable() {
		$sql = "DROP TABLE IF EXISTS `{$this->settings_table}`;";
	}


	//*************************************************************************
	// Settings Install / Uninstall     (can be buffered)
	//
	//*************************************************************************


	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @param unknown $setting_value
	 * @param unknown $setting_default_value
	 * @param unknown $description           (optional)
	 * @return unknown
	 */
	function InstallSetting($plugin_guid, $setting_name, $setting_value, $setting_default_value, $description = '') {
		$sql = "INSERT INTO `{$this->settings_table}`   (`SettingID`, `HostPluginGUID`, `SettingName`, `SettingValue`, `SettingDefaultValue`, `Description`)
                VALUES                                  (''         , '$plugin_guid'  , '$setting_name', '$setting_value', '$setting_default_value', '$description');";

		if ($this->buffered_mode == TRUE) {
			$this->buffer[$plugin_guid][$setting_name] = array
			(
				"Value"         => array("key_value" => $setting_value,    "key_update" => FALSE),
				"DefaultValue"  => array("key_value" => $setting_default_value,  "key_update" => FALSE),
				"Description"   => array("key_value" => $description,     "key_update" => FALSE),
				"flag_REMOVE" => FALSE,
				"flag_CREATE" => TRUE,
				"flag_UPDATE" => FALSE
			);

			if ((isset($this->buffer[$plugin_guid][$setting_name]["flag_REMOVE"]) && ($this->buffer[$plugin_guid][$setting_name]["flag_REMOVE"] == TRUE))) {
				$this->buffer[$plugin_guid][$setting_name]["flag_REMOVE"] = FALSE;
			}

			return TRUE;
		}
		else {
			$sql = $this->__InstallSettingSQL($plugin_guid, $setting_name, $setting_value, $setting_default_value, $description);
			return $this->SQL_Query($sql);
		}

	}





	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @param unknown $setting_value
	 * @param unknown $setting_default_value
	 * @param unknown $description           (optional)
	 * @return unknown
	 */
	function __InstallSettingSQL($plugin_guid, $setting_name, $setting_value, $setting_default_value, $description = '') {
		$sql_string = "INSERT INTO `{$this->settings_table}`   (`SettingID`, `HostPluginGUID`, `SettingName`, `SettingValue`, `SettingDefaultValue`, `Description`)
                	   VALUES                                  (''         , '$plugin_guid'  , '$setting_name', '$setting_value', '$setting_default_value', '$description');\n";

		return $sql_string;
	}








	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @return unknown
	 */
	function UninstallSetting($plugin_guid, $setting_name) {

		if ($this->buffered_mode == TRUE) {
			if ($this->buffer[$plugin_guid][$setting_name]["flag_CREATE"] == TRUE) {
				$this->buffer[$plugin_guid][$setting_name]["flag_CREATE"] = FALSE;
			}

			$this->buffer[$plugin_guid][$setting_name]["flag_REMOVE"] = TRUE;
			return TRUE;
		}
		else {
			$sql = $this->__UninstallSettingSQL($plugin_guid, $setting_name);
			return $this->SQL_Query($sql);
		}
	}





	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @return unknown
	 */
	function __UninstallSettingSQL($plugin_guid, $setting_name) {
		$sql_string = "DELETE FROM `{$this->settings_table}` WHERE `HostPluginGUID` = '$plugin_guid' AND `SettingName` = '$setting_name' LIMIT 1;\n";
		return $sql_string;
	}


	//*************************************************************************
	// Settings::SET                    (can be buffered)
	//
	//*************************************************************************


	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @param unknown $keys
	 * @return unknown
	 */
	function __SetSettingMultiSQL($plugin_guid, $setting_name, $keys) {
		// Even if this function shouldn't be used by anyone, I'll document it a bit, even if it's just for me:
		// The purpose of this function is to generate a custom UPDATE query, that can update 1 or more keys for a defined setting.
		// The $keys parameter must be an associative array : keys of this array are the keynames, values are the keyvalues.
		// Beware updating this function : it's heavily used by all Set_ functions, and by the UpdateBuffer that relies mainly on this one for all
		// updates to the buffer.

		if (isarray($keys)) {
			$set_string = "";
			$parse = "";

			foreach ($keys as $key_name => $key_value) {
				$set_string .= $parse . "`$key_name` = '$key_value'";
				if ($parse == "") { $parse = ", "; }
			}
		}
		else {
			return FALSE;
		}

		$sql_string = "UPDATE `{$this->settings_table}` SET $set_string WHERE `HostPluginGUID` = '$plugin_guid' AND `SettingName` = '$setting_name' LIMIT 1;\n";
		return $sql_string;
	}





	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @param unknown $setting_value
	 * @return unknown
	 */
	function SetSetting($plugin_guid, $setting_name, $setting_value) {
		if ($this->buffered_mode == TRUE) {
			$temp_update = strcmp($this->buffer[$plugin_guid][$setting_name]["Value"]["key_value"], $setting_value);
			if ($temp_update != 0) { $temp_update = TRUE; } else { $temp_update = FALSE; }


			$this->buffer[$plugin_guid][$setting_name]["Value"] = array
			(
				"key_value"     =>  $setting_value,
				"key_update"    =>  $temp_update
			);

			$this->buffer[$plugin_guid][$setting_name]["flag_UPDATE"] = ($this->buffer[$plugin_guid][$setting_name]["flag_UPDATE"] || $temp_update);
			return TRUE;
		}
		else {
			$sql = $this->__SetSettingMultiSQL($plugin_guid, $setting_name, array("SettingValue" => $setting_value));
			return $this->SQL_Query($sql);
		}
	}








	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @param unknown $setting_default_value
	 * @return unknown
	 */
	function SetSettingDefault($plugin_guid, $setting_name, $setting_default_value) {
		if (($this->buffered_mode == TRUE) && ($this->buffer_includes_defaults == FALSE)) {
			// No support for defaults values if they were not fetched.
			// There could be one, but all the scripts that doesn't read default values (i.e all non-admin scripts) shouldn't change default values.
			return FALSE;
		}

		if ($this->buffered_mode == TRUE) {
			$temp_update = strcmp($this->buffer[$plugin_guid][$setting_name]["DefaultValue"]["key_value"], $setting_default_value);
			if ($temp_update != 0) { $temp_update = TRUE; } else { $temp_update = FALSE; }

			$this->buffer[$plugin_guid][$setting_name]["DefaultValue"] = array
			(
				"key_value"     =>  $setting_default_value,
				"key_update"    =>  $temp_update
			);

			$this->buffer[$plugin_guid][$setting_name]["flag_UPDATE"] = ($this->buffer[$plugin_guid][$setting_name]["flag_UPDATE"] || $temp_update);
			return TRUE;
		}
		else {
			$sql = $this->__SetSettingMultiSQL($plugin_guid, $setting_name, array("SettingDefaultValue" => $setting_default_value));
			return $this->SQL_Query($sql);
		}
	}





	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @param unknown $setting_description
	 * @return unknown
	 */
	function SetSettingDescription($plugin_guid, $setting_name, $setting_description) {
		if (($this->buffered_mode == TRUE) && ($this->buffer_includes_descriptions == FALSE)) {
			// No support for descriptions values if they were not fetched.
			// There could be one, but all the scripts that doesn't read descriptions (i.e all non-admin scripts) shouldn't change descriptions.
			return FALSE;
		}

		if ($this->buffered_mode == TRUE) {
			$temp_update = strcmp($this->buffer[$plugin_guid][$setting_name]["Description"]["key_value"], $setting_description);
			if ($temp_update != 0) { $temp_update = TRUE; } else { $temp_update = FALSE; }

			$this->buffer[$plugin_guid][$setting_name]["Description"] = array
			(
				"key_value"     =>  $setting_description,
				"key_update"    =>  $temp_update
			);

			$this->buffer[$plugin_guid][$setting_name]["flag_UPDATE"] = ($this->buffer[$plugin_guid][$setting_name]["flag_UPDATE"] || $temp_update);
			return TRUE;
		}
		else {
			$sql = $this->__SetSettingMultiSQL($plugin_guid, $setting_name, array("Description" => $setting_description));
			return $this->SQL_Query($sql);
		}
	}











	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @return unknown
	 */
	function SetSettingToDefault($plugin_guid, $setting_name) {
		if (($this->buffered_mode == TRUE) && ($this->buffer_includes_defaults == FALSE)) {
			// Cannot set anything to his default value in buffered mode if defaults weren't fetched from DB first.
			return FALSE;
		}

		if ($this->buffered_mode == TRUE) {
			$temp_update = strcmp($this->buffer[$plugin_guid][$setting_name]["Value"]["key_value"], $this->buffer[$plugin_guid][$setting_name]["DefaultValue"]["key_value"]);
			if ($temp_update != 0) { $temp_update = TRUE; } else { $temp_update = FALSE; }

			$this->buffer[$plugin_guid][$setting_name]["Value"] = array
			(
				"key_value"  =>  $this->buffer[$plugin_guid][$setting_name]["DefaultValue"]["key_value"],
				"flag_UPDATE"   =>  ($this->buffer[$plugin_guid][$setting_name]["flag_UPDATE"] || $temp_update)
			);

			return TRUE;
		}
		else {
			$sql = "UPDATE `{$this->settings_table}` SET `SettingValue` = `SettingDefaultValue` WHERE `HostPluginGUID` = '$plugin_guid' AND `SettingName` = '$setting_name'";
			return $this->SQL_Query($sql);
		}
	}





	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @return unknown
	 */
	function SetAllSettingsToDefault($plugin_guid) {
		if (($this->buffered_mode == TRUE) && ($this->buffer_includes_defaults == FALSE)) {
			// Cannot set anything to his default value in buffered mode if defaults weren't fetched from DB first.
			return FALSE;
		}

		if ($this->buffered_mode == TRUE) {
			foreach ($this->buffer[$plugin_guid] as $key => $value) {
				$this->SetSettingToDefault($plugin_guid, $key);
			}

			return TRUE;
		}
		else {
			$sql = "UPDATE `{$this->settings_table}` SET `SettingValue` = `SettingDefaultValue` WHERE `HostPluginGUID` = '$plugin_guid'";
			return $this->SQL_Query($sql);
		}
	}


	//*************************************************************************
	// Settings::GET                    (can be buffered)
	//
	//*************************************************************************


	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @return unknown
	 */
	function GetSetting($plugin_guid, $setting_name) {
		$sql = "SELECT `SettingValue` FROM `{$this->settings_table}` WHERE `HostPluginGUID` = '$plugin_guid' AND `SettingName` = '$setting_name' LIMIT 1";

		if ($this->buffered_mode == TRUE) {
			return $this->buffer[$plugin_guid][$setting_name]["Value"]["key_value"];
		}
		else {
			$result = $this->SQL_Query($sql);
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			return $row["SettingValue"];
		}
	}





	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @return unknown
	 */
	function GetSettingDefault($plugin_guid, $setting_name) {
		if (($this->buffered_mode == TRUE) && ($this->buffer_includes_defaults == FALSE)) {
			// Please be coherent mister user ! How could this work if you haven't specified to fetch descriptions ?
			return FALSE;
		}

		$sql = "SELECT `SettingDefaultValue` FROM `{$this->settings_table}` WHERE `HostPluginGUID` = '$plugin_guid' AND `SettingName` = '$setting_name' LIMIT 1";

		if ($this->buffered_mode == TRUE) {
			return $this->buffer[$plugin_guid][$setting_name]["DefaultValue"]["key_value"];
		}
		else {
			$result = $this->SQL_Query($sql);
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			return $row["SettingDefaultValue"];
		}
	}





	/**
	 *
	 *
	 * @param unknown $plugin_guid
	 * @param unknown $setting_name
	 * @return unknown
	 */
	function GetSettingDescription($plugin_guid, $setting_name) {
		if (($this->buffered_mode == TRUE) && ($this->buffer_includes_descriptions == FALSE)) {
			// Please be coherent mister user ! How could this work if you haven't specified to fetch descriptions ?
			return FALSE;
		}

		$sql = "SELECT `Description` FROM `{$this->settings_table}` WHERE `HostPluginGUID` = '$plugin_guid' AND `SettingName` = '$setting_name' LIMIT 1";

		if ($this->buffered_mode == TRUE) {
			return $this->buffer[$plugin_guid][$setting_name]["Description"]["key_value"];
		}
		else {
			$result = $this->SQL_Query($sql);
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			return $row["Description"];
		}
	}


}
