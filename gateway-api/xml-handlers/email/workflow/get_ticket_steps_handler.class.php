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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/ticket_steps.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class gets the ticket steps for a ticket
 *
 */
class get_ticket_steps_handler extends xml_parser
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
   function get_ticket_steps_handler(&$xml) {
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
		
      
		$ticket_steps =& new ticket_steps();
		$steps = $ticket_steps->get_steps_for_ticket($ticket_id);
		
		$tickets = array();
		
		$this->output_xml($steps);      

   }
   
	function output_xml($steps) {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$ticket_steps =& $data->add_child("ticket_steps", xml_object::create("ticket_steps"));
		
		if(is_array($steps))
		foreach ($steps as $step) {
			$ticket_step =& $ticket_steps->add_child("ticket_step", xml_object::create("ticket_step", NULL, array("id"=>$step->id)));
			$ticket_step->add_child("date_created", xml_object::create("date_created", $step->dateCreated));
			$ticket_step->add_child("created_by_agent", xml_object::create("created_by_agent", $step->createdByAgentName, array("id"=>$step->createdByAgentId)));
			$ticket_step->add_child("note", xml_object::create("note", $step->note));
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

