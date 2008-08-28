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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/entity/CerEntityObject.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/entity/model/CerNextStep.class.php");

/**
 * Database abstraction layer for ticket data
 *
 */
class ticket_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;
   
   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return ticket_sql
    */
   function ticket_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Get ticket view function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function basic_view($params) {
      $limit = 0+$params["limit"];
      $page = 0+$params["page"]*$limit;
      $field = $params["order_by_field"];
      $direction = $params["order_by_direction"];
      $sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_priority, t.ticket_spam_trained, t.last_reply_by_agent, t.ticket_spam_probability, t.is_closed, t.is_deleted, t.is_waiting_on_customer,  
               th.thread_date, thr.thread_received, th.thread_address_id, t.min_thread_id, a.address_address, 
               t.ticket_mask, t.ticket_time_worked AS total_time_worked, ad.address_address AS requester_address, ad.address_banned, 
               q.queue_id, q.queue_name, c.name AS company_name, c.id AS company_id, t.ticket_date,  
               thr.thread_address_id AS requester_address_id, 
               t.min_thread_id, t.max_thread_id, UNIX_TIMESTAMP(th.thread_date) AS last_wrote_date
               FROM (ticket t, thread th, thread thr, address a, address ad) 
               LEFT JOIN queue q ON ( q.queue_id = t.ticket_queue_id )
               LEFT JOIN public_gui_users pu ON ( ad.public_user_id = pu.public_user_id )
               LEFT JOIN company c ON ( pu.company_id = c.id )
               WHERE t.max_thread_id = th.thread_id
               AND t.min_thread_id = thr.thread_id
               AND a.address_id = th.thread_address_id
               AND ad.address_id = thr.thread_address_id
               ORDER BY `%s` %s
               LIMIT %d, %d";
      return $this->db->GetAll(sprintf($sql, $field, $direction, $page, $limit));
   }  
   
   /**
    * Get headers function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_headers($params) {  
      $tickets = $params['tickets'];
      $sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_priority, t.ticket_spam_trained, t.last_reply_by_agent, t.ticket_spam_probability, 
               th.thread_date, thr.thread_received, th.thread_address_id, t.min_thread_id, 
               t.is_closed, t.is_deleted, t.is_waiting_on_customer,
               a.address_address, 
               t.ticket_mask, t.ticket_time_worked AS total_time_worked, ad.address_address AS requester_address, ad.address_banned, 
               q.queue_id, q.queue_name, q.queue_reply_to, c.name AS company_name, c.id AS company_id, t.ticket_date, 
               thr.thread_address_id AS requester_address_id, 
               t.min_thread_id, t.max_thread_id, th.thread_type AS max_thread_type, t.skill_count, UNIX_TIMESTAMP(th.thread_date) AS last_wrote_date,
               t.ticket_due AS due_date,
               sla.name as sla_name, sla.id as sla_id, c.sla_expire_date 
               FROM (ticket t, thread th, thread thr, address a, address ad) 
               LEFT JOIN queue q ON ( q.queue_id = t.ticket_queue_id )
               LEFT JOIN public_gui_users pu ON ( ad.public_user_id = pu.public_user_id )
               LEFT JOIN company c ON ( pu.company_id = c.id )
               LEFT JOIN sla ON ( c.sla_id = sla.id )
               WHERE t.max_thread_id = th.thread_id
               AND t.min_thread_id = thr.thread_id
               AND a.address_id = th.thread_address_id
               AND ad.address_id = thr.thread_address_id
               AND t.ticket_id IN ('%s') ";
      return $this->db->GetAll(sprintf($sql, $tickets));
   }

   /**
    * Get requesters for a ticket
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function requester_list($params) {  
      $ticket_id = 0+$params['ticket_id'];
      $sql = "SELECT r.suppress, a.address_id, a.address_address FROM requestor r LEFT JOIN address a USING (address_id) 
               WHERE ticket_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $ticket_id));
   }
   
   function requester_list_by_tickets($params) {
   	extract($params);
      $sql = "SELECT ticket_id, r.suppress, a.address_id, a.address_address FROM requestor r LEFT JOIN address a USING (address_id) 
               WHERE ticket_id IN (%s)";
      return $this->db->GetAll(sprintf($sql, implode(', ', $tickets)));
   }
   
   /**
    * Get watchers for a ticket
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function watcher_list($params) {  
      $ticket_id = 0+$params['ticket_id'];
//      $sql = "SELECT u.user_id, u.user_name, u.user_email FROM ticket t LEFT JOIN user u USING ( user_id ) WHERE queue_watch = 1 AND t.ticket_id = '%d'";
			$sql = "";
      return $this->db->GetAll(sprintf($sql, $ticket_id));
   }
   
   function watcher_list_by_tickets($params) {  
   	extract($params);
		$sql = "";
      return $this->db->GetAll(sprintf($sql, implode(', ',$tickets)));
   }
   
   /**
    * Flags by ticket id list
    *
    */
   function flag_list_by_tickets($params) {  
   	extract($params);
      $sql = "SELECT ticket_id, agent_id FROM `ticket_flags_to_agents` WHERE ticket_id IN (%s)";
      return $this->db->GetAll(sprintf($sql, implode(', ',$tickets)));
   }
   
