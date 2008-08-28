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

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");

class CerWorkstationSettings {
	var $ip_security_disabled = 0;
	var $valid_ips = array();
	
	/**
	* @return CerWorkstationSettings
	* @desc 
	*/
	function CerWorkstationSettings() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		// [JAS]: Standard settings
		$sql = "SELECT `ip_security_disabled` FROM `workstation_settings` LIMIT 0,1";
		$res = $db->query($sql);
		
		if($row = $db->grab_first_row($res)) {
			$this->setIpSecurityDisabled(@$row["ip_security_disabled"]);
		}
		
		// [JAS]: Valid IPs
		$sql = "SELECT `ip_mask` FROM `workstation_valid_ips`";
		$res = $db->query($sql);

		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$this->valid_ips[] = @$row["ip_mask"];
		}
	}
	
	/**
	* @return void
	* @param array $ips
	* @desc 
	*/
	function setValidIps($ips) {
		if(is_string($ips)) {
			$ips = str_replace(array("\r"," "),"",$ips);
			$this->valid_ips = split("\n",$ips);
		} elseif(is_array($ips)) {
			$this->valid_ips = $ips;	
		}
		
		unset($ips);
	}
	
	/**
	* @return array
	* @desc 
	*/
	function getValidIps() {
		if(!is_array($this->valid_ips))
			return array();
			
		return $this->valid_ips;
	}
	
	/**
	* @return boolean
	* @param string $ip
	* @desc Tests if an IP should pass session security
	*/
	function isValidIp($ip) {
		// [JAS]: Config Override
		if($this->hasIpSecurityDisabled())
			return true;
		
		// [JAS]: Test all our IPs for a wildcard match
		if(is_array($this->valid_ips))
		foreach($this->valid_ips as $mask) {
			if(empty($mask)) continue;
			if(0 == strcmp(substr($ip,0,strlen($mask)),$mask)) {
				return true;
			}
		}
			
		return false;
	}
	
	/**
	* @return void
	* @param bit $bool
	* @desc 
	*/
	function setIpSecurityDisabled($bool) {
		settype($bool, "integer");
		$this->ip_security_disabled = ($bool) ? 1 : 0;
	}
	
	/**
	* @return bit
	* @desc Returns a 0 if disabled and 1 if enabled
	*/
	function hasIpSecurityDisabled() {
		return $this->ip_security_disabled;
	}

	/**
	* @return void
	* @desc Saves settings to the database
	*/
	function saveSettings() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "DELETE FROM `workstation_settings`";
		$db->query($sql);
		$sql = "DELETE FROM `workstation_valid_ips`";
		$db->query($sql);
		
		settype($this->ip_security_disabled, "integer");
		
		$sql = sprintf("INSERT INTO `workstation_settings` (`ip_security_disabled`) VALUES ('%d')",
			$this->ip_security_disabled
		);
		$db->query($sql);
		
		if(is_array($this->valid_ips)) {
			foreach($this->valid_ips as $ip) {
				$sql = sprintf("INSERT INTO `workstation_valid_ips` (`ip_mask`) VALUES (%s)",
					$db->escape($ip)
				);
				$db->query($sql);
			}
		}
		
		unset($sql);
	}
	
};