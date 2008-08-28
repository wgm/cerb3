<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2006, WebGroup Media LLC
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
require_once(FILESYSTEM_PATH . "cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "cerberus-api/notification/CerNotification.class.php");
include_once(FILESYSTEM_PATH . "cerberus-api/compatibility/compatibility.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");

/**
 * Functionality for working with tickets
 * 
 * @author Jeff Standen <jeff@webgroupmedia.com>
 * @copyright (c) 2006 WebGroup Media, LLC.
 *
 */
class CerWorkstationTickets {
	
	/**
	* @return CerWorkstationTickets
	* @desc 
	*/
	function CerWorkstationTickets() {
	}

	function getTicketsByIds($ids,$with_content=true,$preserve_order=false) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$tickets = array();
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		
		$sql = sprintf("SELECT t.ticket_id, t.min_thread_id, t.max_thread_id, t.opened_by_address_id, ad.public_user_id, ad.address_address as opened_by_address, t.ticket_mask, t.ticket_subject, t.is_closed, t.is_deleted, t.is_waiting_on_customer, t.ticket_priority, ".
			"th2.thread_date as latest_date, t.max_thread_id, t.ticket_status_id, ts.ticket_status_text, a.address_address as latest_reply, q.queue_id, q.queue_name, q.queue_prefix, q.queue_email_display_name, q.queue_reply_to, ".
			"t.ticket_spam_trained, t.ticket_spam_probability, t.ticket_due, FROM_UNIXTIME(dd.expire_timestamp) as delay_timestamp, th2.thread_message_id ".
			"FROM ticket t ".
			"LEFT JOIN ticket_status ts ON (ts.ticket_status_id = t.ticket_status_id) ".
			"INNER JOIN address ad ON (ad.address_id = t.opened_by_address_id) ".
			"INNER JOIN queue q ON (q.queue_id = t.ticket_queue_id) ".
			"INNER JOIN thread th2 ON (th2.thread_id = t.max_thread_id) ".
			"INNER JOIN address a ON (th2.thread_address_id=a.address_id) ".
			"LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ".
			"WHERE t.ticket_id IN (%s) ",
			(!empty($user_id) ? $user_id : 0),
			implode(',', $ids)
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$ticket = new CerWorkstationTicket();
				$ticket->id = intval($row['ticket_id']);
				$ticket->min_thread_id = intval($row['min_thread_id']);
				$ticket->max_thread_id = intval($row['max_thread_id']);
				$ticket->mask = (!empty($row['ticket_mask'])) ? $row['ticket_mask'] : $ticket->id;
				$ticket->subject = stripslashes($row['ticket_subject']);
				$ticket->last_message_id = stripslashes($row['thread_message_id']);
				$ticket->opened_by_address_id = intval($row['opened_by_address_id']);
				$ticket->public_user_id = intval($row['public_user_id']);
				$ticket->opened_by_address = stripslashes($row['opened_by_address']);
				$ticket->queue_id = stripslashes($row['queue_id']);
				$ticket->queue_name = stripslashes($row['queue_name']);
				$ticket->queue_prefix = stripslashes($row['queue_prefix']);
				$ticket->queue_display_name = stripslashes($row['queue_email_display_name']);
				$ticket->queue_reply_to = stripslashes($row['queue_reply_to']);
				$ticket->is_deleted = $row['is_deleted'];
				$ticket->is_closed = $row['is_closed'];
				$ticket->ticket_status_id = $row['ticket_status_id'];
				$ticket->ticket_status_text = $row['ticket_status_text'];
				$ticket->priority = intval($row['ticket_priority']);
				$ticket->is_waiting_on_customer = $row['is_waiting_on_customer'];
				$ticket->date_latest_reply = $row['latest_date'];
				$ticket->address_latest_reply = $row['latest_reply'];
				$ticket->spam_trained = $row['ticket_spam_trained'];
				$ticket->spam_probability = $row['ticket_spam_probability'];
				$ticket->date_due = new cer_DateTime($row['ticket_due']);
				$ticket->date_delay = new cer_DateTime($row['delay_timestamp']);
				$tickets[$ticket->id] = $ticket;
			}

			CerWorkstationTickets::_addFlagsToTicketHeaders($tickets);
			CerWorkstationTickets::_addTagsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addTeamsToTicketHeaders($tickets);
			CerWorkstationTickets::_addAgentsToTicketHeaders($tickets);
		}
		
		if($preserve_order) {
			$tmp_tickets = array();
			
			foreach($ids as $idx=>$id) {
				if(!isset($tickets[$id]))
					continue;
				$tmp_tickets[$id] = $tickets[$id];
			}
			
			$tickets = $tmp_tickets;
			unset($tmp_tickets);
		}
		
		return $tickets;
	}
	
	function getAgentCounts($agent_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$counts = array('flagged'=>0, 'suggested'=>0);
		
		$sql = sprintf("SELECT count(*) as hits FROM ticket t ".
			"INNER JOIN ticket_flags_to_agents fta ON (fta.ticket_id=t.ticket_id) ".
			"WHERE t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 AND fta.agent_id = %d ",
				$agent_id
		);
		$res = $db->query($sql);
		if($row = $db->grab_first_row($res)) {
			$counts['flagged'] = $row['hits'];
		}
		
		$sql = sprintf("SELECT count(*) as hits FROM ticket t ".
			"INNER JOIN ticket_spotlights_to_agents sta ON (sta.ticket_id=t.ticket_id) ".
			"LEFT JOIN ticket_flags_to_agents fta ON (fta.ticket_id=t.ticket_id) ".
			"WHERE fta.agent_id IS NULL AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 AND sta.agent_id = %d ",
				$agent_id
		);
		$res = $db->query($sql);
		if($row = $db->grab_first_row($res)) {
			$counts['suggested'] = $row['hits'];
		}
		
		return $counts;
	}
	
	function quickAssignToAgent($teams,$agent_id,$limit=5) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$left = $limit;
		$queues = array();
		
		if(!is_array($teams) || empty($teams) || empty($agent_id))
			return FALSE;
			
		// [JAS]: Aggregate the teams into a list of queues and use a IN (...) queue search
		if(is_array($teams)) {
			$sql = sprintf("SELECT queue_id FROM `team_queues` WHERE team_id IN (%s) AND quick_assign = 1",
				implode(',', $teams)
			);
			$res = $db->query($sql);
			
			if($db->num_rows($res))
			while($row = $db->fetch_row($res)) {
				$queues[$row['queue_id']] = $row['queue_id'];
			}
		}
			
		/*
		 * [JAS]: Assign any suggested work from these teams first, ignoring delayed.
		 */
		$sql = sprintf("SELECT t.ticket_id, fta.agent_id as flag_agent ".
			"FROM ticket t ".
			"INNER JOIN ticket_spotlights_to_agents sta USING (ticket_id) ".
			"LEFT JOIN ticket_flags_to_agents fta ON ( fta.ticket_id = t.ticket_id ) ".
			"LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ".
			"WHERE t.ticket_queue_id IN (%s) ".
			"AND sta.agent_id = %d ".
			"AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
			"AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp < UNIX_TIMESTAMP()) ".
			"GROUP BY t.ticket_id ".
			"HAVING flag_agent IS NULL ".
			"ORDER BY t.ticket_priority DESC, t.ticket_due ASC ".
			"LIMIT 0,%d",
				$agent_id,
				implode(',', $queues),
				$agent_id,
				$limit
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$ticket_id = intval($row['ticket_id']);
				CerWorkstationTickets::addFlagToTicket($agent_id,$ticket_id);
				$left--;
			}
		}

		
		/*
		 * [JAS]: If we still have more room in our limit, assign from our unassigned team pools
		 * also ignoring delayed tickets.
		 */
		if($left) {
			$sql = sprintf("SELECT t.ticket_id, fta.agent_id as flag_agent ".
				"FROM ticket t ".
				"LEFT JOIN ticket_flags_to_agents fta ON ( fta.ticket_id = t.ticket_id ) ".
				"LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ".
				"WHERE t.ticket_queue_id IN (%s) ".
				"AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
				"AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp < UNIX_TIMESTAMP()) ".
				"GROUP BY t.ticket_id ".
				"HAVING flag_agent IS NULL ".
				"ORDER BY t.ticket_priority DESC, t.ticket_due ASC ".
				"LIMIT 0,%d",
					$agent_id,
					implode(',', $queues),
					$left
			);
			$res = $db->query($sql);
			
			if($db->num_rows($res)) {
				while($row = $db->fetch_row($res)) {
					$ticket_id = intval($row['ticket_id']);
					CerWorkstationTickets::addFlagToTicket($agent_id,$ticket_id);
				}
			}
		}
		
		return TRUE;
	}
	
