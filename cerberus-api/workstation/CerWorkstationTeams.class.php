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
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

class CerWorkstationTeams {
	
	var $teams = array();
	var $queues = array();
	var $total_hits = 0;
	
	/**
	* @return CerWorkstationTeams
	* @desc 
	*/
	function CerWorkstationTeams() {
		$this->_loadTeams();
	}

	/**
	 * Enter description here...
	 *
	 * @return CerWorkstationTeams
	 */
	function getInstance() {
		static $instance = null;
		
		if(null == $instance) {
			$instance = new CerWorkstationTeams();
		}
		
		return $instance;
	}
	
	function getTeamsWithQuickAssignLoads($agent_id,$restrict_qids) {
		$copy = $this->teams;
		foreach($copy as $idx => $t) { // reset
			$copy[$idx]->workload_hits = 0;
		}
		$this->total_hits = 0;
		
		$this->_addQuickAssignCountToTeams($copy,false,$agent_id,$restrict_qids);
		return $copy;
	}
	
	function getTeamsWithRelativeLoads($agent_id,$restrict_qids=array()) {
		$copy = $this->teams;
		foreach($copy as $idx => $t) { // reset
			$copy[$idx]->workload_hits = 0;
		}
		$this->total_hits = 0;
		
		$this->_addWorkloadCountToTeams($copy,false,$agent_id,$restrict_qids);
		return $copy;
	}
	
	function _loadTeams() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "SELECT `team_id`, `team_name`, `team_acl1`, `team_acl2`, `team_acl3` FROM `team` ORDER BY `team_name`";
		$res = $db->query($sql);
		
		if(!$db->num_rows($res))
			return;
			
		while($row = $db->fetch_row($res)) {
			$team = new stdClass();
			$team->name = stripslashes($row['team_name']);
			$team->workload_hits = 0;
			$team->acl1 = intval($row['team_acl1']);
			$team->acl2 = intval($row['team_acl2']);
			$team->acl3 = intval($row['team_acl3']);
			$team->members = array();
			$team->queues = array();
			$team->tagsets = array();
			$team->quick_assign = array();
			$this->teams[$row['team_id']] = $team;
		}
		
		// [JAS]: Members
		$sql = "SELECT m.`member_id`, m.`team_id`, m.`agent_id`, m.`is_watcher`, u.`user_name`, u.`user_email` ".
			"FROM `team_members` m ".
			"INNER JOIN `user` u ON (m.agent_id=u.user_id) "
			;
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$team_id = intval($row['team_id']);
				$agent_id = intval($row['agent_id']);
				$member_id = intval($row['member_id']);
				$user_name = stripslashes($row['user_name']);
				$user_email = stripslashes($row['user_email']);
				$is_watcher = intval($row['is_watcher']);
				
				if(!isset($this->teams[$team_id]))
					continue;

				$team =& $this->teams[$team_id];
				
				$member = new stdClass();
				$member->member_id = $member_id;
				$member->agent_id = $agent_id;
				$member->team_id = $team_id;
				$member->user_name = $user_name;
				$member->user_email = $user_email;
				$member->is_watcher = $is_watcher;
				
