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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class gets the ticket steps for a ticket
 *
 */
class get_ticket_requesters_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
   var $xml;

   /**
    * Class constructor
    *
    * @param object $xml
    * @return get_listeners_handler
    */
   function get_ticket_requesters_handler(&$xml) {
      $this->xml =& $xml;
   }

   /**
    * main() function for this class. 
    *
    */
	function process() {
		$users_obj =& new general_users();
		if($users_obj->check_login() === FALSE) {
			xml_output::error(0, 'Not logged in. Please login before proceeding!');
		}

		$ticket_obj =& $this->xml->get_child('ticket', 0);
		$ticket_id = $ticket_obj->get_attribute('id', FALSE);
		
		$ticket_requesters =& new ticket_requesters();
		$requesters = $ticket_requesters->get_requesters($ticket_id);
		
		if($requesters === FALSE) {
			xml_output::error(0, "Failed to retrieve ticket requesters");
		}
		else {
			$this->output_xml($requesters);
		}
		
   }
   
	function output_xml($requesters) {
		/*
		@var $requester requester 
		*/

		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$requesters_elm =& $data->add_child("requesters", xml_object::create("requesters"));
		
		if(is_array($requesters))
		foreach ($requesters as $requester) {
			$requester_elm =& $requesters_elm->add_child("requester", xml_object::create("requester"));
			$requester_elm->add_child("address", xml_object::create("address", $requester->address, array("id"=>$requester->address_id)));
			$requester_elm->add_child("is_primary", xml_object::create("is_primary", $requester->is_primary));
			if($requester->public_user_id != "") {
				$contact_elm =& $requester_elm->add_child("contact", xml_object::create("contact", NULL, array("id"=>$requester->public_user_id)));
				$contact_elm->add_child("first_name", xml_object::create("first_name", $requester->first_name));
				$contact_elm->add_child("last_name", xml_object::create("last_name", $requester->last_name));
				if($requester->company_id != "") {
					$contact_elm->add_child("company", xml_object::create("company", $requester->company_name, array("id"=>$requester->company_id)));
				}
			}
		}
		
		xml_output::success();
	}
}
/* 
<?xml version="1.0" encoding="UTF-8"?>
<cerberus_xml>
   <channel>email</channel>
   <module>workflow</module>
   <command>get_ticket_steps</command>
   <data>
	<ticket id="2"/>
   </data>
</cerberus_xml>
*/