//	function getSuggestionsByTeams($teams,$tags,$agent_id,$limit=5,$orderCol="",$show_flagged=0) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//		
//		if(!is_array($teams) || !is_array($tags))
//			return FALSE;
//		
//		switch($orderCol) {
//			case "due":
//				$order_by = "t.ticket_due DESC";
//				break;
//			case "latest":
//				$order_by = "th2.thread_date DESC";
//				break;
//			default:
//				$order_by = "t.ticket_priority DESC";
//				break;
//		}
//			
//		$sql = sprintf("SELECT t.ticket_id, t.ticket_mask, t.ticket_subject, t.ticket_priority, t.is_closed, t.is_deleted, t.is_waiting_on_customer, ".
//		"th2.thread_date as latest_date, t.max_thread_id, a.address_address as latest_reply, fta.agent_id as flag_agent, ".
//		"dd.expire_timestamp,t.ticket_spam_trained, t.ticket_spam_probability, t.ticket_due ".
//			"FROM ticket t ".
//			"INNER JOIN workstation_routing_to_tickets wrt USING (ticket_id) ".
//			(!empty($tags) ? "INNER JOIN workstation_tags_to_tickets wtt ON (wtt.ticket_id=t.ticket_id) " : " ").
//			"INNER JOIN thread th USING (ticket_id) ".
//			"INNER JOIN thread th2 USING (ticket_id) ".
//			"INNER JOIN address a ON (th2.thread_address_id=a.address_id) ".
//			"LEFT JOIN ticket_flags_to_agents fta ON ( fta.ticket_id = t.ticket_id ) ".
//			"LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ".
//			"WHERE wrt.team_id IN (%s) ".
//			(!empty($tags) ? sprintf("AND wtt.tag_id IN (%s) ",implode(',',$tags)) : "").
//			"AND th2.thread_id = t.max_thread_id ".
//			"AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
//			"AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp < UNIX_TIMESTAMP()) ".
//			"GROUP BY t.ticket_id ".
//			((!$show_flagged) ? "HAVING flag_agent IS NULL " : "") .
//			"ORDER BY %s ".
//			"LIMIT 0,%d",
//				$agent_id,
//				implode(',', $teams),
//				$order_by,
//				$limit
//		);
//		$res = $db->query($sql);
//		
//		if($db->num_rows($res)) {
//			while($row = $db->fetch_row($res)) {
//				$ticket = new CerWorkstationTicket();
//				$ticket->id = intval($row['ticket_id']);
//				$ticket->mask = (!empty($row['ticket_mask'])) ? $row['ticket_mask'] : $ticket->id;
//				$ticket->subject = stripslashes($row['ticket_subject']);
//				$ticket->priority = intval($row['ticket_priority']);
//				$ticket->is_deleted = $row['is_deleted'];
//				$ticket->is_closed = $row['is_closed'];
//				$ticket->is_waiting_on_customer = $row['is_waiting_on_customer'];
//				$ticket->date_latest_reply = $row['latest_date'];
//				$ticket->address_latest_reply = $row['latest_reply'];
//				$ticket->spam_trained = $row['ticket_spam_trained'];
//				$ticket->spam_probability = $row['ticket_spam_probability'];
//				$ticket->date_due = new cer_DateTime($row['ticket_due']);
//				$tickets[$ticket->id] = $ticket;
//			}
//
//			CerWorkstationTickets::_addFlagsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addTagsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addTeamsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addAgentsToTicketHeaders($tickets);
//		}
//
//		return $tickets;		
//	}
	
	function getMyTickets($agent_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("SELECT t.ticket_id, t.ticket_mask, t.ticket_subject, t.ticket_priority, t.is_closed, t.is_deleted, t.is_waiting_on_customer, ".
		"th2.thread_date as latest_date, t.max_thread_id, a.address_address as latest_reply, ".
		"dd.expire_timestamp,t.ticket_spam_trained, t.ticket_spam_probability, t.ticket_due ".
			"FROM ticket t ".
			"INNER JOIN thread th USING (ticket_id) ".
			"INNER JOIN thread th2 USING (ticket_id) ".
			"INNER JOIN address a ON (th2.thread_address_id=a.address_id) ".
			"INNER JOIN ticket_flags_to_agents fta ON ( fta.ticket_id = t.ticket_id AND fta.agent_id =%d ) ".
			"LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ".
			"WHERE th2.thread_id = t.max_thread_id ".
			"AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
			"AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp < UNIX_TIMESTAMP()) ".
			"GROUP BY t.ticket_id ".
			"ORDER BY t.ticket_priority DESC ",
				$agent_id,
				$agent_id
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$ticket = new CerWorkstationTicket();
				$ticket->id = intval($row['ticket_id']);
				$ticket->mask = (!empty($row['ticket_mask'])) ? $row['ticket_mask'] : $ticket->id;
				$ticket->subject = stripslashes($row['ticket_subject']);
				$ticket->priority = intval($row['ticket_priority']);
				$ticket->is_deleted = $row['is_deleted'];
				$ticket->is_closed = $row['is_closed'];
				$ticket->is_waiting_on_customer = $row['is_waiting_on_customer'];
				$ticket->date_latest_reply = $row['latest_date'];
				$ticket->address_latest_reply = $row['latest_reply'];
				$ticket->spam_trained = $row['ticket_spam_trained'];
				$ticket->spam_probability = $row['ticket_spam_probability'];
				$ticket->date_due = new cer_DateTime($row['ticket_due']);
				$tickets[$ticket->id] = $ticket;
			}

			CerWorkstationTickets::_addFlagsToTicketHeaders($tickets);
			CerWorkstationTickets::_addTagsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addTeamsToTicketHeaders($tickets);
			CerWorkstationTickets::_addAgentsToTicketHeaders($tickets);
		}
		
		return $tickets;		
	}
	
//	function getUnassignedTicketsCount() {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//
//		$sql = sprintf("SELECT t.ticket_id ".
//			"FROM ticket t ".
//			"WHERE t.num_teams = 0 AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
//			"GROUP BY t.ticket_id"
//		);
//		$res = $db->query($sql);
//		
//		return $db->num_rows($res);
//	}
	
//	function getUnassignedTags($include_flagged=false) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//
//		$sql = "SELECT count(*) as hits, wt.tag_id, wt.tag_name ".
//			"FROM workstation_tags_to_tickets wtt ".
//			"INNER JOIN ticket t ON (t.ticket_id=wtt.ticket_id) ".
//			"INNER JOIN workstation_tags wt ON (wt.tag_id=wtt.tag_id) ".
//			"WHERE t.num_teams = 0 ".
//			"AND t.is_deleted = 0 AND t.is_closed = 0 AND t.is_waiting_on_customer = 0 ".
//			"GROUP BY wtt.tag_id HAVING hits > 0 ".
//			"ORDER BY wt.tag_name ASC ";
//		$res = $db->query($sql);
//
//		$tags = array();
//		
//		if($db->num_rows($res)) {
//			while($row = $db->fetch_row($res)) {
//				$hits = intval($row['hits']);
//				$tag_id = intval($row['tag_id']);
//				$tag_name = stripslashes($row['tag_name']);
//				
//				$tags[$tag_id] = new stdClass();
//				$ptr =& $tags[$tag_id];
//				$ptr->name = $tag_name;
//				$ptr->hits = $hits;
//			}
//		}
//
//		return $tags;
//	}
	
