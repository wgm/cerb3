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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");


class ticket_requesters
{
	/**
    * DB abstraction layer handle
    *
    * @var object
    */
	var $db;

	function ticket_requesters() {
		$this->db =& database_loader::get_instance();
	}

	function get_requesters($ticket_id) { 
		
		$result =& $this->db->get("ticket", "get_ticket_requesters", array("ticket_id"=>$ticket_id));
		return $result;
	}
	
	/*
		
	*/
	function add_requester($ticket_id, $note, $createdByAgentId) {

	}
	
	function modify_requster($step_id, $note) {

	}
	
	function delete_requester($requster_id) {

	}

}

