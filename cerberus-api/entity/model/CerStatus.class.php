<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2007, WebGroup Media LLC
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
|		Daniel Hildebrandt	(hildy@webgroupmedia.com)		[DDH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class CerStatus extends CerEntityObject {
	var $_id;
	var $_text;

	function CerStatus() {
		$this->CerEntityObject(); // constructor
	}

	function getId() {
		return $this->_id;
	}
	function setId($value) {
		settype($value,"integer");
		$this->_id = $value;
	}
	function getText() {
		return $this->_text;
	}
	function setText($value) {
		settype($value,"string");
		$this->_text = $value;
	}

}
