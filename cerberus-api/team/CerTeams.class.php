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

class CerTeams extends CerEntityObjectController {

	// Use? DATE_FORMAT(`user_last_login`,'%Y-%m-%d %H:%i:%s')
	function CerTeams($validate=true) {
		$this->CerEntityObjectController("team","CerTeam");
		
		// [TODO]: Make this all a column model later with the _validate() being automatic?
		$this->_addColumn("Id","team_id","integer");
		$this->_addColumn("Name","team_name","string");
		$this->_addColumn("DefaultResponseTime","default_response_time","integer");
		$this->_addColumn("DefaultSchedule","default_schedule","integer");
		
		if($validate) $this->_validate();
	}
	
	function getInstance() {
		static $instance = null;
		
		if(null == $instance) {
			$instance = new CerTeams();
		}
		
		return $instance;
	}
	
}
