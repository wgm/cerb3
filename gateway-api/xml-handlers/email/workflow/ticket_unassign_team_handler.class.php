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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
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
class ticket_unassign_team_handler extends xml_parser
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
    * @return ticket_unassign_team_handler
    */
   function ticket_unassign_team_handler(&$xml) {
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
		/* @var $team_obj xml_object */
		/* @var $ticket_obj xml_object */
		
		$assign_obj =& $this->xml->get_child('assign', 0);
		$team_obj =& $assign_obj->get_child('team', 0);
		$ticket_obj =& $assign_obj->get_child('ticket', 0);
		
		$ticket_id = $ticket_obj->get_attribute("id", FALSE);
		$team_id = $team_obj->get_attribute("id", FALSE);
		
      $email_obj =& new email_tickets();
		
		if($email_obj->unassign_team($ticket_id, $team_id) === FALSE) {
			xml_output::error(0, 'Failed unassigning team from ticket.');
		}
		else {
			xml_output::success();
		}
      
   }
   
}