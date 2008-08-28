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
//require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 *
 */
class assign_mass_workflow_handler extends xml_parser
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
   function assign_mass_workflow_handler(&$xml) {
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

		$tickets_elm =& $this->xml->get_child('tickets', 0);
		$ticket_elms =& $tickets_elm->get_children("ticket");
		$tickets = array();
		if(is_array($ticket_elms)) {
			foreach($ticket_elms AS $ticket_elm) {
				$tickets[] = $ticket_elm->get_attribute('id', FALSE);
			}
		}


		$workflow_elm =& $this->xml->get_child('workflow', 0);
		

		$tags_elm =& $workflow_elm->get_child('tags', 0);
		$tag_elms =& $tags_elm->get_children("tag");
		$tags = array();
		if(is_array($tag_elms)) {
			foreach($tag_elms as $key=>$tag_elm) {
				$tags[] = $tag_elm->get_attribute('id', FALSE);
			}
		}

		$teams_elm =& $workflow_elm->get_child('teams', 0);
		$team_elms =& $teams_elm->get_children("team");
		$teams = array();
		if(is_array($team_elms)) {
			foreach($team_elms as $key=>$team_elm) {
				$teams[] = $team_elm->get_attribute('id', FALSE);
			}
		}
		
		$spotlights_elm =& $workflow_elm->get_child('spotlights', 0);
		$spotlight_elms =& $spotlights_elm->get_children("spotlight");
		$agents = array();
		if(is_array($spotlight_elms)) {
			foreach($spotlight_elms as $key=>$spotlight_elm) {
				$agents[] = $spotlight_elm->get_attribute('id', FALSE);
			}
		}		
		//print_r($agents);exit();
		
		$wsTickets = new CerWorkstationTickets();
		
		foreach($tags AS $tag_id) {
			$wsTickets->addTagTickets($tag_id, $tickets);
		}
//		foreach($teams AS $team_id) {
//			$wsTickets->addTeamTickets($team_id, $tickets);
//		}
		foreach($agents AS $agent_id) {
			$wsTickets->addAgentTickets($agent_id, $tickets);
		}
		
		xml_output::success();
		
		
//		if($tag_success === FALSE || $team_success === FALSE) {
//			$message = "";
//			if($tag_success === FALSE)
//				$message .= 'Failed adding tag to ticket. ';
//			if($team_success === FALSE)
//				$message .= 'Failed assigning teams to ticket. ';
//			if($spotlight_success ===FALSE) {
//				$message .= 'Failed adding spotlights to ticket. ';
//			}
//				
//			xml_output::error(0, 'Failed adding tag to ticket.');
//		}
//		else {
//			xml_output::success();
//		}
//      
   }
   
}
