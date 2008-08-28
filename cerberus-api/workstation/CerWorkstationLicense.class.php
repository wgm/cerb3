<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

if(!defined("VALID_INCLUDE")) define("VALID_INCLUDE",1);
require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

/*
 * Important Licensing Note from the Cerberus Helpdesk Team:
 * 
 * Yes, it would be really easy for you to to just cheat and edit this file to 
 * use the software without paying for it.  We're trusting the community to be
 * honest and understand that quality software backed by a dedicated team takes
 * money to develop.  We aren't volunteers over here, and we aren't working 
 * from our bedrooms -- we do this for a living.  This pays our rent, health
 * insurance, and keeps the lights on at the office.  If you're using the 
 * software in a commercial or government environment, please be honest and
 * buy a license.  We aren't asking for much. ;)
 * 
 * Encoding/obfuscating our source code simply to get paid is something we've
 * never believed in -- any copy protection mechanism will inevitably be worked
 * around.  Cerberus development thrives on community involvement, and the 
 * ability of users to adapt the software to their needs.
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas, the ability to
 * purchase add-on tools (e.g., Workstation, Standalone Parser) and the 
 * warm-fuzzy feeling of doing the right thing.
 *
 * Thanks!
 * -the Cerberus Helpdesk dev team (Jeff, Mike, Jerry, Darren, Brenan)
 * and Cerberus Core team (Luke, Alasdair, Vision, Philipp, Jeremy, Ben)
 *
 * http://www.cerberusweb.com/
 * support@cerberusweb.com
 */

class CerWorkstationLicense {
	var $key_xml;
	var $license_id;
	var $max_web_users;
	var $max_desktop_users;
	var $licensee;
	var $expires;
	var $enableJParser = false;
	var $hasLicense = false;
	
	function CerWorkstationLicense() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$sql = "SELECT `license_id`, `key_xml`, `licensed_to`, `max_web_users`, `max_desktop_users`, `expires`, `enable_jparser` FROM `workstation` LIMIT 0,1";
		$res = $db->query($sql);
		
		if($row = $db->grab_first_row($res)) {
			$this->key_xml = @$row["key_xml"];
			$this->license_id = @$row["license_id"];
			$this->licensee = @$row["licensed_to"];
			$this->max_web_users = intval(@$row["max_web_users"]);
			$this->max_desktop_users = intval(@$row["max_desktop_users"]);
			$this->expires = @$row["expires"];
			$this->enable_jparser = intval(@$row["enable_jparser"]);
			$this->hasLicense = true;
		}
	}
	
	function saveLicense($xml) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		// [JAS]: Clear old key info
		$sql = "DELETE FROM `workstation`";
		$db->query($sql);
		
		/* @var $workstation_xml xml_object */
		$xml_obj = xml_parser::parse_as_object($xml);
		$workstation_xml =& $xml_obj["workstation"][0];
		
		// [JAS]: Parse XML
		if(null != $workstation_xml) {
			$data_xml = $workstation_xml->get_child("data",0);

			if(null != $data_xml) {
				$key_id = $data_xml->get_child_data("license_id");
				$key_licensee = $data_xml->get_child_data("licensee");
				$key_max_web_users = $data_xml->get_child_data("max_web_users");
				$key_max_desktop_users = $data_xml->get_child_data("max_desktop_users");
				$key_expires = $data_xml->get_child_data("expires");
				$key_file = $data_xml->get_child_data("license");
				$enable_jparser = $data_xml->get_child_data("enable_jparser");
					
				$sql = sprintf("INSERT INTO `workstation` (`license_id`, `key_xml`,`licensed_to`,`max_web_users`,`max_desktop_users`,`expires`,`enable_jparser`) ".
					"VALUES (%s,%s,%s,'%d','%d',%s,%d)",
						$db->escape($key_id),
						$db->escape($key_file),
						$db->escape($key_licensee),
						intval($key_max_web_users),
						intval($key_max_desktop_users),
						$db->escape($key_expires),
						intval($enable_jparser)
				);
				$db->query($sql);
			}
				
		}
	}
	
	function getLicenseXml() {
		return $this->key_xml;
	}
	
	function getLicenseId() {
		return $this->license_id;
	}
	
	function getMaxDesktopUsers() {
		return intval($this->max_desktop_users);
	}
	
	function getMaxWebUsers() {
		return intval($this->max_web_users);
	}
	
	function getLicensee() {
		return $this->licensee;
	}
	
	function getExpiration() {
		return $this->expires;
	}
	
	function getEnableJParser() {
		return $this->enableJParser;
	}
	
	function hasLicense() {
		return $this->hasLicense;
	}
	
	/**
	* @return array
	* @desc Returns an array of Workstation enabled users (key is user id, value is user name)
	*/
	function getValidUsers() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = "SELECT `user_name`, `user_id` FROM `user` WHERE `user_ws_enabled` = 1";
		$res = $db->query($sql);
		
		$valid_users = array();
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$user_id = $row["user_id"];
			$user_name = $row["user_name"];
			$valid_users[$user_id] = $user_name;
		}
		unset($user_id);
		unset($user_name);
		
		return $valid_users;
	}
	
	/**
	* @return boolean
	* @param array $user_ids
	* @desc Enables an array of user IDs for Workstation access
	*/
	function saveValidUsers($user_ids) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$current_users = $this->getValidUsers();
		
		if(!is_array($user_ids))
			$user_ids = array($user_ids);

		foreach($user_ids as $idx => $user_id) {
			/* 
			 * [JAS]: If our user was a current user, remove them.  When we're done we'll
			 * remove any leftover users who had access revoked.
			 */
			if(isset($current_users[$user_id]))
				unset($current_users[$user_id]);
				
			// [JAS]: Ensure it's just an int
			$user_ids[$idx] = sprintf("%d", $user_id);
		}

		$update_string = "UPDATE `user` SET `user_ws_enabled` = '%d' WHERE `user_id` IN (%s)";
		
		// [JAS]: Enable selected agents
		if(is_array($user_ids) && !empty($user_ids)) {
			CerSecurityUtils::integerArray($user_ids);
			
			$sql = sprintf($update_string,
				1,
				implode(',',$user_ids)
			);
			$db->query($sql);
		}
		
		if(is_array($current_users) && !empty($current_users)) {
			$cur_ids = array_keys($current_users);
			CerSecurityUtils::integerArray($cur_ids);
			
			$sql = sprintf($update_string,
				0,
				implode(',',$cur_ids)
			);
			$db->query($sql);
			
		}
			
		return true;
	}
	
};