<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
|		Mike Fogg		(mike@webgroupmedia.com)		[mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 *
 */
class delete_workflow_item_handler extends xml_parser
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
    * @return ticket_add_tag_handler
    */
   function delete_workflow_item_handler(&$xml) {
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

		/* @var $assign_obj xml_object */
		/* @var $tag_obj xml_object */
		/* @var $ticket_obj xml_object */
		
		$workflow_elm =& $this->xml->get_child('workflow', 0);
		
		$tickets_elm =& $this->xml->get_child('tickets', 0);
		$ticket_elms =& $tickets_elm->get_children('ticket');
		$ticket_ids = array();
		if(is_array($ticket_elms)) {
			foreach($ticket_elms AS $ticket_elm) {
				$ticket_ids[] = $ticket_elm->get_attribute("id", FALSE);
			}
		}
		
		//$ticket_id = $ticket_elm->get_attribute("id", FALSE);

		$item_elm =& $this->xml->get_child('item', 0);
		$item_type = $item_elm->get_attribute("type", FALSE);
		$item_id = $item_elm->get_attribute("id", FALSE);
		
		$email_obj =& new email_tickets();

		$success = TRUE;
		if($item_type == "TAG") {
			foreach($ticket_ids AS $ticket_id) {
				$success = $email_obj->remove_tag($ticket_id, $item_id);
			}
		}
		elseif($item_type == "TEAM") {
			foreach($ticket_ids AS $ticket_id) {
				$success = $email_obj->unassign_team($ticket_id, $item_id);
			}
		}
		elseif($item_type == "SPOTLIGHT") {
			foreach($ticket_ids AS $ticket_id) {
				$success = $email_obj->delete_spotlight($ticket_id, $item_id);
			}
		}
		else {
			$success = FALSE;
		}

		if($success === FALSE) {
			xml_output::error(0, 'Failed deleting workflow item.');
		}
		else {
			xml_output::success();
		}
      
   }
   
}