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

class CerWorkstationSla {
	
	var $plans = array();
	
	/**
	* @return CerWorkstationSla
	* @desc 
	*/
	function CerWorkstationSla() {
		$this->_loadPlans();
	}

	function _loadPlans() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$this->plans = array();
		
		$sql = "SELECT sl.`id` as sla_id, sl.`name` as sla_name ".
			"FROM `sla` sl ";
		$res = $db->query($sql);
		
		if(!$db->num_rows($res))
			return;
		
		while($row = $db->fetch_row($res)) {
			$plan = new stdClass();
			$plan->name = stripslashes($row['sla_name']);
			$plan->teams = array();
			$this->plans[$row['sla_id']] = $plan;
		}
		
		$sql = "SELECT sl.`sla_id`, sl.`team_id`, sl.`schedule_id`, sl.`response_time` ".
			"FROM `sla_to_team` sl ";
		$res = $db->query($sql);

		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$sla_id = $row['sla_id'];
				$schedule_id = $row['schedule_id'];
				$response_time = $row['response_time'];
				$team_id = $row['team_id'];
				
				if(!isset($this->plans[$sla_id]))
					continue;

				$plan =& $this->plans[$sla_id];
				
				$team = new stdClass();
				$team->team_id = $team_id;
				$team->schedule_id = $schedule_id;
				$team->response_time = $response_time;
				$team->sla_id = $sla_id;
				
				$plan->teams[$team_id] = $team;
			}
			
		}
		
		return true;
	}
	
	function refresh() {
		$this->_loadPlans();
	}
	
	function scheduleDueDate($hours,$schedule_id) {
		$time = cer_ScheduleHandler::mktimeDueDateHoursFromSchedule($hours,$schedule_id);
		return $time;
	}
	
	function saveSlaTeam($sla_id,$team_id,$team_schedule_id,$team_response_time) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("REPLACE INTO `sla_to_team` (`sla_id`,`team_id`,`schedule_id`,`response_time`) ".
			"VALUES(%d,%d,%d,%d)",
				$sla_id,
				$team_id,
				$team_schedule_id,
				$team_response_time
		);
		$db->query($sql);
	}
	
	function slaHasTeam($sla_id, $team_id) {
		if(empty($this->plans) || !is_array($this->plans) || empty($sla_id) || empty($team_id))
			return false;

		if(!isset($this->plans[$sla_id]))
			return false;
			
		foreach($this->plans[$sla_id]->teams as $b_team_id => $team) {
			if($b_team_id == $team_id)
				return true;
		}
		
		return false;
	}
	
	function getPlans() {
		if(is_array($this->plans))
			return $this->plans;
		else 
			return array();
	}
	
}