//	function getUnassignedTickets($orderCol="",$limit=25,$tags=array()) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//		
//		switch($orderCol) {
//			case "due":
//				$order_by = "t.ticket_due DESC";
//				break;
//			case "latest":
//				$order_by = "th2.thread_date DESC";
//				break;
//			default:
//				$order_by = "t.ticket_priority DESC";
//				break;
//		}
//			
//		$sql = sprintf("SELECT t.ticket_id, t.ticket_mask, t.ticket_subject, t.ticket_priority, t.is_closed, t.is_deleted, t.is_waiting_on_customer, ".
//		"th2.thread_date as latest_date, t.max_thread_id, a.address_address as latest_reply,t.ticket_spam_trained, t.ticket_spam_probability, t.ticket_due ".
//			"FROM ticket t ".
//			(!empty($tags) ? "INNER JOIN workstation_tags_to_tickets wtt ON (wtt.ticket_id=t.ticket_id) " : " ").
//			"INNER JOIN thread th2 ON (th2.thread_id = t.max_thread_id) ".
//			"INNER JOIN address a ON (th2.thread_address_id=a.address_id) ".
//			"WHERE t.num_teams = 0 ".
//			(!empty($tags) ? sprintf("AND wtt.tag_id IN (%s) ",implode(',',$tags)) : "").
//			"AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
//			"GROUP BY t.ticket_id ".
//			"ORDER BY %s ".
//			"LIMIT 0,%d",
//				$order_by,
//				$limit
//		);
//		$res = $db->query($sql);
//		
//		if($db->num_rows($res)) {
//			while($row = $db->fetch_row($res)) {
//				$ticket = new CerWorkstationTicket();
//				$ticket->id = intval($row['ticket_id']);
//				$ticket->mask = (!empty($row['ticket_mask'])) ? $row['ticket_mask'] : $ticket->id;
//				$ticket->subject = stripslashes($row['ticket_subject']);
//				$ticket->priority = intval($row['ticket_priority']);
//				$ticket->is_deleted = $row['is_deleted'];
//				$ticket->is_closed = $row['is_closed'];
//				$ticket->is_waiting_on_customer = $row['is_waiting_on_customer'];
//				$ticket->date_latest_reply = $row['latest_date'];
//				$ticket->address_latest_reply = $row['latest_reply'];
//				$ticket->spam_trained = $row['ticket_spam_trained'];
//				$ticket->spam_probability = $row['ticket_spam_probability'];
//				$ticket->date_due = new cer_DateTime($row['ticket_due']);
//				$tickets[$ticket->id] = $ticket;
//			}
//
//			CerWorkstationTickets::_addFlagsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addTagsToTicketHeaders($tickets);
////			CerWorkstationTickets::_addTeamsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addAgentsToTicketHeaders($tickets);
//		}
//		
//		return $tickets;		
//	}
	
	function getSuggestedTickets($agent_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("SELECT t.ticket_id, t.ticket_mask, t.ticket_subject, t.ticket_priority, t.is_closed, t.is_deleted, t.is_waiting_on_customer, ".
		"th2.thread_date as latest_date, t.max_thread_id, a.address_address as latest_reply, ".
		"dd.expire_timestamp,t.ticket_spam_trained, t.ticket_spam_probability, t.ticket_due ".
			"FROM ticket t ".
			"INNER JOIN ticket_spotlights_to_agents sta USING (ticket_id) ".
			"INNER JOIN thread th USING (ticket_id) ".
			"INNER JOIN thread th2 USING (ticket_id) ".
			"INNER JOIN address a ON (th2.thread_address_id=a.address_id) ".
			"LEFT JOIN dispatcher_delays dd ON ( dd.ticket_id = t.ticket_id AND dd.agent_id =%d ) ".
			"WHERE th2.thread_id = t.max_thread_id ".
			"AND sta.agent_id = %d ".
			"AND t.is_closed = 0 AND t.is_deleted = 0 AND t.is_waiting_on_customer = 0 ".
			"AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp < UNIX_TIMESTAMP()) ".
			"GROUP BY t.ticket_id ".
			"ORDER BY t.ticket_priority DESC ",
				$agent_id,
				$agent_id,
				$agent_id
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$ticket = new CerWorkstationTicket();
				$ticket->id = intval($row['ticket_id']);
				$ticket->mask = (!empty($row['ticket_mask'])) ? $row['ticket_mask'] : $ticket->id;
				$ticket->subject = stripslashes($row['ticket_subject']);
				$ticket->priority = intval($row['ticket_priority']);
				$ticket->is_deleted = $row['is_deleted'];
				$ticket->is_closed = $row['is_closed'];
				$ticket->is_waiting_on_customer = $row['is_waiting_on_customer'];
				$ticket->date_latest_reply = $row['latest_date'];
				$ticket->address_latest_reply = $row['latest_reply'];
				$ticket->spam_trained = $row['ticket_spam_trained'];
				$ticket->spam_probability = $row['ticket_spam_probability'];
				$ticket->date_due = new cer_DateTime($row['ticket_due']);
				$tickets[$ticket->id] = $ticket;
			}

			CerWorkstationTickets::_addFlagsToTicketHeaders($tickets);
			CerWorkstationTickets::_addTagsToTicketHeaders($tickets);
//			CerWorkstationTickets::_addTeamsToTicketHeaders($tickets);
			CerWorkstationTickets::_addAgentsToTicketHeaders($tickets);
		}
		
		return $tickets;		
	}
	
	function getMonitorEvents($user_id,$epoch=0) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$acl = CerACL::getInstance();
		$queues = array_keys($acl->queues);
		
		if(empty($epoch)) $epoch = mktime();
		
		$sql = sprintf("SELECT al.audit_id, al.ticket_id, al.epoch, al.timestamp, al.user_id, al.action, al.action_value, u.user_name as user,t.ticket_subject,t.ticket_mask,t.ticket_priority " .
			"FROM ticket_audit_log al ".
			"LEFT JOIN user u ON (al.user_id = u.user_id) " .
			"LEFT JOIN ticket t ON (al.ticket_id=t.ticket_id) ".
			"WHERE UNIX_TIMESTAMP(al.timestamp) > %d " .
			"AND t.ticket_queue_id IN (%s) ".
			"ORDER BY al.timestamp DESC, al.audit_id DESC ".
			"LIMIT 0,100",
			$epoch,
			implode(',', $queues)
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$ticket = new CerWorkstationTicket();
				$ticket->id = intval($row['ticket_id']);
				$ticket->mask = (!empty($row['ticket_mask'])) ? $row['ticket_mask'] : $ticket->id;
				$ticket->subject = stripslashes($row['ticket_subject']);
				$ticket->priority = intval($row['ticket_priority']);
				$ticket->action_timestamp = $row['timestamp'];
				$ticket->action = CER_AUDIT_LOG::print_action($row['action'],stripslashes($row['action_value']),stripslashes($row['user']),$row['timestamp']);
				$tickets[] = $ticket;
			}
		}
				
		return $tickets;
	}
	
	function _addTagsToTicketHeaders(&$tickets) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($tickets))
			return FALSE;
		
		$sql = sprintf("SELECT t.`ticket_id`, t.`tag_id`, wt.`tag_name` ".
			"FROM `workstation_tags_to_tickets` t ".
			"INNER JOIN `workstation_tags` wt USING (`tag_id`) ".
			"WHERE t.`ticket_id` IN (%s) ".
			"ORDER BY wt.`tag_name` ",
			implode(',', array_keys($tickets))
		);
		$tag_res = $db->query($sql);
		
		if($db->num_rows($tag_res)) {
			while($tag_row = $db->fetch_row($tag_res)) {
				$tickets[$tag_row['ticket_id']]->tags[$tag_row['tag_id']] = 
					array(
						"name"=>stripslashes($tag_row['tag_name'])
					);
			}
		}
		
		return TRUE;
	}
	
	function _addFlagsToTicketHeaders(&$tickets) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($tickets))
			return FALSE;
		
		$sql = sprintf("SELECT t.`ticket_id`, t.`agent_id`, u.`user_name` ".
			"FROM `ticket_flags_to_agents` t ".
			"INNER JOIN `user` u ON (u.`user_id` = t.`agent_id`) ".
			"WHERE t.`ticket_id` IN (%s) ".
			"ORDER BY u.`user_name` ",
			implode(',', array_keys($tickets))
		);
		$flag_res = $db->query($sql);
		
		if($db->num_rows($flag_res)) {
			while($flag_row = $db->fetch_row($flag_res)) {
				$tickets[$flag_row['ticket_id']]->flags[$flag_row['agent_id']] = stripslashes($flag_row['user_name']);
			}
		}
		
		return TRUE;
	}
	
