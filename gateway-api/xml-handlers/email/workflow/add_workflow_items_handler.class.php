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
class add_workflow_items_handler extends xml_parser
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
   function add_workflow_items_handler(&$xml) {
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
		
		$ticket_elm =& $workflow_elm->get_child('ticket', 0);
		$ticket_id = $ticket_elm->get_attribute("id", FALSE);

		$tags_elm =& $workflow_elm->get_child('tags', 0);
		$tags_children = $tags_elm->get_children();
		$tag_ids = array();
		if(is_array($tags_children['tag'])) {
			foreach($tags_children['tag'] as $key=>$tag_elm) {
				$tag_ids[] = $tag_elm->get_attribute('id', FALSE);
			}
		}

		$teams_elm =& $workflow_elm->get_child('teams', 0);
		$teams_children = $teams_elm->get_children();
		$team_ids = array();
		if(is_array($teams_children['team'])) {
			foreach($teams_children['team'] as $key=>$team_elm) {
				$team_ids[] = $team_elm->get_attribute('id', FALSE);
			}
		}
		
		$spotlights_elm =& $workflow_elm->get_child('spotlights', 0);
		$spotlights_children = $spotlights_elm->get_children();
		$agent_ids = array();
		if(is_array($spotlights_children['spotlight'])) {
			foreach($spotlights_children['spotlight'] as $key=>$spotlight_elm) {
				$agent_ids[] = $spotlight_elm->get_attribute('id', FALSE);
			}
		}		
		
		$email_obj =& new email_tickets();
		$tag_success = $email_obj->add_tags($ticket_id, $tag_ids);
		$team_success = $email_obj->assign_teams($ticket_id, $team_ids);
		$spotlight_success = $email_obj->add_spotlights($ticket_id, $agent_ids);
		
		if($tag_success === FALSE || $team_success === FALSE) {
			$message = "";
			if($tag_success === FALSE)
				$message .= 'Failed adding tag to ticket. ';
			if($team_success === FALSE)
				$message .= 'Failed assigning teams to ticket. ';
			if($spotlight_success ===FALSE) {
				$message .= 'Failed adding spotlights to ticket. ';
			}
				
			xml_output::error(0, 'Failed adding tag to ticket.');
		}
		else {
			xml_output::success();
		}
      
   }
   
}