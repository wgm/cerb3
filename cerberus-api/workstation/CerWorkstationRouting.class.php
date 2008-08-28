<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");

class CerWorkstationRouting {
	
	var $queues = array();
//	var $tags = array();
//	var $flagged_agents = array();
//	var $suggested_agents = array();
	
	/**
	* @return CerWorkstationRouting
	* @desc 
	*/
	function CerWorkstationRouting() {
		$this->_loadRouting();
	}

	function _loadRouting() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		// Tags
		$sql = "SELECT `tag_id`, `queue_id` FROM `workstation_routing_tags`";
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$tag_id = $row['tag_id'];
			$queue_id = $row['queue_id'];
			
			if(!isset($this->queues[$queue_id])) {
				$this->queues[$queue_id] = new stdClass();
				$this->queues[$queue_id]->tags = array();
				$this->queues[$queue_id]->flagged_agents = array();
				$this->queues[$queue_id]->suggested_agents = array();
			}
				
			$this->queues[$queue_id]->tags[$tag_id] = $tag_id;
		}

		// Agents
		$sql = "SELECT `agent_id`, `queue_id`, `is_flagged` FROM `workstation_routing_agents`";
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$agent_id = $row['agent_id'];
			$queue_id = $row['queue_id'];
			
			if(!isset($this->queues[$queue_id])) {
				$this->queues[$queue_id] = new stdClass();
				$this->queues[$queue_id]->tags = array();
				$this->queues[$queue_id]->flagged_agents = array();
				$this->queues[$queue_id]->suggested_agents = array();
			}
			
			if($row['is_flagged']) { // flagged
				$this->queues[$queue_id]->flagged_agents[$agent_id] = $agent_id;
			} else { // suggested
				$this->queues[$queue_id]->suggested_agents[$agent_id] = $agent_id;
			}
		}
		
		return true;
	}
	
	function queueHasTag($queue_id, $tag_id) {
		if(empty($queue_id) || empty($tag_id) || !isset($this->queues[$queue_id]))
			return false;
		
		foreach($this->queues[$queue_id]->tags as $q_tag_id) {
			if($tag_id == $q_tag_id)
				return true;
		}
		
		return false;
	}
	
	function queueHasAgent($queue_id, $agent_id, $is_flagged=0) {
		if(empty($queue_id) || empty($agent_id) || !isset($this->queues[$queue_id]))
			return false;
		
		if($is_flagged) { // assigned
			if(isset($this->queues[$queue_id]->flagged_agents[$agent_id]))
				return true;
		} else { // suggested
			if(isset($this->queues[$queue_id]->suggested_agents[$agent_id]))
				return true;
		}
		
		return false;
	}
	
	function saveQueueTags($queue_id, $queue_tags) {
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
		
		$wstags = new CerWorkstationTags();
		
		if(empty($queue_id))
			return;
		
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		// clear old tags
		$sql = sprintf("DELETE FROM `workstation_routing_tags` WHERE `queue_id` = '%d' ",
			$queue_id
		);
		$db->query($sql);

		if(empty($queue_tags)) //  || !is_array($queue_tags)
			return;

		$wstags->applyMailboxTags($queue_tags, $queue_id);
	}
	
	function saveQueueAgents($queue_id, $queue_agents, $is_flagged=0) {
		if(empty($queue_id))
			return;
		
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		// clear old tags
		$sql = sprintf("DELETE FROM `workstation_routing_agents` WHERE `queue_id` = '%d' AND `is_flagged` = %d ",
			$queue_id,
			($is_flagged) ? 1 : 0
		);
		$db->query($sql);

		if(empty($queue_agents) || !is_array($queue_agents))
			return;
		
		foreach($queue_agents as $agent_id) {
			$sql = sprintf("INSERT INTO `workstation_routing_agents` (`queue_id`, `agent_id`, `is_flagged`) ".
				"VALUES ('%d','%d',%d)",
					$queue_id,
					$agent_id,
					($is_flagged) ? 1 : 0
			);
			$db->query($sql);
		}
	}
	
	function routeTicket($ticket_id, $queue_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		if(!isset($this->queues[$queue_id]))
			return;

		$queue =& $this->queues[$queue_id];
			
		// [JAS]: Tags
		if(is_array($queue->tags))
		foreach($queue->tags as $tag_id => $tag) {
			$this->addTagToTicket($tag_id, $ticket_id);
		}
		
		// [JAS]: Assign Agents
		if(is_array($queue->flagged_agents))
		foreach($queue->flagged_agents as $agent_id => $agent) {
			$this->addAgentToTicket($agent_id, $ticket_id, 1);
		}
		
		// [JAS]: Suggest Agents
		if(is_array($queue->suggested_agents))
		foreach($queue->suggested_agents as $agent_id => $agent) {
			$this->addAgentToTicket($agent_id, $ticket_id);
		}
		
 	}
	
 	function addTagToTicket($tag_id, $ticket_id) {
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::addTagsToTicketId(array($tag_id),$ticket_id);
 	}
 	
 	function addAgentToTicket($agent_id, $ticket_id, $is_flagged=0) {
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		
		if($is_flagged) {
			CerWorkstationTickets::addFlagToTicket($agent_id,$ticket_id);
		} else {
			CerWorkstationTickets::addAgentsToTicketId(array($agent_id),$ticket_id);
		}
 	}
 	
	function importRouting() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		foreach($this->queues as $queue_id => $queue) {
			// [JAS]: Tags
			if(is_array($queue->tags))
			foreach($queue->tags as $tag_id => $tag) {
				$sql = sprintf("INSERT INTO `workstation_tags_to_tickets` (`ticket_id`, `tag_id`) ".
					"SELECT t.ticket_id, '%d' FROM `ticket` t WHERE t.`ticket_queue_id` = '%d'",
						$tag_id,
						$queue_id
				);
				$db->query($sql);
			}
			
			
		}
	}
}