//	function _addTeamsToTicketHeaders(&$tickets) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//		
//		if(!is_array($tickets))
//			return FALSE;
//		
//		$sql = sprintf("SELECT t.`ticket_id`, t.`team_id`, wt.`team_name` ".
//			"FROM `workstation_routing_to_tickets` t ".
//			"INNER JOIN `team` wt USING (`team_id`) ".
//			"WHERE t.`ticket_id` IN (%s) ".
//			"ORDER BY wt.`team_name` ",
//			implode(',', array_keys($tickets))
//		);
//		$team_res = $db->query($sql);
//		if($db->num_rows($team_res)) {
//			while($team_row = $db->fetch_row($team_res)) {
//				$tickets[$team_row['ticket_id']]->teams[$team_row['team_id']] = stripslashes($team_row['team_name']);
//			}
//		}
//		
//		return TRUE;
//	}
	
	function _addAgentsToTicketHeaders(&$tickets) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($tickets))
			return FALSE;
		
		$sql = sprintf("SELECT t.`ticket_id`, t.`agent_id`, u.`user_name` ".
			"FROM `ticket_spotlights_to_agents` t ".
			"INNER JOIN `user` u ON (t.agent_id=u.user_id) ".
			"WHERE t.`ticket_id` IN (%s) ".
			"ORDER BY u.`user_name` ",
			implode(',', array_keys($tickets))
		);
		$agent_res = $db->query($sql);
		
		if($db->num_rows($agent_res)) {
			while($agent_row = $db->fetch_row($agent_res)) {
				$tickets[$agent_row['ticket_id']]->agents[$agent_row['agent_id']] = stripslashes($agent_row['user_name']);
			}
		}
		
		return TRUE;
	}
	
	function _addPreviewToTicketHeaders(&$tickets) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($tickets))
			return FALSE;
		
		$sql = sprintf("SELECT t.ticket_id, tcp.`thread_content_part`, tcp.`thread_id` ".
			"FROM `thread_content_part` tcp ".
			"INNER JOIN `thread` th USING (thread_id) ".
			"INNER JOIN `ticket` t ON (th.thread_id=t.max_thread_id) ".
			"WHERE t.`ticket_id` IN (%s) ".
			"ORDER BY tcp.`content_id` ASC ",
			implode(',', array_keys($tickets))
		);
		$content_res = $db->query($sql);
		
		if($db->num_rows($content_res)) {
			while($content_row = $db->fetch_row($content_res)) {
				if(isset($tickets[$content_row['ticket_id']]))
					$tickets[$content_row['ticket_id']]->preview_text .= stripslashes($content_row['thread_content_part']);
			}
		}
		
		return TRUE;
	}
	
	function getTicketPreviewObject($ticket_id,$thread_id=null) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$threads = array();
		
		$date = "";
		$sender = "";
		$text = "";
		
		$sql = sprintf("SELECT th.thread_id, th.thread_date, a.address_address ".
			"FROM `ticket` t ".
			"INNER JOIN `thread` th ON (th.ticket_id=t.ticket_id) ".
			"INNER JOIN `address` a ON (th.thread_address_id=a.address_id) ".
			"WHERE t.`ticket_id` = %d ".
			"ORDER BY th.thread_id ASC",
				$ticket_id
		);
		$ticket_res = $db->query($sql);
		
		// [TODO] Include comments
		if($row = $db->num_rows($ticket_res)) {
			$last = 0;
			$num = 1;
			while($row = $db->fetch_row($ticket_res)) {
				$i = $row['thread_id'];
				$threads[$i] = array(
					"thread_id"=>$i,
					"date"=>$row['thread_date'],
					"address"=>stripslashes($row['address_address']),
					"next"=>0,
					"prev"=>0,
					"pos"=>$num++
				);
				// [JAS]: Linked list of threads
				if($last) {
					$threads[$i]['prev'] = $last;
					$threads[$last]['next'] = $i;
				}
				$last = $i;
			}
		}
		
		if(empty($thread_id) || !isset($threads[$thread_id])) {
			foreach($threads as $thid => $th) {
				$thread_id = $thid;
			}
		}

		$date = $threads[$thread_id]['date'];
		$sender = $threads[$thread_id]['address'];
		
		$sql = sprintf("SELECT tcp.thread_id, tcp.`thread_content_part`, tcp.`thread_id` ".
			"FROM `thread_content_part` tcp ".
			"WHERE tcp.`thread_id` = %d ".
			"ORDER BY tcp.`content_id` ASC ",
				$thread_id
		);
		$content_res = $db->query($sql);
		
		if($db->num_rows($content_res)) {
			while($content_row = $db->fetch_row($content_res)) {
				$text .= $content_row['thread_content_part'];
			}
		}
		
		// [TODO] This should be handled in a more generic way later
		if(!get_magic_quotes_gpc())
			$text = stripslashes($text);
		
		$preview = new stdClass();
		$preview->sender = $sender;
		$preview->date = $date;
		$preview->text = $text;
		
		return array("preview"=>$preview,"threads"=>$threads,"thread_id"=>$thread_id);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 * @return CerWorkstationThread
	 */
	function getThreadById($id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$thread = new CerWorkstationThread();
		
		$sql = sprintf("SELECT th.thread_id, th.ticket_id, th.thread_message_id ".
			"FROM thread th WHERE th.thread_id = %d",
			$id
		);
		$res = $db->query($sql);
		
		if($row = $db->grab_first_row($res)) {
			$thread->id = $row['thread_id'];
			$thread->ticket_id = $row['ticket_id'];
			$thread->message_id = stripslashes($row['thread_message_id']);
		} else {
			return null;
		}
		
		return $thread;
	}
	
	function getThreadText($thread_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$date = "";
		$sender = "";
		$text = "";
		
		$sql = sprintf("SELECT t.ticket_id, th.thread_id, th.thread_date, a.address_address ".
			"FROM `thread` th ".
			"INNER JOIN `ticket` t  ON (th.ticket_id=t.ticket_id) ".
			"INNER JOIN `address` a ON (th.thread_address_id=a.address_id) ".
			"WHERE th.`thread_id` = %d ".
			"LIMIT 0,1",
				$thread_id
		);
		$ticket_res = $db->query($sql);
		
		if($row = $db->grab_first_row($ticket_res)) {
			$date = $row['thread_date'];
			$ticket_id = intval($row['ticket_id']);
			$sender = stripslashes($row['address_address']);
			
			$sql = sprintf("SELECT tcp.thread_id, tcp.`thread_content_part`, tcp.`thread_id` ".
				"FROM `thread_content_part` tcp ".
				"WHERE tcp.`thread_id` = %d ".
				"ORDER BY tcp.`content_id` ASC ",
					$thread_id
			);
			$content_res = $db->query($sql);
			
			if($db->num_rows($content_res)) {
				while($content_row = $db->fetch_row($content_res)) {
					$text .= $content_row['thread_content_part'];
				}
			}
		}
		
		// [TODO] This should be handled in a more generic way later
		if(!get_magic_quotes_gpc())
			$text = stripslashes($text);
		
		$preview = new stdClass();
		$preview->ticket_id = $ticket_id;
		$preview->sender = $sender;
		$preview->date = $date;
		$preview->text = $text;
		
		return $preview;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return CerWorkstationTicket
	 */
	function getTicketById($id) {
		$tickets = CerWorkstationTickets::getTicketsByIds(array($id));
		return @$tickets[$id];
	}
	
	function addAgentDelayToTicket($agent_id, $ticket_id, $delay) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */

		if(empty($agent_id) || empty($ticket_id))
			return FALSE;

		CerWorkstationTickets::removeAgentDelayFromTicket($agent_id,$ticket_id);
		
		$sql = sprintf("INSERT INTO `dispatcher_delays` (ticket_id,agent_id,delay_type,added_timestamp,expire_timestamp,reason) ".
			"VALUES (%d,%d,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()+%d,'')",
			$ticket_id,
			$agent_id,
			$delay
		);
		$db->query($sql);
			
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		if(!empty($user_id)) {
			include_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");
			CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_DELAY,cer_DateTimeFormat::secsAsEnglishString($delay));
		}

		return TRUE;
	}
	
	function removeAgentDelayFromTicket($agent_id, $ticket_id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */

		$sql = sprintf("DELETE FROM `dispatcher_delays` WHERE ticket_id = %d AND agent_id = %d",
			$ticket_id,
			$agent_id
		);
		$db->query($sql);
		
		return TRUE;
	}
	
	function _cacheTicketFlags($ticket_id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */

		$sql = sprintf("SELECT count(agent_id) as hits, ticket_id FROM `ticket_flags_to_agents` WHERE ticket_id = %d GROUP BY ticket_id",
			$ticket_id
		);
		$res = $db->query($sql);
		
		if($row = $db->grab_first_row($res)) {
			$hits = $row['hits'];
		} else {
			$hits = 0;
		}
		
		$sql = sprintf("UPDATE `ticket` SET `num_flags` = %d WHERE `ticket_id` = %d",
			$hits,
			$ticket_id
		);
		$db->query($sql);
	}
	
	/*
	 * [TODO]: Update this function to also handle an array of ticket IDs as an argument, so we can avoid N query issues
	 */
