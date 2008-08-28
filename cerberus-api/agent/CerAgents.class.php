<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2006, WebGroup Media LLC
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

require_once(FILESYSTEM_PATH . "cerberus-api/entity/CerEntityObject.class.php");

class CerAgents extends CerEntityObjectController {

	// Use? DATE_FORMAT(`user_last_login`,'%Y-%m-%d %H:%i:%s')
	function CerAgents() {
		$this->CerEntityObjectController("user","CerAgent");
		
		// [TODO]: Make this all a column model later with the _validate() being automatic?
		$this->_addColumn("Id","user_id","integer");
		$this->_addColumn("RealName","user_name","string");
		$this->_addColumn("DisplayName","user_display_name","string");
		$this->_addColumn("Email","user_email","string");
		$this->_addColumn("Login","user_login","string");
		$this->_addColumn("Password","user_password","string");
		$this->_addColumn("LastLogin","user_last_login","string");
		$this->_addColumn("Superuser","user_superuser","boolean");
		$this->_addColumn("Disabled","user_disabled","boolean");
		$this->_addColumn("Xsp","user_xsp","boolean");
		$this->_addColumn("WsEnabled","user_ws_enabled","boolean");
		$this->_validate();
	}
	
	function getNameById($user_id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
		$sql = sprintf("SELECT u.user_name FROM user u WHERE u.user_id = %d",
			$user_id
		);
		$res = $db->query($sql);
		$user_name = "";
		if($db->num_rows($res)) {
			$row = $db->fetch_row($res);
			$user_name = stripslashes($row['user_name']);
		}
		
		return $user_name;	
	}
	
	function getSignature($user_id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
		$sql = sprintf("SELECT sig_content FROM user_sig WHERE user_id = %d",
			$user_id
		);
		$res = $db->query($sql);
		
		if($row = $db->grab_first_row($res)) {
			return stripslashes($row['sig_content']);
		}
		
		return "";
	}
	
	function isAgentAddress($address) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */

		$sql = sprintf("SELECT u.user_id FROM user u WHERE u.user_email != '' AND u.user_email = %s",
			$db->escape($address)
		);
		$addy_res = $db->query($sql);

		if($db->num_rows($addy_res))
		{
			if($row = $db->fetch_row($addy_res))
			return $row["user_id"];
		}

		return false;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return CerAgents
	 */
	function getInstance() {
		static $instance = null;
		
		if(null == $instance) {
			$instance = new CerAgents();
		}
		
		return $instance;
	}
	
}
