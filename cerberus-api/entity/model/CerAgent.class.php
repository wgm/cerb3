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

class CerAgent extends CerEntityObject {
	var $_id; // PK
	var $_realName;
	var $_displayName;
	var $_email;
	var $_login;
	var $_pass;
	var $_groupId;
	var $_lastLogin;
	var $_superuser;
	var $_disabled;
	var $_xsp;
	var $_wsEnabled;
	
	function CerAgent() {
		$this->CerEntityObject(); // constructor
	}
	
	function getId() {
		return $this->_id;
	}
	function setId($value) {
		settype($value,"integer");
		$this->_id = $value;
	}
	
	function getRealName() {
		return $this->_realName;
	}
	function setRealName($value) {
		settype($value,"string");
		$this->_realName = $value;
	}
	
	function getDisplayName() {
		return $this->_displayName;
	}
	function setDisplayName($value) {
		settype($value,"string");
		$this->_displayName = $value;
	}
	
	function getEmail() {
		return $this->_email;
	}
	function setEmail($value) {
		settype($value,"string");
		$this->_email = $value;
	}
	
	function getLogin() {
		return $this->_login;
	}
	function setLogin($value) {
		settype($value, "string");
		$this->_login = $value;
	}
	
	function getPassword() {
		return $this->_pass;
	}
	function setPassword($value) {
		settype($value,"string");
		$this->_pass = $value;
	}
	
	function getGroupId() {
		return $this->_groupId;
	}
	function setGroupId($value) {
		settype($value,"integer");
		$this->_groupId = $value;
	}
	
	function getLastLogin() {
		return $this->_lastLogin;
	}
	function setLastLogin($value) {
//		settype($value,"");
		$this->_lastLogin = $value;
	}
	
	function getSuperuser() {
		return $this->_superuser;
	}
	function setSuperuser($value) {
		settype($value,"boolean");
		$this->_superuser = $value;
	}
	
	function getDisabled() {
		return $this->_disabled;
	}
	function setDisabled($value) {
		settype($value,"boolean");
		$this->_disabled = $value;
	}
	
	function getXsp() {
		return $this->_xsp;
	}
	function setXsp($value) {
		settype($value,"boolean");
		$this->_xsp = $value;
	}
	
	function getWsEnabled() {
		return $this->_wsEnabled;
	}
	function setWsEnabled($value) {
		settype($value,"boolean");
		$this->_wsEnabled = $value;
	}
	
}