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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/requester.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/address.class.php");

class ticket_properties_saver
{
	/**
    * DB abstraction layer handle
    *
    * @var object
    */
	var $db;
	var $ticket_id;
	var $subject;
	var $status;
	var $delete_requesters;
	var $add_requesters;
	
	var $subject_error;
	var $req_delete_error;
	var $req_add_error;
	
	//var $requesters_without_addresses;
	var $addresses_to_create;

	/**
	 * A class for saving ticket properties
	 *
	 * @param string $subject
	 * @param array $delete_requesters
	 * @param array $add_requesters
	 */
	function ticket_properties_saver($ticket_id, $subject, $status, $delete_requesters, $add_requesters) {
		$this->db =& database_loader::get_instance();
		$this->ticket_id =  $ticket_id;
		$this->subject = $subject;
		$this->status = $status;
		$this->delete_requesters = $delete_requesters;
		$this->add_requesters = $add_requesters;
		$this->errors = array();
		$this->subject_error=false;
		$this->req_delete_error = false;
		$this->req_add_error = false;
		$this->set_addresses_to_create();
		
	}
	
	function set_addresses_to_create() {
		$requesters_without_addresses = array();
		foreach ($this->add_requesters AS $requester) {
			if($requester->address->email == 0) {
				$addresses_to_create[] =& $requester->address;
			}
		}
		$this->addresses_to_create = $addresses_to_create;
	}
	
	/*
	 * 
	 */
	function save() { 
		$result =& $this->db->get("ticket", "update_ticket_subject", array("ticket_id"=>$this->ticket_id,"subject"=>$this->subject,"status"=>$this->status));
		if($result===FALSE) {
			$this->subject_error=true;
		}
		
		if(sizeof($this->delete_requesters) > 0) {
			//$result =& $this->db->Get("ticket","delete_requesters", array("ticket_id"=>$this->ticket_id,"address_list"=>$this->delete_requesters));
			$address_list = array();
			foreach ($this->delete_requesters AS $requester) {
				$address_list[] = $requester->address->email;
			}
			$result =& $this->db->Get("ticket","delete_requesters", array("ticket_id"=>$this->ticket_id,"address_list"=>$address_list));
			if($result===FALSE) {
				$this->req_delete_error=true;
			}
		}
		if(sizeof($this->addresses_to_create) > 0) {
			foreach ($this->addresses_to_create AS $address) {
				//gets the address  if it exists, otherwise creates it
				$result =& $this->db->Get("addresses","get_address_id", array("address"=>$address->email));
				if($result !== FALSE && is_int($result)) {
					$address->id = $result;
				}
			}
		}
		
		if(sizeof($this->add_requesters) > 0) {
			$address_list = array();
			foreach ($this->add_requesters AS $requester) {
				$address_list[] = $requester->address->email;
			}			
			$result =& $this->db->Get("ticket","add_requesters", array("ticket_id"=>$this->ticket_id,"address_list"=>$address_list));
			if($result===FALSE) {
				$this->req_add_error=true;
			}
		}
		
	}
	
}

