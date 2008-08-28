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
|		Daniel Hildebrandt		(hildy@webgroupmedia.com)		[DDH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/entity/CerEntityObject.class.php");

class CerStatuses extends CerEntityObjectController {

	function CerStatuses($validate=true) {
		$this->CerEntityObjectController("ticket_status","CerStatus");
		
		// [TODO]: Make this all a column model later with the _validate() being automatic?
		$this->_addColumn("Id","ticket_status_id","integer");
		$this->_addColumn("Text","ticket_status_text","string");
		
		if($validate) $this->_validate();
	}
	
	function getInstance() {
		static $instance = null;
		
		if(null == $instance) {
			$instance = new CerStatuses();
		}
		
		return $instance;
	}
	
}