//	function _cacheTicketTeams($ticket_id) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//
//		$sql = sprintf("SELECT count(team_id) as hits, ticket_id FROM `workstation_routing_to_tickets` WHERE ticket_id = %d GROUP BY ticket_id",
//			$ticket_id
//		);
//		$res = $db->query($sql);
//		
//		if($row = $db->grab_first_row($res)) {
//			$hits = $row['hits'];
//		} else {
//			$hits = 0;
//		}
//		
//		$sql = sprintf("UPDATE `ticket` SET `num_teams` = %d WHERE `ticket_id` = %d",
//			$hits,
//			$ticket_id
//		);
//		$db->query($sql);
//	}
	
	function addRequesterToTicket($requester,$ticket_id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */

		// [JAS]: [TODO] Move the requester functionaluty from CER_PARSER_TICKET to CerWorkstationTickets
		include_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php");
		$cer_ticket = new CER_PARSER_TICKET();
		$requester_id = $cer_ticket->get_address_id($requester);
		if($cer_ticket->save_requester_link($ticket_id,$requester_id)) {
			global $session;
			CER_AUDIT_LOG::log_action($ticket_id,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$requester);
		}
	}
	
	function removeRequesterIdFromTicket($requester_id,$ticket_id,$user_id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
		// [ddh]: retrieve requester address for logging purposes
		$requester_address = "address not found";
		$sql = sprintf("SELECT a.address_address FROM address a WHERE address_id = %d",
				$requester_id
		);
		$result = $db->query($sql);
		
		if ($row = $db->grab_first_row($result)) {
			$requester_address = $row["address_address"];
		}

		$sql = sprintf("DELETE FROM requestor WHERE ticket_id = %d AND ".
			"address_id = %d",
				$ticket_id,
				$requester_id
		);
		$db->query($sql);
		
		CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_REMOVE_REQUESTER,$requester_address);
	}
	
	function addFlagToTicket($agent_id, $ticket_id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		$notify = CerNotification::getInstance();

		if(empty($agent_id) || empty($ticket_id))
			return FALSE;
			
		$sql = sprintf("REPLACE INTO `ticket_flags_to_agents` (ticket_id,agent_id) VALUES (%d,%d)",
			$ticket_id,
			$agent_id
		);
		$db->query($sql);
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		$user_name = CerAgents::getNameById($agent_id);

		if(!empty($user_id)) {
			// [JAS]: Don't notify the agent about their own flagging.
			if($agent_id != $user_id) {
				$notify->triggerEvent(EVENT_ASSIGNMENT,array('ticket_id'=>$ticket_id,'agent_id'=>$agent_id));		
				CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_TAKE_OTHER,$user_name);
			} else {
				CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_TAKE,"");
			}
		}
		
		CerWorkstationTickets::_cacheTicketFlags($ticket_id);
		
		return TRUE;
	}
	
	/**
	 * Flags multiple tickets to an agent, returning conflicts. 
	 *
	 * @param int $agent_id The agent to whom the tickets will be flagged
	 * @param array $tickets The ticket ids to flag.  (keys and values are the ticket ids)
	 * @param boolean $override If false only unflagged tickets will be flagged to this agent, otherwise all tickets will be flagged
	 * @return array If override=false, Returns an array of ticket ids that others had flagged, otherwise an empty array
	 */
	function addFlagToTickets($agent_id, $tickets, $override=false) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
	   	// Find out if anyone selected any of these tickets previously.
	   	$sql = "SELECT ticket_id, agent_id FROM `ticket_flags_to_agents` WHERE ticket_id IN (%s)";
	   	$res = $db->query(sprintf($sql,implode(",",$tickets)));
	   	$flagged_by_others_only = array();
	   	$flagged_by_others = array();
	   	$flagged_by_me = array();
	   	
	   	// If anyone has already selected any of the tickets add it to an appropriate array
	   	if(is_array($res)) {
	   		foreach($res as $row) {
	   			$ticket_id = $row['ticket_id'];
	   			
	   			if($row['agent_id'] == $agent_id) {
	   				$flagged_by_me[] = $tickets[$ticket_id];
	   			}
	   			else {
	   				$flagged_by_others[] = $tickets[$ticket_id];
	   			}
	   		}
	   		$flagged_by_me = array_unique($flagged_by_me);
	   		$flagged_by_others = array_unique($flagged_by_others);
		   	
		   	if($override) {
		   		//[mdf] override param specified, so flag tickets even if they are flagged by others (as long as not by me)
		   		$tickets_to_insert = array_diff($tickets, $flagged_by_me);
		   	}
		   	else {
		   		//[mdf] default to only insert tickets if they are currently not flagged by anyone...
		   		$tickets_to_insert = array_diff($tickets, $flagged_by_me, $flagged_by_others);
				//[mdf] however, we keep track of ones that have been flagged by others but not me, so the user can confirm later
			   	$flagged_by_others_only = array_diff($flagged_by_others, $flagged_by_me);
		   	}
	
		   	if(is_array($tickets_to_insert)) {
		   		foreach($tickets_to_insert as $ticket_id=>$ticket) {
	   				CerWorkstationTickets::addFlagToTicket($agent_id,$ticket_id);
		   		}
		   	}
		}
	   	return $flagged_by_others_only;
	}	
	
	function removeFlagOnTicket($ticket_id,$agent_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		if(empty($agent_id) || empty($ticket_id))
			return FALSE;
			
		$sql = sprintf("DELETE FROM `ticket_flags_to_agents` WHERE ticket_id = %d AND agent_id = %d",
			$ticket_id,
			$agent_id
		);
		$db->query($sql);
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		if(!empty($user_id)) {
			$user_name = CerAgents::getNameById($agent_id);
			
			if($agent_id != $user_id) {
				CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_RELEASE_OTHER,$user_name);
			} else {
				CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_RELEASE,"");
			}
			
		}
		
		CerWorkstationTickets::_cacheTicketFlags($ticket_id);
		
		return TRUE;
	}
	
	function removeFlagOnTickets($ticket_ids, $agent_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		if(empty($agent_id) || empty($ticket_ids) || !is_array($ticket_ids))
			return FALSE;
		
		for($i=0; $i < sizeof($ticket_ids); $i++) {
			if(!settype($ticket_ids[$i], "integer")) 
				$ticket_ids[$i] = 0;
		}
		
		$sql = "DELETE FROM `ticket_flags_to_agents` WHERE agent_id = %d and ticket_id IN (%s)";
		$sql = sprintf($sql, $agent_id, "'".implode("','",$ticket_ids)."'");
		
		$db->query($sql);
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		$user_name = CerAgents::getNameById($agent_id);
		
		// [JAS]: [TODO] Foreach??
		for($i=0; $i < sizeof($ticket_ids); $i++) {
			if($ticket_ids[$i] !== false) {
				if(!empty($user_id)) {
					if($agent_id != $user_id) {
						CER_AUDIT_LOG::log_action($ticket_ids[$i],$user_id,AUDIT_ACTION_RELEASE_OTHER,$user_name);
					} else {
						CER_AUDIT_LOG::log_action($ticket_ids[$i],$user_id,AUDIT_ACTION_RELEASE,"");
					}

				}
				CerWorkstationTickets::_cacheTicketFlags($ticket_ids[$i]);
			}
		}
		
		return TRUE;		
	}
	
	function changeTicketTags($ticket_id, $tags) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("DELETE FROM `workstation_tags_to_tickets` WHERE `ticket_id` = %d",
			$ticket_id
		);
		$db->query($sql);
		
		if(!is_array($tags) || empty($ticket_id))
			return FALSE;
		
		foreach($tags as $tag_id) {
			$sql = sprintf("INSERT INTO `workstation_tags_to_tickets` (`ticket_id`,`tag_id`) ".
				"VALUES (%d,%d)",
					$ticket_id,
					$tag_id
			);
			$db->query($sql);
		}
	}
	
	/**
	 * Adds one tag to multiple tickets with one query
	 *
	 * @param integer $tag_id
	 * @param array $ticket_ids
	 * @return void
	 */
	function addTagTickets($tag_id, $ticket_ids) {
		
		$db = cer_Database::getInstance();

		if(empty($tag_id) || empty($ticket_ids) || !is_array($ticket_ids))
			return FALSE;
		
		for($i=0; $i < sizeof($ticket_ids); $i++) {
			if(!settype($ticket_ids[$i], "integer")) 
				$ticket_ids[$i] = 0;
		}
		
		$sql = "REPLACE INTO `workstation_tags_to_tickets` (`ticket_id`,`tag_id`) VALUES ";
		
		$separator = "";
		foreach($ticket_ids as $ticket_id) {
			if($ticket_id !== 0 && $tag_id !== 0) {			
				$sql .= $separator . sprintf("(%d,%d)", $ticket_id, $tag_id);
				if($separator=="") $separator = ",";
			}
		}
		$db->query($sql);
	}
	
//	function changeTicketTeams($ticket_id, $teams) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//
//		if(empty($ticket_id))
//			return FALSE;
//		
//		$sql = sprintf("DELETE FROM `workstation_routing_to_tickets` WHERE `ticket_id` = %d",
//			$ticket_id
//		);
//		$db->query($sql);
//		
//		if(is_array($teams))
//		foreach($teams as $team_id) {
//			$sql = sprintf("INSERT INTO `workstation_routing_to_tickets` (`ticket_id`,`team_id`) ".
//				"VALUES (%d,%d)",
//					$ticket_id,
//					$team_id
//			);
//			$db->query($sql);
//		}
//		
//		CerWorkstationTickets::_cacheTicketTeams($ticket_id);
//	}
	
	/**
	 * Adds ticket routing for one team on many tickets with one query
	 *
	 * @param integer $team_id
	 * @param array $ticket_ids
	 * @return void
	 */
