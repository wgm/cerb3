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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/requesters/requester.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/requesters/ticket_requesters.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/ticket_properties_saver.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/requester.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class gets the ticket steps for a ticket
 *
 */
class save_ticket_properties_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var xml xml_object
    */
   var $xml;

   /**
    * Class constructor
    *
    * @param object $xml
    * @return get_listeners_handler
    */
   function save_ticket_properties_handler(&$xml) {
      $this->xml =& $xml;
   }

   /**
    * main() function for this class. 
    *
    */
	function process() {
		/*
		@var xml xml_object
		*/
		$users_obj =& new general_users();
		if($users_obj->check_login() === FALSE) {
			xml_output::error(0, 'Not logged in. Please login before proceeding!');
		}

		$ticket_obj =& $this->xml->get_child('ticket', 0);
		$ticket_id = $ticket_obj->get_attribute('id', FALSE);
		
		$subject = $this->xml->get_child_data("subject") ;
		$status  = $this->xml->get_child_data("status") ;
		
		$delete_requesters_elm =& $this->xml->get_child('delete_requesters', 0);
		$requester_elms =& $delete_requesters_elm->get_children("requester");
		$delete_requesters = array();
		if(is_array($requester_elms))
		foreach ($requester_elms AS $requester_elm) {
			$requester = requester2::createFromXML($requester_elm);
			$delete_requesters[] = $requester;// $requester_elm->get_attribute('address', FALSE);
		}
		
		$add_requesters_elm =& $this->xml->get_child('add_requesters', 0);
		$requester_elms =& $add_requesters_elm->get_children("requester");
		$add_requesters = array();
		if(is_array($requester_elms))
		foreach ($requester_elms AS $requester_elm) {
			$requester = requester2::createFromXML($requester_elm);
			$add_requesters[] = $requester;
		}

		$ticket_properties_saver =& new ticket_properties_saver($ticket_id, $subject, $status, $delete_requesters, $add_requesters);
		//$ticket_properties_saver =& new ticket_properties_saver($ticket_id, $subject, $delete_requesters, $add_requesters);
		$ticket_properties_saver->save();
		
		
		if($ticket_properties_saver->subject_error || $ticket_properties_saver->req_delete_error || $ticket_properties_saver->req_add_error) {
			xml_output::error(0, "Failed to save ticket properties");
		}
		else {
			xml_output::success();
		}
		
   }
   
}
