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
|		Jeff Standen    	  (jeff@webgroupmedia.com)		 [JAS]
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");

class email_tickets
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	var $db;

	function email_tickets() {
		$this->db =& database_loader::get_instance();
	}

	function assign_team($ticket_id, $team_id) {
		if(empty($ticket_id) || empty($team_id))
			return FALSE;
			
		return $this->db->Get("ticket", "assign_team", array("ticket_id"=>$ticket_id,"team_id"=>$team_id));
	}
	
	function assign_teams($ticket_id, $team_ids) {
		if(empty($ticket_id) || !is_array($team_ids))
			return FALSE;
			
		foreach ($team_ids AS $team_id) {	
			if(!$this->db->Get("ticket", "assign_team", array("ticket_id"=>$ticket_id,"team_id"=>$team_id)))
				return false;
		}
	}

	function unassign_team($ticket_id, $team_id) {
		if(empty($ticket_id) || empty($team_id))
			return FALSE;
			
		return $this->db->Get("ticket", "unassign_team", array("ticket_id"=>$ticket_id,"team_id"=>$team_id));
	}

	function add_tag($ticket_id, $tag_id) {
		if(empty($ticket_id) || empty($tag_id))
			return FALSE;
			
		return $this->db->Get("ticket", "add_tag", array("ticket_id"=>$ticket_id,"tag_id"=>$tag_id));
	}
	
	function add_tags($ticket_id, $tag_ids) {
		if(empty($ticket_id) || !is_array($tag_ids))
			return FALSE;

		foreach ($tag_ids AS $tag_id) {
			if(!$this->db->Get("ticket", "add_tag", array("ticket_id"=>$ticket_id,"tag_id"=>$tag_id)))
				return false;	
		}

	}
	
	function add_spotlights($ticket_id, $agent_ids) {
		if(empty($ticket_id) || !is_array($agent_ids))
			return FALSE;

		CerWorkstationTickets::addAgentsToTicketId($agent_ids,$ticket_id);
		
		return TRUE;
	}
	
	function delete_spotlight($ticket_id, $agent_id) {
		if(empty($ticket_id) || empty($agent_id))
			return FALSE;

		CerWorkstationTickets::removeAgentsFromTicketId(array($agent_id),$ticket_id);
		
		return TRUE;
	}
	
	function remove_tag($ticket_id, $tag_id) {
		if(empty($ticket_id) || empty($tag_id))
			return FALSE;
			
		return $this->db->Get("ticket", "remove_tag", array("ticket_id"=>$ticket_id,"tag_id"=>$tag_id));
	}
	
	function get_ticket_tags() {
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
		$wsTags = new CerWorkstationTags();

		//print_r($wsTags);
		
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		//$sets = $wsTags->sets;
		
		$tags = $wsTags->getTags();
		
		//$setsElm =& $data->add_child("sets", xml_object::create("sets"));

		$tagsElm =& $data->add_child("tags", xml_object::create("tags"));
		
//		if(is_array($sets)) {
//			foreach ($sets as $setId=>$set) {
//				$setElm =& $setsElm->add_child("set", xml_object::create("set", NULL, array("id"=>$setId)));
//				$setElm->add_child("name", xml_object::create("name", $set->name));
//				$tagsElm =& $setElm->add_child("tags", xml_object::create("tags"));
		foreach ($tags AS $tagId=>$tag) {
			/* @var $tag CerWorkstationTag */
			$tagElm =& $tagsElm->add_child("tag", xml_object::create("tag", NULL, array("id"=>$tagId)));
			$tagElm->add_child("name", xml_object::create("name", $tag->name));
			
			$termsElm =& $tagElm->add_child("terms", xml_object::create("terms"));
			if(is_array($tag->terms)) {
				foreach($tag->terms as $term) {
					$termElm =& $termsElm->add_child("term", xml_object::create("term", $term));
				}
			}					
			
		}
				
				
//			}
//		}

		return TRUE;
	}

	
	function flag_tickets($tickets, $agent_id, $override) {
		/* @var $db database_loader */
   	
		if(empty($tickets) || empty($agent_id) || !is_array($tickets))
			return FALSE;
   		
		$flagged_by_others = $this->db->Get("ticket", "flag_tickets", array("tickets"=>$tickets, "agent_id"=>$agent_id,"override"=>$override));

		return $flagged_by_others;

	}

	function unflag_tickets($tickets, $agent_id) {
		/* @var $db database_loader */

		if(empty($tickets) || empty($agent_id) || !is_array($tickets))
			return FALSE;

		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		return CerWorkstationTickets::removeFlagOnTickets($tickets,$agent_id);   		

		return TRUE;
	}
   
   function set_ticket_priority($ticket_id, $priority) {
   	if(!$this->db->Save("ticket", "set_ticket_priority", array("ticket_id"=>$ticket_id, "priority"=>$priority))) {
   		return FALSE;
   	}
   	
   	return TRUE;
   }
   
   function set_ticket_due($ticket_id, $due) {
   	if(!$this->db->Save("ticket", "set_ticket_due", array("ticket_id"=>$ticket_id, "due"=>$due))) {
   		return FALSE;
   	}
   	
   	return TRUE;
   }
   
	/* 

   */
   function get_ticket_conflict_info($tickets, $agent_id) {
   		$result = $this->db->Get("ticket", "get_ticket_conflict_data", array("tickets"=>$tickets, "agent_id"=>$agent_id));

   		$conflicts = array();
   		$current_ticket_id = "fjiaej3";
   		for($i=0; $i < sizeof($result); $i++) {
			//t.ticket_id, t.ticket_subject, u.user_id, u.user_name
			if($result[$i]['ticket_id'] == $current_ticket_id) {
				$conflict['user_str'] .= ', ' . $results[$i]['user_name'];
			}
			else {
				if($i > 0) {
					$conflicts[] = $conflict;
					unset($conflict);
				}
				$conflict['ticket_id'] = $result[$i]['ticket_id'];
				$conflict['subject'] = $result[$i]['ticket_subject'];
				//$conflict['user_id'] = $result[$i]['ticket_id'];
				$conflict['user_str'] = $result[$i]['user_name'];
			}
			$current_ticket_id = $result[$i]['ticket_id'];
   		}
   		if(!empty($conflict))
   			$conflicts[] = $conflict;
   		return $conflicts;
   }
   
   function report_spam_tickets($tickets, $agent_id) {
   	/* @var $db database_loader */
   	
   	if(empty($tickets) || empty($agent_id) || !is_array($tickets))
   		return FALSE;
   	
		$bayes = new cer_BayesianAntiSpam();
		$bayes->mark_tickets_as_spam($tickets);
   		
	   	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
	   	foreach($tickets as $ticket_id) {
				CerWorkstationTickets::setTicketStatus($ticket_id,"deleted");
	   	}
   	
   	return TRUE;
   }

   function trash_tickets($tickets, $agent_id) {
   	/* @var $db database_loader */
   	
   	if(empty($tickets) || empty($agent_id) || !is_array($tickets))
   		return FALSE;
   	
   	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
   	foreach($tickets as $ticket_id) {
			CerWorkstationTickets::setTicketStatus($ticket_id,"deleted");
   	}
   	
   	
   	return TRUE;
   }
   
   function resolve_tickets($tickets, $agent_id) {
   	/* @var $db database_loader */
   	
   	if(empty($tickets) || empty($agent_id) || !is_array($tickets))
   		return FALSE;
   		
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
   	foreach($tickets as $ticket_id) {
			CerWorkstationTickets::setTicketStatus($ticket_id,"closed");
   	}   	
   	
   	return TRUE;
   }
   
}