//	function addTeamTickets($team_id, $ticket_ids) {
//		
//		$db = cer_Database::getInstance();
//
//		if(empty($team_id) || empty($ticket_ids) || !is_array($ticket_ids))
//			return FALSE;
//		
//		for($i=0; $i < sizeof($ticket_ids); $i++) {
//			if(!settype($ticket_ids[$i], "integer")) 
//				$ticket_ids[$i] = 0;
//		}
//		
//		$sql = "REPLACE INTO `workstation_routing_to_tickets` (`ticket_id`,`team_id`) VALUES ";
//		
//		$separator = "";
//		foreach($ticket_ids as $ticket_id) {
//			if($ticket_id !== 0 && $team_id !== 0) {
//				$sql .= $separator . sprintf("(%d,%d)", $ticket_id, $team_id);
//				if($separator=="") $separator = ",";
//			}
//		}
//		$db->query($sql);
//		
//		foreach($ticket_ids as $ticket_id) {
//			CerWorkstationTickets::_cacheTicketTeams($ticket_id);
//		}
//	}	
	
	function changeTicketAgents($ticket_id, $agents) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$notify = CerNotification::getInstance();

		$preticket = CerWorkstationTickets::getTicketById($ticket_id);
		if(!$preticket) return;
		
		$sql = sprintf("DELETE FROM `ticket_spotlights_to_agents` WHERE `ticket_id` = %d",
			$ticket_id
		);
		$db->query($sql);
		
		if(!is_array($agents) || empty($ticket_id))
			return FALSE;
		
		foreach($agents as $agent_id) {
			$sql = sprintf("INSERT INTO `ticket_spotlights_to_agents` (`ticket_id`,`agent_id`) ".
				"VALUES (%d,%d)",
					$ticket_id,
					$agent_id
			);
			$db->query($sql);
			
			if(!isset($preticket->agents[$agent_id])) {
				$notify->triggerEvent(EVENT_ASSIGNMENT,array('ticket_id'=>$ticket_id,'agent_id'=>$agent_id));
			}
		}
		
	}
	
	/**
	 * Adds spotlight assignments for one agent and many tickets with one query
	 *
	 * @param integer $agent_id
	 * @param array $ticket_ids
	 * @return void
	 */
	function addAgentTickets($agent_id, $ticket_ids, $is_hard_assign=0) {
		//echo "$agent_id $ticket_ids";exit();
		$db = cer_Database::getInstance();
		$notify = CerNotification::getInstance();

		if(empty($agent_id) || empty($ticket_ids) || !is_array($ticket_ids))
			return FALSE;

		for($i=0; $i < sizeof($ticket_ids); $i++) {
			if(!settype($ticket_ids[$i], "integer")) 
				$ticket_ids[$i] = 0;
		}
		
		$sql = "REPLACE INTO `ticket_spotlights_to_agents` (`ticket_id`,`agent_id`) VALUES ";
		
		$separator = "";
		foreach($ticket_ids as $ticket_id) {
			if($ticket_id !== 0 && $agent_id !== 0) {
				$sql .= $separator . sprintf("(%d,%d)", $ticket_id, $agent_id);
				if($separator=="") $separator = ",";
			}
			$notify->triggerEvent(EVENT_ASSIGNMENT,array('ticket_id'=>$ticket_id,'agent_id'=>$agent_id));
		}
		//echo $sql;exit();
		$db->query($sql);
	}		
	
	function addTagsToTicketId($tags, $ticket_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		if(!is_array($tags) || empty($ticket_id))
			return FALSE;
		
		foreach($tags as $tag_id) {
			$sql = sprintf("REPLACE INTO `workstation_tags_to_tickets` (`ticket_id`,`tag_id`) ".
				"VALUES ('%d','%d')",
					$ticket_id,
					$tag_id
			);
			$db->query($sql);
		}

		return TRUE;
	}
	
//	function addTeamsToTicketId($teams, $ticket_id) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//
//		if(!is_array($teams) || empty($ticket_id))
//			return FALSE;
//		
//		foreach($teams as $team_id) {
//	   	$sql = sprintf("REPLACE INTO `workstation_routing_to_tickets` (`ticket_id`, `team_id`) ".
//	   		"VALUES ('%d','%d')",
//	   			$ticket_id,
//	   			$team_id
//	   	);
//			$db->query($sql);
//		}
//		
//		CerWorkstationTickets::_cacheTicketTeams($ticket_id);
//
//		return TRUE;
//	}
	
	function addAgentsToTicketId($agents, $ticket_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$notify = CerNotification::getInstance();

		if(!is_array($agents) || empty($ticket_id))
			return FALSE;
		
		foreach($agents as $agent_id) {
	   	$sql = sprintf("REPLACE INTO `ticket_spotlights_to_agents` (`ticket_id`, `agent_Id`) ".
	   		"VALUES ('%d','%d')",
	   			$ticket_id,
	   			$agent_id
	   	);
			$db->query($sql);
			
			$notify->triggerEvent(EVENT_ASSIGNMENT,array('ticket_id'=>$ticket_id,'agent_id'=>$agent_id));
		}
		
		return TRUE;
	}
	
	function removeTagsFromTicketId($tags, $ticket_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($tags) || empty($ticket_id))
			return FALSE;
		
		$sql = sprintf("DELETE FROM `workstation_tags_to_tickets` WHERE `ticket_id` = %d AND `tag_id` IN (%s)",
			$ticket_id,
			implode(',', $tags)
		);
		$db->query($sql);
		
		return TRUE;
	}
	
	function removeAgentsFromTicketId($agents, $ticket_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($agents) || empty($ticket_id))
			return FALSE;
		
		$sql = sprintf("DELETE FROM `ticket_spotlights_to_agents` WHERE `ticket_id` = %d AND `agent_id` IN (%s)",
			$ticket_id,
			implode(',', $agents)
		);
		$db->query($sql);
		
		return TRUE;
	}
	
