<?php

class CerPop3Account {
	var $_id;
	var $_name;
	var $_host;
	var $_port = 110;
	var $_login;
	var $_pass;
	var $_lastPolled;
	var $_disabled;
	var $_max_messages;
	var $_delete = 0;
	var $_lockTime;
	var $_maxSize;
	
	/*
	 * [JAS]: Getters/Setters
	 */
	function getId() {
		return $this->_id;
	}
	function setId($value) {
		$this->_id = $value;
	}
	
	function getName() {
		return $this->_name;
	}
	function setName($value) {
		$this->_name = $value;
	}
	
	function getHost() {
		return $this->_host;
	}
	function setHost($value) {
		$this->_host = $value;
	}
	
	function getPort() {
		return $this->_port;
	}
	function setPort($value) {
		settype($value,"integer");
		$this->_port = $value;
	}
	
	function getLogin() {
		return $this->_login;
	}
	function setLogin($value) {
		$this->_login = $value;
	}
	
	function getPass() {
		return $this->_pass;
	}
	function setPass($value) {
		$this->_pass = $value;
	}
	
	function getDisabled() {
		return $this->_disabled;
	}
	function setDisabled($value) {
		settype($value, "boolean");
		$this->_disabled = $value;
	}
	
	function getDelete() {
		return $this->_delete;
	}
	function setDelete($value) {
		settype($value, "boolean");
		$this->_delete = $value;
	}
	
	function getMaxMessages() {
		return $this->_max_messages;
	}
	function setMaxMessages($value) {
		settype($value, "integer");
		$this->_max_messages = $value;
	}
	
	function getLastPolled() {
		return $this->_lastPolled;
	}
	function setLastPolled($value) {
		$this->_lastPolled = intval($value);
	}
	
	function getLockTime() {
		return $this->_lockTime;
	}
	function setLockTime($value) {
		$this->_lockTime = intval($value);
	}
	
	function getMaxSize() {
		return $this->_maxSize;
	}
	function setMaxSize($value) {
		$this->_maxSize = intval($value);
	}
}