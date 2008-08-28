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
 * This class handles getting the listeners for a ticket (ie requesters and watchers
 *
 */
class add_ticket_step_handler extends xml_parser
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
   function add_ticket_step_handler(&$xml) {
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

		$step_obj =& $this->xml->get_child('ticket_step', 0);
		
		//$date_created = $step_obj->get_child_data("date_created");
		$note = $step_obj->get_child_data("note");
		$createdBy = $step_obj->get_child_data("created_by_agent_id");
		
		$ticket_obj =& $step_obj->get_child("ticket", 0);
		$ticket_id = $ticket_obj->get_attribute('id', FALSE);
		$ticket_steps =& new ticket_steps();
		$step = $ticket_steps->add_step($ticket_id, $note, $createdBy);
		
		if($step == null) {
			xml_output::error(0, 'Failed creating new ticket step.');
		}
		else {
			
			$this->output_xml($step);			
			xml_output::success();
		}
      
   }

	function output_xml($step) {
		/* @var step CerNextStep */

		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		
		$ticket_step =& $data->add_child("ticket_step", xml_object::create("ticket_step", NULL, array("id"=>$step->getId())));
	}   
   
/* expects this format for input:
<?xml version="1.0" encoding="UTF-8"?>
<cerberus_xml>
<channel>email</channel>
<module>workflow</module>
<command>add_ticket_step</command>
<data>
<ticket_step id="0">
<note>Phone It.</note>
<created_by_agent id="2">Bob</created_by_agent>
<ticket id="14"/>
</ticket_step>
</data></cerberus_xml>

*/
}