//	function removeTeamsFromTicketId($teams, $ticket_id) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//		
//		if(!is_array($teams) || empty($ticket_id))
//			return FALSE;
//		
//		$sql = sprintf("DELETE FROM `workstation_routing_to_tickets` WHERE `ticket_id` = %d AND `team_id` IN (%s)",
//			$ticket_id,
//			implode(',', $teams)
//		);
//		$db->query($sql);
//		
//		CerWorkstationTickets::_cacheTicketTeams($ticket_id);	
//		
//		return TRUE;
//	}
	
	function sendCloseResponse($ticket) {
		include_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php");
		
		$cer_ticket = new CER_PARSER_TICKET();
		$cer_ticket->load_ticket_data($ticket); // this is needed for the autoresponse

		$cer_parser = new CER_PARSER();
		$cer_parser->send_closeresponse($cer_ticket);							
			
	}
	
	function create($to,$subject,$body,$from,$attachments=array(),$options=array()) {
		$headers = array();
		$headers[] = "To: " . $to;
		$headers[] = "CC: " . $from; // [ddh] to auto-add additional requestors on a manual ticket create
		$headers[] = "From: " . $from;
		$headers[] = "Reply-To: " . $from;
		$headers[] = "Subject: " . $subject;
		$headers[] = "Date: " . date("r");
		$bool = CerWorkstationTickets::send_internal_mail($headers,$body,$attachments,$options);
		
		if($bool) {
			/* @var $db cer_Database */
			$db = cer_Database::getInstance();
			$id = 0;
			$mask = "";
			$from_array = explode(',', $from);
			
			// [JAS]: Recover our new ticket id
			$sql = sprintf("SELECT t.ticket_id, t.ticket_mask ".
				"FROM ticket t ".
				"INNER JOIN thread th ON (th.thread_id = t.min_thread_id) ".
				"INNER JOIN address a ON (t.opened_by_address_id=a.address_id) ".
				"WHERE 1 ". //  t.ticket_subject = %s
				"AND a.address_address = %s ".
				"ORDER BY t.ticket_id DESC ".
				"LIMIT 0,1",
					$db->escape(trim($from_array[0]))
			);
			$res = $db->query($sql);
			if($row = $db->grab_first_row($res)){
				$id = intval($row['ticket_id']);
				$mask = empty($row['ticket_mask']) ? $id : $row['ticket_mask'];
			}

			// Send a copy to the requester
			if(@$options['CC_REQUESTER']) {
				$notice = sprintf("A new helpdesk ticket has been opened for you with the ID: %s\r\n----\r\n\r\n",
					$mask
				);
				
				$ticket = CerWorkstationTickets::getTicketById($id);
				$cfg = CerConfiguration::getInstance();
				$mask_string = sprintf("[%s #%s]: ",
					$ticket->queue_prefix,
					$ticket->mask
				);
				$subject = sprintf("%s%s",
					(!empty($cfg->settings['subject_ids']) ? $mask_string : ""),
					$subject
				);

				CerWorkstationTickets::send_outgoing_mail($from,$subject,$notice.$body,$to,$attachments);
			}
			
			return $id;
			
		} else {
			return FALSE;
		}
	}
	
	/*
	 * Adds raw thread content to a ticket without parsing/emailing.
	 * Currently used for logging outbound forwarded email.
	 */
	function addTicketThread($ticket_id,$thread_type,$to,$cc,$from,$subject,$body,$attachments=array()) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
		$agents = CerAgents::getInstance();
		
		if(!is_array($to)) $to = array($to);
		if(!is_array($cc)) $cc = array($cc);
			
		$to_list = implode(",",$to);
		$cc_list = implode(",",$cc);

		// [JAS]: [TODO] Turn message id into an API call
		$message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
		$requester_id = CerWorkstationTickets::getAddressId($from);

		$thread_date = date("Y-m-d H:i:s");
		$thread_received = date("Y-m-d H:i:s");
		$is_agent_reply = (($agents->isAgentAddress($from)) ? 1 : 0);

		$sql = "INSERT INTO thread (ticket_id, thread_message_id, thread_address_id, thread_type, thread_date, ".
			"thread_received, thread_subject, thread_to, thread_cc, thread_replyto, is_agent_message, is_hidden) ".
		sprintf("VALUES (%d,%s,%d,%s,%s,%s,%s,%s,%s,%s,%d,0)",
			$ticket_id,
			$db->escape($message_id),
			$requester_id,
			$db->escape($thread_type),
			$db->escape($thread_date),
			$db->escape($thread_received),
			$db->escape($subject),
			$db->escape($to_list),
			$db->escape($cc_list),
			$db->escape($from),
			$is_agent_reply
		);
		$db->query($sql);
		$thread_id = $db->insert_id();

		include_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
		$thread_handler = new cer_ThreadContentHandler();
		$thread_handler->writeThreadContent($thread_id,$body);

		/*
		 * [JAS]: [TODO] Later this should be capable of handling the first ticket post 
		 * by setting ticket.opened_by_address_id and ticket.min_thread
		 */
		$sql = sprintf("UPDATE ticket SET max_thread_id = %d, last_reply_by_agent = %d ".
		"WHERE ticket_id = %d",
			$thread_id,
			$is_agent_reply,
			$ticket_id
		);
		$db->query($sql);

		// [TODO] redo attachments