//   function routing_list_by_tickets($params) {
//   	extract($params);
//   	$sql = "SELECT wst.`ticket_id`, wst.`team_id`, tm.team_name ".
//			"FROM `workstation_routing_to_tickets` wst ".
//			"LEFT JOIN `team` tm ON (wst.`team_id`=tm.`team_id`) ".
//			"WHERE wst.`ticket_id` IN (%s)";
//      return $this->db->GetAll(sprintf($sql, implode(', ',$tickets)));
//   } 
   
   function tag_list_by_tickets($params) {
   	extract($params);
   	$sql = "SELECT wst.`ticket_id`, wst.`tag_id`, tg.tag_name ".
			"FROM `workstation_tags_to_tickets` wst ".
			"LEFT JOIN `workstation_tags` tg ON (wst.`tag_id`=tg.`tag_id`) ".
			"WHERE wst.`ticket_id` IN (%s)";
      return $this->db->GetAll(sprintf($sql, implode(', ',$tickets)));
   }
   
   function spotlight_list_by_tickets($params) {
   	extract($params);
   	$sql = "SELECT sl.`ticket_id`, sl.`agent_id`, u.`user_name` ".
			"FROM `ticket_spotlights_to_agents` sl ".
			"LEFT JOIN `user` u ON (sl.`agent_id`=u.`user_id`) ".
			"WHERE sl.`ticket_id` IN (%s)";
      return $this->db->GetAll(sprintf($sql, implode(', ',$tickets)));
   }
   
   function add_tag($params) {
   	extract($params);

   	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
   	CerWorkstationTickets::addTagsToTicketId(array($tag_id),$ticket_id);

   	return TRUE;
   }

   function remove_tag($params) {
   	extract($params);
   	
   	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
   	CerWorkstationTickets::removeTagsFromTicketId(array($tag_id),$ticket_id);
   	
   	return TRUE;
   }
   
   function set_ticket_priority($params) {
   	extract($params);
   	$sql = "UPDATE ticket SET ticket_priority = %d WHERE ticket_id = %d";
   	return $this->db->Execute(sprintf($sql, $priority, $ticket_id));
   }
   
   function set_ticket_due($params) {
   	extract($params);
   	$sql = sprintf("UPDATE ticket set ticket_due = '%s' WHERE ticket_id = %d",
   	date("Y-m-d H:i:s",$due), $ticket_id);
   	
	return $this->db->Execute($sql);
   }
   
   function add_task($params) {
      extract($params);
      $sql = "INSERT INTO ticket_tasks (ticket_id, estimate, date_added, completed, title) VALUES ('%d', '%d', UNIX_TIMESTAMP(), 0, %s)";
      return $this->db->Execute(sprintf($sql, $ticket_id, $estimate, $this->db->qstr($title)));
   }
   
   /**
    * Flag/Unflag Tickets
    *
    */
   function flag_tickets($params) {
   	extract($params);
	//$tickets, $agent_id, $override
	
   	// Find out if anyone selected any of these tickets previously.
   	$sql = "SELECT ticket_id, agent_id FROM `ticket_flags_to_agents` WHERE ticket_id IN (%s)";
   	$res = $this->db->GetAll(sprintf($sql,implode(",",$tickets)));
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

	   	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		
	   	if(is_array($tickets_to_insert)) {
	   		foreach($tickets_to_insert as $ticket_id=>$ticket) {
   				CerWorkstationTickets::addFlagToTicket($agent_id,$ticket_id);
	   		}
	   	}
   	}
   	
   	return $flagged_by_others_only;
   }
   
   /**
    * Get requesters for a ticket
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_primary_contact_by_tickets($params) {  
   	extract($params);
      $sql = "SELECT t.ticket_id, CONCAT(pgu.name_first, ' ', pgu.name_last) AS name, pgu.public_user_id AS id FROM ticket t LEFT JOIN thread th ON (t.min_thread_id = th.thread_id)
      			LEFT JOIN address a ON (th.thread_address_id = a.address_id) INNER JOIN public_gui_users pgu USING (public_user_id) WHERE t.ticket_id IN (%s)";
      return $this->db->GetAll(sprintf($sql, implode(', ',$tickets)));
   }
   
   function get_ticket_steps($params) {
   		extract($params);
   		$sql = "SELECT ns.id, ns.ticket_id, ns.date_created, " .
   				"ns.note, ns.created_by_agent_id, u.user_name as created_by_agent_name ".
   				" FROM next_step ns ".
   				" LEFT JOIN user u ON (ns.created_by_agent_id = u.user_id) ".
   				" WHERE ns.ticket_id = %d ". 
   				"ORDER BY ns.date_created ASC ";//ns.date_completed, ns.date_created desc ";
   		
   		return $this->db->GetAll(sprintf($sql, $ticket_id));
   }
   
	function get_ticket_conflict_data($params) {
   		extract($params);
   		if(sizeof($tickets) == 0)
   			return array();
   		
   		$ticket_str = implode(', ', $tickets);
   		
   		$sql = "SELECT t.ticket_id, t.ticket_subject, u.user_id, u.user_name 
   		FROM ticket_flags_to_agents f
   		INNER JOIN ticket t ON f.ticket_id = t.ticket_id
   		INNER JOIN user u ON f.agent_id = u.user_id
   		WHERE f.ticket_id in (%s)
   		AND u.user_id != %d
   		ORDER BY t.ticket_id
   		";
   		//echo sprintf($sql, $ticket_str);exit();

   		$res = $this->db->GetAll(sprintf($sql, $ticket_str, $agent_id));
   		//print_r($res);exit();
   		
   		return $res;		
	}
	
	function set_status_awaiting_reply($params) {
		extract($params);
		
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::setTicketWaitingOnCustomer($ticket_id,1);
		
		return TRUE;
	}
	
	function get_ticket_requesters($params) {
		extract($params);
		$sql = " SELECT a.address_address, a.address_id, pgu.name_first, pgu.name_last, pgu.public_user_id, 
		c.id company_id, c.name company_name, 
		IF (r.address_id = th.thread_address_id, 1, 0) AS is_primary		
		FROM requestor r
		INNER JOIN address a ON r.address_id = a.address_id
		LEFT JOIN public_gui_users pgu ON a.public_user_id = pgu.public_user_id
		LEFT JOIN company c ON pgu.company_id = c.id
		INNER JOIN ticket t ON t.ticket_id = r.ticket_id
		INNER JOIN thread th ON t.min_thread_id = th.thread_id
		WHERE r.ticket_id = '%d' ";

		$result = $this->db->Execute(sprintf($sql, $ticket_id));
		$requesters=array();
		while (!$result->EOF)
		{
			$requesters[]= new requester($ticket_id, $result->fields['address_id'], $result->fields['address_address'], $result->fields['public_user_id'], $result->fields['name_first'], $result->fields['name_last'], $result->fields['company_name'], $result->fields['company_id'], $result->fields['is_primary']);
			$result->MoveNext();
		}
		return $requesters;			
		
	}
	
	function add_requesters($params) {
		extract($params); //$address_list, $ticket_id
		$addresses = "'". implode("','", $address_list) . "'";
		
		$sql = " SELECT address_id FROM address WHERE address_address IN (%s) ";
		$res = $this->db->GetAll(sprintf($sql, $addresses));

		$error_flag = false;
		if(is_array($res)) {
			foreach ($res AS $row) {
				$sql2 = "INSERT IGNORE INTO requestor (ticket_id, address_id) VALUES (?,?) ";
				$result = $this->db->Execute($sql2, array($ticket_id, $row['address_id']));
				if($result == FALSE)
					$error_flag = true;
			}
		}
		return ($error_flag !== TRUE); 	

	}	
	
	function delete_requesters($params) {
		extract($params); //$address_list, $ticket_id
		$addresses = "'". implode("','", $address_list) . "'";
		
		$sql = " SELECT address_id FROM address WHERE address_address IN (%s) ";
		$res = $this->db->GetAll(sprintf($sql, $addresses));
		
		$address_id_list = "";
		$first_time = true;
		$error_flag = false;
		if(is_array($res)) {
			foreach ($res AS $row) {
				if(!$first_time) 
					$address_id_list .= ',';
				$address_id_list .= "'" . $row['address_id'] . "'";
				$first_time = false;
			}
			
			if(trim($address_id_list) != "") {
				$sql = " DELETE FROM requestor WHERE ticket_id = '%d' AND address_id IN (%s)";
				$res = $this->db->Execute(sprintf($sql, $ticket_id, $address_id_list));
				if($res == FALSE) {
					$error_flag = true;
				}
			}
		}
		return ($error_flag !== TRUE);
	}
	
	function update_ticket_subject($params) {
		extract($params);//subject, ticket_id, status
		$sql = "UPDATE ticket set ticket_subject = ? WHERE ticket_id = ? ";
		$this->db->Execute($sql, array($subject, $ticket_id));
		
		$sql = "";
		switch($status) {
			case "open": {
				$sql = "UPDATE ticket set is_closed = 0, is_deleted = 0 WHERE ticket_id = ? ";
				break;
			}
			case "closed": {
				$sql = "UPDATE ticket set is_closed = 1, is_deleted = 0 WHERE ticket_id = ? ";
				break;
			}
			case "deleted": {
				$sql = "UPDATE ticket set is_closed = 1, is_deleted = 1 WHERE ticket_id = ? ";
				break;
			}
		}
		
		if(!empty($sql)) {
			$this->db->Execute($sql, array($ticket_id));
		}
		
		return TRUE;
	}
}