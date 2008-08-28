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

class CerCronTask {
	var $_id;
	var $_title;
	var $_script;
	var $_enabled;
	var $_minute;
	var $_hour;
	var $_dayOfMonth;
	var $_dayOfWeek;
	var $_nextRuntime;
	var $_lastRuntime;
	
	function CerCronTask() {
	}
	
	function setId($id) {
		settype($id,"integer");
		$this->_id = $id;
	}
	function getId() {
		return $this->_id;
	}
	
	function setTitle($str) {
		settype($str,"string");
		$this->_title = $str;
	}
	function getTitle() {
		return $this->_title;
	}
	
	function setScript($str) {
		settype($str,"string");
		$this->_script = $str;
	}
	function getScript() {
		return $this->_script;
	}
	
	function setEnabled($bool) {
		settype($bool,"integer");
		$this->_enabled = $bool;
	}
	function getEnabled() {
		return $this->_enabled;
	}
	
	function setMinute($str) {
		settype($str,"string");
		$this->_minute = $str;
	}
	function getMinute() {
		return $this->_minute;
	}
	
	function setHour($str) {
		settype($str,"string");
		$this->_hour = $str;
	}
	function getHour() {
		return $this->_hour;
	}
	
	function setDayOfMonth($str) {
		settype($str,"string");
		$this->_dayOfMonth = $str;
	}
	function getDayOfMonth() {
		return $this->_dayOfMonth;
	}
	
	function setDayOfWeek($str) {
		settype($str,"string");
		$this->_dayOfWeek = $str;
	}
	function getDayOfWeek() {
		return $this->_dayOfWeek;
	}
	
	function setNextRuntime($i) {
		settype($i,"integer");
		$this->_nextRuntime = $i;
	}
	function getNextRuntime() {
		return $this->_nextRuntime;
	}
	
	function setLastRuntime($i) {
		settype($i,"integer");
		$this->_lastRuntime = $i;
	}
	function getLastRuntime() {
		return $this->_lastRuntime;
	}
	
};