				$team->members[$member_id] = $member;
				$team->agents[$agent_id] =& $team->members[$member_id];
			}
		}
		
		// [JAS]: Queues
		$sql = "SELECT team_id, queue_id, quick_assign FROM `team_queues`";
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$queue_id = intval($row['queue_id']);
				$team_id = intval($row['team_id']);
				$quick_assign = intval($row['quick_assign']);
				
				if(!isset($this->teams[$team_id]))
					continue;
					
				$team =& $this->teams[$team_id];
				$team->queues[$queue_id] = $queue_id;
				
				// [JAS]: Queues to Teams Index
				if(!isset($this->queues[$queue_id]))
					$this->queues[$queue_id] = array();
				
				// [JAS]: Team Quick Assign Index
				if(!empty($quick_assign))
					$team->quick_assign[$queue_id] =& $this->queues[$queue_id];	
				
				// [JAS]: Reverse Queues to Teams index
				$this->queues[$queue_id][$team_id] = $team_id;
			}
		}
		
		// [JAS]: Tag Sets
		$sql = "SELECT `set_id`, `team_id` FROM `workstation_tag_sets_to_teams`";
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$set_id = intval($row['set_id']);
				$team_id = intval($row['team_id']);
				
				if(!isset($this->teams[$team_id]))
					continue;
					
				$team =& $this->teams[$team_id];
				$team->tagsets[$set_id] = $set_id;
			}
		}
		
		$this->total_hits = 0;
		$this->_addWorkloadCountToTeams($this->teams);
		
		return true;
	}
	
	function _addWorkloadCountToTeams(&$teams,$include_flagged=false,$agent_id=0,$restrict_qids=array()) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "SELECT COUNT(*) as hits, t.ticket_queue_id ".
			"FROM `ticket` t ".
			((!empty($agent_id)) ? sprintf("LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ",$agent_id) : " ").
			"WHERE t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
			((!empty($agent_id)) ? "AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp < UNIX_TIMESTAMP()) " : " ").
			((!empty($restrict_qids) && is_array($restrict_qids)) ? sprintf("AND t.ticket_queue_id IN (%s) ",implode(',',$restrict_qids)) : " ").
			"GROUP BY t.ticket_queue_id";
		$res = $db->query($sql);

		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$queue_id = intval($row['ticket_queue_id']);
				$hits = intval($row['hits']);

				// [JAS]: Loop through each queue and increment responsible team counters
				if(is_array($this->queues) && isset($this->queues[$queue_id]))
				foreach($this->queues[$queue_id] as $team_id) {
					if(isset($teams[$team_id])) {
						$teams[$team_id]->workload_hits += $hits;
						$this->total_hits += $hits;
					}
				}
			}
		}
	}

	function _addQuickAssignCountToTeams(&$teams,$include_flagged=false,$agent_id=0,$restrict_qids=array()) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "SELECT COUNT(*) as hits, t.ticket_queue_id ".
			"FROM `ticket` t ".
			((!empty($agent_id)) ? sprintf("LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ",$agent_id) : " ").
			((!$include_flagged) ? "LEFT JOIN ticket_flags_to_agents tfa ON ( tfa.ticket_id = t.ticket_id ) " : " ").
			"WHERE t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
			((!empty($agent_id)) ? "AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp < UNIX_TIMESTAMP()) " : " ").
			((!$include_flagged) ? "AND (tfa.agent_id IS NULL) " : " ").
			((!empty($restrict_qids) && is_array($restrict_qids)) ? sprintf("AND t.ticket_queue_id IN (%s) ",implode(',',$restrict_qids)) : " ").
			"GROUP BY t.ticket_queue_id";
		$res = $db->query($sql);

		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$queue_id = intval($row['ticket_queue_id']);
				$hits = intval($row['hits']);

				// [JAS]: Loop through each queue and increment responsible team counters
				if(is_array($this->queues) && isset($this->queues[$queue_id]))
				foreach($this->queues[$queue_id] as $team_id) {
					if(isset($teams[$team_id]) && isset($teams[$team_id]->quick_assign[$queue_id])) {
						$teams[$team_id]->workload_hits += $hits;
						$this->total_hits += $hits;
					}
				}
			}
		}
	}
	
	function deleteTeam($team_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("DELETE FROM `team` WHERE `team_id` = %d",
			$team_id
		);
		$db->query($sql);
		
		$sql = sprintf("DELETE FROM `team_members` WHERE `team_id` = %d",
			$team_id
		);
		$db->query($sql);
		
//		$sql = sprintf("DELETE FROM `workstation_routing` WHERE `team_id` = %d",
//			$team_id
//		);
//		$db->query($sql);

//		$sql = sprintf("DELETE FROM `workstation_routing_to_tickets` WHERE `team_id` = %d",
//			$team_id
//		);
//		$db->query($sql);
		
		$sql = sprintf("DELETE FROM `team_queues` WHERE `team_id` = %d",
			$team_id
		);
		$db->query($sql);
		
//		$sql = sprintf("DELETE FROM `team_tag_sets` WHERE `team_id` = %d",
//			$team_id
//		);
//		$db->query($sql);

//		// [DDH]: remove teams from support center profiles
//		include_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicGUISettings.class.php");
//		CerPublicGuiHandler::removeTeam($team_id);
		
		// [JAS]: Reload the team cache
		$this->_loadTeams();
	}
	
	function saveTeam($team_id, $team_name, $acl1, $acl2, $acl3, $team_members, $team_queues, $team_sets, $quick_assign=array()) {
		if(!is_array($quick_assign)) $quick_assign = array($quick_assign);

		// [JAS]: Reload the team cache
		$this->_loadTeams();
		
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($team_members))
			$team_members = array();
			
		// [JAS]: Team info
		$sql = sprintf("UPDATE `team` SET `team_name` = %s,`team_acl1`=%d,`team_acl2`=%d,`team_acl3`=%d WHERE `team_id` = %d",
			$db->escape($team_name),
			$acl1,
			$acl2,
			$acl3,
			$team_id
		);
		$db->query($sql);
		
		// [JAS]: Team members
		if(is_array($team_members))
		foreach($team_members as $agent_id) {
			// new member
			if(!isset($this->teams[$team_id]->agents[$agent_id])) {
				$sql = sprintf("INSERT INTO `team_members` (`agent_id`,`team_id`) ".
					"VALUES ('%d','%d')",
						$agent_id,
						$team_id
				);
				$db->query($sql);
			}
		}
		
		// nuke any removed agents from the team members
		$mem_ids = array_values($team_members);
		CerSecurityUtils::integerArray($mem_ids);
		$agent_ids = implode(',', $mem_ids);
		if(!empty($agent_ids)) {
			// select members
			$sql = sprintf("DELETE FROM `team_members` WHERE `team_id` = '%d' AND `agent_id` NOT IN (%s)",
				$team_id,
				$agent_ids
			);
			$db->query($sql);
		} else {
			// all members
			$sql = sprintf("DELETE FROM `team_members` WHERE `team_id` = '%d'",
				$team_id
			);
			$db->query($sql);
		}

		// [JAS]: Team queues
		if(is_array($team_queues))
		foreach($team_queues as $queue_id) {
			// new queue
			if(!isset($this->teams[$team_id]->queues[$queue_id])) {
				$sql = sprintf("INSERT INTO `team_queues` (`queue_id`,`team_id`) ".
					"VALUES ('%d','%d')",
						$queue_id,
						$team_id
				);
				$db->query($sql);
			}
		}
		
		// [JAS]: Quick assign
		if(is_array($team_queues)) {
			foreach($team_queues as $queue_id) {
				$pos = array_search($queue_id,$quick_assign);
				$quick = (!is_null($pos) && $pos !== false) ? 1 : 0;
				$sql = sprintf("UPDATE `team_queues` SET `quick_assign` = %d WHERE `team_id` = %d AND `queue_id` = %d",
					$quick,
					$team_id,
					$queue_id
				);
				$db->query($sql);
			}
		}
		
		// clear unchecked queues
		$queue_ids = implode(',', array_values($team_queues));
		if(!empty($queue_ids)) {
			$sql = sprintf("DELETE FROM `team_queues` WHERE `team_id` = '%d' AND `queue_id` NOT IN (%s)",
				$team_id,
				$queue_ids
			);
			$db->query($sql);
		} else {
			$sql = sprintf("DELETE FROM `team_queues` WHERE `team_id` = '%d'",
				$team_id
			);
			$db->query($sql);
		}
		
		// [JAS]: Team tag sets
		
		$sql = sprintf("DELETE FROM `workstation_tag_sets_to_teams` WHERE `team_id` = %d",
			$team_id
		);
		$db->query($sql);
		
		if(!empty($team_sets) && is_array($team_sets)) {
			foreach($team_sets as $setId) {
				$db->query(sprintf("REPLACE INTO `workstation_tag_sets_to_teams` (`set_id`,`team_id`) VALUES (%d,%d)",
					$setId,
					$team_id
				));
			}
		}
		
	}
	
	function addTeam($name) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("INSERT INTO `team` (`team_name`) VALUES (%s)",
			$db->escape($name)
		);
		$db->query($sql);
		
		return $db->insert_id();
	}
	
	function getTeams() {
		if(is_array($this->teams))
			return $this->teams;
		else 
			return array();
	}
	
	function updateWatcher($agent_id,$teamsIds) {
		if(empty($agent_id))
			return FALSE;

		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
			
		$sql = sprintf("UPDATE `team_members` SET `is_watcher` = 0 WHERE `agent_id` = %d",
			$agent_id
		);
		$db->query($sql);

		if(is_array($teamsIds)) {
			$sql = sprintf("UPDATE `team_members` SET `is_watcher` = 1 WHERE `agent_id` = %d AND `team_id` IN (%s)",
				$agent_id,
				implode(',', $teamsIds)
			);
			$db->query($sql);
		}
		
		$this->_loadTeams();
	}
	
}