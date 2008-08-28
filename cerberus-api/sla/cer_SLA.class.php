<?php

require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

class cer_SLA {
	
	var $db = null;
	var $queue_gated = array();
	var $plans = array();
	var $requester_to_sla_hash = array();  // [JAS]: Keep ourselves from getting too redundant in one page load, since the SLA process is linear.
	
	function cer_SLA($ids=array()) {
		$this->db = cer_Database::getInstance();
		$this->_loadQueueModesCache();
		$this->_loadSLAPlansById($ids);
	}
	
	function _loadQueueModesCache() {
		$sql = "SELECT q.queue_id, q.queue_mode ".
			"FROM queue q ";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$this->queue_gated[$row["queue_id"]] = $row["queue_mode"];
			}
		}
	}
	
	function _loadSLAPlansById($ids=array()) {
		if(!is_array($ids)) return false;
		
		CerSecurityUtils::integerArray($ids);
		
		$sql = sprintf("SELECT s.id, s.name ".
				"FROM sla s ".
				"%s ".
				"ORDER BY s.name ",
					((!empty($ids)) ? "WHERE id IN (" . implode(",", $ids) . ")" : "")
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$sla_plan = new cer_SLAPlan();
					$sla_plan->sla_id = $row["id"];
					$sla_plan->sla_name = stripslashes($row["name"]);
				$this->plans[$row["id"]] = $sla_plan;
			}
		}
		
		$sql = sprintf("SELECT sq.sla_id, sq.queue_id, sq.schedule_id, sq.response_time, q.queue_name, q.queue_mode, sch.schedule_name ".
				"FROM (sla_to_queue sq, queue q) ".
				"LEFT JOIN schedule sch ON (sch.schedule_id = sq.schedule_id) ".
				"WHERE sq.queue_id = q.queue_id ".
				"%s ".
				"ORDER BY q.queue_name ",
					((!empty($ids)) ? "AND sq.sla_id IN (" . implode(",", $ids) . ")" : "")
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$this->plans[$row["sla_id"]]->queues[$row["queue_id"]] = 
					new cer_SLAPlanQueue($row["queue_id"], $row["queue_name"], $row["queue_mode"], $row["schedule_id"], $row["schedule_name"], $row["response_time"]);
			}
		}
	}
	
	function queueIsGated($qid) {
		if($this->queue_gated[$qid])
			return true;
			
		return false;
	}
	
	function requesterIdHasKeytoGatedQueue($requester_id,$queue_id) {
		
		if($sla_id = $this->getSlaIdForRequesterId($requester_id)) {
			$this->requester_to_sla_hash[$requester_id] = $sla_id;
			
			if(isset($this->plans[$sla_id]->queues[$queue_id])) {
				return true;
			}
		}
		
		return false;
	}

	function getSlaIdForRequesterId($requester_id) {
		$sql = sprintf("SELECT c.sla_id, c.sla_expire_date ".
				"FROM (address a, public_gui_users pu, company c) ".
				"WHERE a.public_user_id = pu.public_user_id ".
				"AND (c.sla_expire_date = '0000-00-00 00:00:00' OR c.sla_expire_date > NOW() ) ". // and SLA not expired
				"AND pu.company_id = c.id ".
				"AND a.address_id = %d",
					$requester_id
			);
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res)) {
			$this->requester_to_sla_hash[$requester_id] = $row["sla_id"];
			return $row["sla_id"];
		}
		else {
			return false;
		}
	}
	
	function getDueDateForRequesterOnQueue($requester_id, $queue_id) {
		if(!$sla_id = @$this->requester_to_sla_hash[$requester_id]) {
			if(!$sla_id = $this->getSlaIdForRequesterId($requester_id)) { 
				// no SLA plan
			}
		}
		
		// [JAS]: Set up the queue defaults first
		if($tmp = $this->getQueueDefaultDue($queue_id)) {
			$response_time = $tmp["queue_default_response_time"];
			$schedule_id = $tmp["queue_default_schedule"];
		}
		
		// [JAS]: If we had an SLA plan, use its schedule + response times to make a due date
		//	override only if the values exist for that queue
		if($sla_id) {
			$sla_resp = @$this->plans[$sla_id]->queues[$queue_id]->queue_response_time;
			if($sla_resp) $response_time = $sla_resp;
			
			$sla_sched = @$this->plans[$sla_id]->queues[$queue_id]->queue_schedule_id;
			if($sla_sched) $schedule_id = $sla_sched;
		}
		
		// otherwise, use the queues defaults or the system default
		if(empty($response_time)) {
			$cfg = CerConfiguration::getInstance();
			$response_time = $cfg->settings["overdue_hours"];
		}
		
		// [JAS]: If after all that work we still failed to get a due date (somehow)...
		if(empty($response_time) && $response_time !== 0) { // Somebody may *want* it to equal zero
			$response_time = rand(10,14); // HAL.. gimme half a 24h day, give or take 2 hrs
		}
		
		// turn time + schedule into a physical due date
		$due_mktime = cer_ScheduleHandler::mktimeDueDateHoursFromSchedule($response_time, $schedule_id);
		
		// return the new due date
		return $due_mktime;
	}
	
	function getQueueDefaultDue($queue_id) {
		$sql = sprintf("SELECT q.queue_default_response_time, q.queue_default_schedule ".
			"FROM queue q ".
			"WHERE q.queue_id = %d",
				$queue_id
			);
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res)) {
			return array(
					"queue_default_response_time" => $row["queue_default_response_time"],
					"queue_default_schedule" => $row["queue_default_schedule"]
				);
		}
		
		return false;
	}
	
};

class cer_SLAPlan
{
	var $sla_id = 0;
	var $sla_name = null;
	var $queues = array();
};

class cer_SLAPlanQueue
{
	var $queue_id = null;
	var $queue_name = null;
	var $queue_mode = null;
	var $queue_schedule_id = null;
	var $queue_schedule_name = null;
	var $queue_response_time = 0;
	
	function cer_SLAPlanQueue($qid,$queue_name,$queue_mode,$schedule_id,$schedule_name,$response_time) {
		$this->queue_id = $qid;
		$this->queue_name = $queue_name;
		$this->queue_mode = $this->_setQueueMode($queue_mode);
		$this->queue_schedule_id = $schedule_id;
		$this->queue_schedule_name = $schedule_name;
		$this->queue_response_time = $response_time;
	}
	
	function _setQueueMode($mode) {
		$val = null;
		
		switch($mode) {
			case 0:
				$val = "Open";
				break;
			case 1:
				$val = "Gated";
				break;
		}
		
		return $val;
	}
};

?>