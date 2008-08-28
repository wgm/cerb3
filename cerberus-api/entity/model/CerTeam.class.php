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

class CerTeam extends CerEntityObject {
	var $_id;
	var $_name;
	var $_defaultResponseTime;
	var $_defaultSchedule;

	function CerTeam() {
		$this->CerEntityObject(); // constructor
	}

	function getId() {
		return $this->_id;
	}
	function setId($value) {
		settype($value,"integer");
		$this->_id = $value;
	}
	function getName() {
		return $this->_name;
	}
	function setName($value) {
		settype($value,"string");
		$this->_name = $value;
	}
	function getDefaultResponseTime() {
		return $this->_defaultResponseTime;
	}
	function setDefaultResponseTime($value) {
		settype($value,"integer");
		$this->_defaultResponseTime = $value;
	}
	function getDefaultSchedule() {
		return $this->_defaultSchedule;
	}
	function setDefaultSchedule($value) {
		settype($value,"integer");
		$this->_defaultSchedule = $value;
	}
}
