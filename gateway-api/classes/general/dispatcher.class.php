<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class general_dispatcher
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	var $db;

	var $departments_list;
	var $teams_list;   
	var $members_list;
	var $sla_list;
	var $priorities_list;

	function general_dispatcher() {
		$this->db =& database_loader::get_instance();
		$this->get_departments_list();
		$this->get_teams_list();
		$this->get_members_list();
		$this->get_sla_list();
		$this->get_priorities_list();
	}


	function get_departments_list() {
		$this->departments_list = $this->db->Get("departments", "get_departments_list", array());
	}

	function get_teams_list() {
		$this->teams_list =  $this->db->Get("departments", "get_all_teams", array());
	}
	
	function get_members_list() {
		$this->members_list = $this->db->Get("teams", "get_all_members", array());
	}
	
	function get_sla_list() {
		$this->sla_list = $this->db->Get("sla", "get_slas", array());
	}
	
	function get_priorities_list() {
		
		$this->priorities_list = array("0"=>"Unassigned",
		"5"=>"None",
		"25"=>"Low",
		"50"=>"Medium",
		"75"=>"High",
		"90"=>"Critical",
		"100"=>"Emergency");
	}

}