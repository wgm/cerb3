<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

class cer_QueueHandler {
	var $db = null;
	var $queues = array();
	var $total_active_tickets = 0;
	
	function cer_QueueHandler($ids=array()) {
		$this->db = cer_Database::getInstance();
		$this->_loadQueuesById($ids);
	}
	
	/**
	 * Enter description here...
	 *
	 * @return cer_QueueHandler
	 */
	function getInstance() {
		static $instance = NULL;
		
		if($instance == NULL) {
			$instance = new cer_QueueHandler();
		}
		
		return $instance;
	}
	
	function getQueues() {
		if(is_array($this->queues))
			return $this->queues;
		else
			return array();
	}
	
	function getQueueAddresses() {
		$blockedAddys = array();
		
		// [JAS]: Build a list of every queue address
		$sql = "SELECT qa.queue_addresses_id, LOWER(CONCAT(qa.queue_address,'@',qa.queue_domain)) as addy, qa.queue_id FROM queue_addresses qa";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$addy = new CerQueueAddress();
				$addy->address = stripslashes($row["addy"]);
				$addy->address_id = intval($row["queue_addresses_id"]);
				$addy->queue_id = intval($row["queue_id"]);
				$blockedAddys[$row["addy"]] = $addy;
			}
		}
		
		return $blockedAddys;
	}
	
	function _loadQueuesById($ids=array()) {
		$q_ids = null;
		
		if(!is_array($ids)) return false;
		
		CerSecurityUtils::integerArray($ids);
		
		$sql = sprintf("SELECT q.queue_id, q.queue_name, q.queue_reply_to, q.queue_mode, q.queue_default_schedule, q.queue_default_response_time  ".
				"FROM queue q ".
				"ORDER BY q.queue_name "
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$new_queue = new cer_Queue();
					$new_queue->queue_id = $row["queue_id"];
					$new_queue->queue_name = stripslashes($row["queue_name"]);
					$new_queue->queue_mode = $row["queue_mode"];
					$new_queue->queue_schedule_id = $row["queue_default_schedule"];
					$new_queue->queue_response_time = $row["queue_default_response_time"];
					$new_queue->queue_reply_to = stripslashes($row["queue_reply_to"]);
				$this->queues[$new_queue->queue_id] = $new_queue;
			}
		}
		
		$this->_loadQueueAddresses();
		$this->_loadQueueLoads();
	}
	
	function _loadQueueLoads() {
		$sql = sprintf("SELECT ticket_queue_id, count(*) as hits FROM ticket WHERE is_closed = 0 AND is_deleted = 0 AND is_waiting_on_customer = 0 GROUP BY ticket_queue_id"
		);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$queue_id = intval($row['ticket_queue_id']);
				$queue_hits = intval($row['hits']);
				
				if(isset($this->queues[$queue_id])) {
					$this->queues[$queue_id]->active_tickets = $queue_hits;
					$this->total_active_tickets += $queue_hits;
				}
			}
		}
	}
	
	// [JAS]: First pass: Load up a queues addresses from the database.
	function _loadQueueAddresses() {
		if(empty($this->queues))
			return;
		
		$qids = array_keys($this->queues);
		
		CerSecurityUtils::integerArray($qids);
		
		$sql = sprintf("SELECT qa.queue_addresses_id, qa.queue_address, qa.queue_domain, qa.queue_id ".
					"FROM queue_addresses qa ".
					"WHERE qa.queue_id IN (%s)",
						implode(",", $qids)
				);
		$res = $this->db->query($sql);
		
		// [JAS]: First pass
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$this->queues[$row["queue_id"]]->queue_addresses[$row["queue_addresses_id"]] = 
					sprintf("%s@%s",
						stripslashes($row["queue_address"]),
						stripslashes($row["queue_domain"])
					);
			}
		}
		
	}
	
};

class cer_Queue {
	var $queue_id = null;
	var $queue_name = null;
	var $queue_mode = null;
	var $queue_schedule_id = null;
	var $queue_response_time = null;
	var $queue_reply_to = null;
	var $queue_addresses = array();
	var $active_tickets = 0;
};

class CerQueueAddress {
	var $queue_id = null;
	var $address = null;
	var $address_id = null;
}

?>