//		$this->_save_thread_attachments($thread_id,$email);

		return $thread_id;
	}
	
	// [JAS]: [TODO] Move to an Address API
	function getAddressId($address) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("SELECT a.address_id FROM address a WHERE a.address_address = %s",
			$db->escape($address)
		);
		$addy_res = $db->query($sql);

		if($db->num_rows($addy_res)) {
			$addy_data = $db->fetch_row($addy_res);
			return $addy_data["address_id"];
		} else {
			$sql = sprintf("INSERT IGNORE INTO address (address_address) VALUES (%s)",
				$db->escape(strtolower($address))
			);

			$db->query($sql);
			return $db->insert_id();
		}
	}
	
	function reply($ticket_id,$body,$from,$attachments=array(),$params=array()) {
		$ticket = CerWorkstationTickets::getTicketById($ticket_id); /* @var $ticket CerWorkstationTicket */
		if(empty($ticket)) return FALSE;
		
		$subject = sprintf("[msg #%s]: %s",
			$ticket->mask,
			$ticket->subject
		);
		
		$headers = array();
		$headers[] = "To: " . $ticket->queue_reply_to;
		$headers[] = "From: " . $from;
		if(isset($params['cc'])) $headers[] = "Cc: " . $params['cc'];
		if(isset($params['bcc'])) $headers[] = "Bcc: " . $params['bcc'];
		$headers[] = "Reply-To: " . $from;
		$headers[] = "Subject: " . $subject;
		$headers[] = "Date: " . date("r");
		
		if(isset($params['thread_id']) && ($thread = CerWorkstationTickets::getThreadById($params['thread_id']))) {
			$headers[] = "In-Reply-To: " . $thread->message_id;
			$headers[] = "References: " . $thread->message_id;
		}
		
		/*
		 * Send our mail to the helpdesk.
		 */ 
		$options = array();
		if(isset($params['cc'])) $options['PROXY_CC'] = $params['cc'];
		if(isset($params['bcc'])) $options['PROXY_BCC'] = $params['bcc'];
		CerWorkstationTickets::send_internal_mail($headers,$body,$attachments,$options);
	}
	
	function comment($ticket_id,$text,$agent_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("INSERT INTO `next_step` (`ticket_id`,`created_by_agent_id`,`date_created`,`note`) ".
			"VALUES (%d,%d,UNIX_TIMESTAMP(NOW()),%s) ",
				$ticket_id,
				$agent_id,
				$db->escape($text)
		);
		$db->query($sql);
		
		// [ddh]: notify watchers of comment. Yeah, it's ugly, but it works, and it's not getting ported forward anywhere...
		include_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerRawEmail.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$cer_parser = new CER_PARSER();
		$ticket = CerWorkstationTickets::getTicketById($ticket_id);
		$email = new stdClass();
		$email->headers = new CerRawEmailHeaders();
		$email->headers->message_id = $ticket->last_message_id;
		$email->headers->from = $ticket->queue_reply_to;
		$email->body = $text;
		$cer_parser->send_email_to_watchers($email, $ticket_id, "", "comment", false);

		// [ddh]: audit log entry (also fixes reporting bug)
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		if(!empty($user_id)) {
			CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_COMMENTED);
		}
	}
	
	function forward($ticket_id,$to,$body,$attachments=array()) {
		$ticket = CerWorkstationTickets::getTicketById($ticket_id); /* @var $ticket CerWorkstationTicket */
		if(empty($ticket)) return FALSE;
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		@$user_email = $session->vars['login_handler']->user_email;
		
		$cfg = CerConfiguration::getInstance();
		$mask_string = sprintf("[%s #%s]: ",
			$ticket->queue_prefix,
			$ticket->mask
		);
		$subject = sprintf("%s%s",
			(!empty($cfg->settings['subject_ids']) ? $mask_string : ""),
			$ticket->subject
		);
		
		CerWorkstationTickets::send_outgoing_mail($to,$subject,$body,$ticket->queue_display_name . " <" . $ticket->queue_reply_to . ">",$attachments);
		CerWorkstationTickets::addTicketThread($ticket_id,"forward",$to,array(),$user_email,$subject,$body,$attachments);
		
		if(!empty($user_id)) {
			CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_THREAD_FORWARD,$to);
		}
	}

	function _buildSmtp($headers,$body,$attachments=array()) {
		
		// removed params to/subject/from to headers
		
		include_once(FILESYSTEM_PATH . "cerberus-api/mail/mimePart.php");
		
		// Build MIME body including file attachments [JSJ]
		$message =& new Mail_mimePart('', array('content_type'=>'multipart/mixed'));
		
		$message->addSubpart($body, array('content_type'=>'text/plain'));
		
		foreach($attachments as $attachment) {
			$params['content_type'] = (isset($attachment['content_type']) && !empty($attachment['content_type'])) ? $attachment['content_type'] : 'application/octet-stream';
			$params['encoding']     = 'base64';
			$params['disposition']  = 'attachment';
			$params['dfilename']    = $attachment['file_name'];
			$data = @file_get_contents($attachment["tmp_file"]);
			$message->addSubpart($data, $params);
		}
		
		$smtp = array();
		
	   // Add Message-ID to headers [JSJ]
	   srand((double)microtime()*10000000);
	   $message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
	   $headers[] = "Message-ID: " . $message_id;
		
	   // Add MIME Header [JSJ]
	   $headers[] = "MIME-Version: 1.0";
		
		// Set MIME encoded body as new body and add required headers [JSJ]
		$mime_output = $message->encode();
		$body = $mime_output['body'];
		foreach($mime_output['headers'] as $key=>$value) {
			$headers[] = $key . ": " . $value;
		}
	
	   foreach($headers as $h) {$smtp[] = $h."\r\n";}
	
	   $smtp[] = "\r\n"; // break headers
	   
		if($body) {$bdy = preg_replace("/^\./","..",explode("\r\n",$body));}
		if($bdy) {foreach($bdy as $b) {$smtp[] = $b."\r\n";}}
		
		return $smtp;
	}
	
	function send_outgoing_mail($to,$subject,$body,$from,$attachments=array(),$in_reply_to=null) {
		include_once(FILESYSTEM_PATH . "cerberus-api/mail/cerbHtmlMimeMail.php");
		
		if(!is_array($to)) $to = array($to);
		
		$cfg = CerConfiguration::getInstance();
		
		$mail = new cerbHtmlMimeMail();
		if (!empty($cfg->settings["cut_line"]))
			$mail->setText($cfg->settings["cut_line"] . "\r\n\r\n" . $body);
		else
			$mail->setText($body);
		$mail->setFrom($from);
		if(!empty($cc)) $mail->setCc($cc);
		$mail->setSubject(stripcslashes($subject));
		$mail->setReturnPath($from);
		$mail->setHeader("Reply-To", $from);
		$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
		
		if(!empty($in_reply_to)) {
			$mail->setHeader("In-Reply-To", $in_reply_to);
			$mail->setHeader("References", $in_reply_to);
		}
		
	   // Add Message-ID to headers [JSJ]
	   srand((double)microtime()*10000000);
	   $message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
		
		$mail->setHeader("Message-ID", $message_id);

		if(count($attachments)) // $send_attachments !== false 
		{
			foreach($attachments as $file)
			{
				$attachment = @$mail->getFile($file['tmp_file']);
				if(!empty($attachment)) $mail->addAttachment($attachment, $file['file_name']);
			}
		}

		$result = @$mail->send($to,$cfg->settings["mail_delivery"]);
	}
	
	function send_internal_mail($headers,$body,$attachments=array(),$options=array()) {
		$smtp = CerWorkstationTickets::_buildSmtp($headers,$body,$attachments);
	
		include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerPop3RawEmail.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerProcessEmail.class.php");
		$process = new CerProcessEmail();
	
		$email = implode("", $smtp);
		
		if(!empty($email)) {
			$pop3email = new CerPop3RawEmail($email);
			$result = $process->process($pop3email,$options);
			if(!$result) { // re-fail...
				$failed = $process->last_error_msg;
			} else {
				// success!
				$failed = FALSE;
			}
		}
		
		if($failed)
			return FALSE;
		else
			return TRUE;
	}	
	
	function markSpam($ticket_id) {
		include_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
		$bayes = new cer_BayesianAntiSpam();
		$bayes->mark_tickets_as_spam(array($ticket_id));
	}
	
	function markHam($ticket_id) {
		include_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
		$bayes = new cer_BayesianAntiSpam();
		$bayes->mark_tickets_as_ham(array($ticket_id));
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $ticket_id
	 * @param unknown_type $status
	 * @todo Needs audit log
	 */
	function setTicketStatus($ticket_id, $status) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$sql = null;
		include_once(FILESYSTEM_PATH . "cerberus-api/acl/CerACL.class.php");
		$acl = CerACL::getInstance();		
		
		switch($status) {
			case "open":
				$sql = sprintf("UPDATE `ticket` SET `is_deleted` = 0, `is_closed` = 0 WHERE `ticket_id` = %d",
					$ticket_id
				);
				break;
			
			case "resolved":
			case "closed":
				$sql = sprintf("UPDATE `ticket` SET `is_deleted` = 0, `is_closed` = 1 WHERE `ticket_id` = %d",
					$ticket_id
				);
				break;
				
			case "deleted":
				if(!$acl->has_priv(PRIV_TICKET_DELETE)) break;
				$sql = sprintf("UPDATE `ticket` SET `is_deleted` = 1, `is_closed` = 1 WHERE `ticket_id` = %d",
					$ticket_id
				);
				break;
		}

		if(!empty($sql)) {
			$db->query($sql);
			
			global $session;
			@$user_id = $session->vars['login_handler']->user_id;
			if(!empty($user_id)) {
				CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_CHANGED_STATUS,$status);
			}
		}
		
	}

	/**
	 * Sets the mailbox for a ticket or tickets
	 *
	 * @param array/integer $ticket_ids An array of ticket ids (or an integer for one ticket id) whose mailbox will be changed.
	 * @param integer $mailbox the queue id that the tickets will be changed to
	 * @return boolean false on error, true on success
	 */
	function setTicketMailbox($ticket_ids, $mailbox) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$sql = null;
		
		if(!is_array($ticket_ids)) $ticket_ids = array($ticket_ids);
		
		if(empty($ticket_ids) || empty($mailbox) || !is_array($ticket_ids)) {
			return false;
		}
		
		for($i=0; $i < sizeof($ticket_ids); $i++) {
			if(!settype($ticket_ids[$i], "integer")) 
				$ticket_ids[$i] = 0;
		}
		
		$sql = sprintf("UPDATE `ticket` SET `ticket_queue_id` = %d WHERE `ticket_id` IN (%s)",
			$mailbox,
			"'".implode("','",$ticket_ids)."'"
		);
		$db->query($sql);
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		if(!empty($user_id)) {
			// [JAS]: [TODO] This really should be optimized later.
			$sql = "SELECT q.queue_name FROM queue q WHERE q.queue_id = '%d';";
			$queue_record = $db->query(sprintf($sql, $mailbox));
			$queue_row = $db->fetch_row($queue_record);
			for($i=0; $i < sizeof($ticket_ids); $i++) {
				CER_AUDIT_LOG::log_action($ticket_ids[$i],$user_id,AUDIT_ACTION_CHANGED_QUEUE,$queue_row['queue_name']);
			}
		}
		return true;
	}	
	
	function setTicketStatusId($ticket_id, $ticket_status_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$sql = null;

		$sql = sprintf("UPDATE `ticket` SET `ticket_status_id` = %d WHERE `ticket_id` = %d",
			$ticket_status_id,
			$ticket_id
		);
		$db->query($sql);
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		if(!empty($user_id)) {
			CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_CHANGED_STATUS_ID,$ticket_status_id);
		}
	}
	
	function setTicketPriority($ticket_id, $priority) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$sql = null;

		$sql = sprintf("UPDATE `ticket` SET `ticket_priority` = %d WHERE `ticket_id` = %d",
			$priority,
			$ticket_id
		);
		$db->query($sql);
		
		global $session;
		@$user_id = $session->vars['login_handler']->user_id;
		if(!empty($user_id)) {
			CER_AUDIT_LOG::log_action($ticket_id,$user_id,AUDIT_ACTION_CHANGED_PRIORITY,$priority);
		}
	}
	
	function setTicketWaitingOnCustomer($ticket_id, $waiting=0) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$sql = null;

		$sql = sprintf("UPDATE `ticket` SET `is_waiting_on_customer` = %d WHERE `ticket_id` = %d",
			$waiting,
			$ticket_id
		);
		$db->query($sql);
	}
	
};

class CerWorkstationThread {
	var $id = null;
	var $ticket_id = null;
	var $message_id = null;
}

class CerWorkstationTicket {
	var $id = null;
	var $min_thread_id = 0;
	var $max_thread_id = 0;
	var $mask = null;
	var $subject = null;
	var $priority = 0;
	var $is_waiting_on_customer = 0;
	var $is_closed = 0;
	var $is_deleted = 0;
	var $ticket_status_id = 0;
	var $ticket_status_text = '';
	var $opened_by_address_id = 0;
	var $opened_by_address = '';
	var $date_latest_reply = "";
	var $queue_id = 0;
	var $queue_name = "";
	var $queue_display_name = "";
	var $queue_prefix = "";
	var $queue_reply_to = "";
	var $queue_address = "";
	var $address_latest_reply = "";
	var $preview_text = "";
	var $last_message_id = "";
	var $spam_trained = 0;
	var $spam_probability = 0.0000;
	var $date_due = null;
	var $date_delay = null;
	var $tags = array();
	var $teams = array();
	var $agents = array();
	var $flags = array();
	
	function getStatus() {
		if($this->is_deleted) {
			return "deleted";
		} elseif ($this->is_closed) {
			return "closed";
		} else {
			return "open";
		}
	}
	
	function getRequesters() {
		$reqs = array();
		$db = cer_Database::getInstance();
		
		$sql = sprintf("SELECT a.address_id,a.address_address FROM requestor r INNER JOIN address a ON (r.address_id=a.address_id) WHERE r.ticket_id = %d ORDER BY a.address_address",
			$this->id
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$addy_id = intval($row['address_id']);
				$addy = stripslashes($row['address_address']);
				$reqs[$addy_id] = $addy;
			}
		}
		
		return $reqs;
	}
	
};