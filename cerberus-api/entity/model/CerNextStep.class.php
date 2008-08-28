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
|		Mike Fogg		(mike@webgroupmedia.com)		[mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/


class CerNextStep extends CerEntityObject {
	var $_id;
	var $_ticketId;
	var $_dateCreated;
	var $_createdByAgentId;
	var $_createdByAgentName;
	var $_note;

	function CerNextStep() {
		$this->CerEntityObject(); // constructor
	}

	function getId() {
		return $this->_id;
	}
	function setId($value) {
		settype($value,"integer");
		$this->_id = $value;
	}
	function getTicketId() {
		return $this->_ticketId;
	}
	function setTicketId($value) {
		settype($value,"integer");
		$this->_ticketId = $value;
	}
	function getDateCreated() {
		return $this->_dateCreated;
	}
	function setDateCreated($value) {
		settype($value,"integer");
		$this->_dateCreated = $value;
	}
	function getCreatedByAgentId() {
		return $this->_createdByAgentId;
	}
	function setCreatedByAgentId($value) {
		settype($value,"integer");
		$this->_createdByAgentId = $value;
	}
	function getCreatedByAgentName() {
		return $this->_createdByAgentName;
	}
	function setCreatedByAgentName($value) {
		// [ddh]: CERB-549: allows comments made by deleted agents to still show up in ticket thread.
		if ($value == null) {
			$this->_createdByAgentName = '[deleted]';
		} else {
			settype($value,"string");
			$this->_createdByAgentName = $value;
		}
	}
	function getNote() {
		return $this->_note;
	}
	function setNote($value) {
		settype($value,"string");
		$this->_note = $value;
	}
}
