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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");

class slas_retriever
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	var $db;
	var $slas;
	var $schedules;
	var $error_message;
   
	function slas_retriever() {
		$this->error_message = "";
		
		$this->db =& database_loader::get_instance();
		$this->slas =& $this->get_result_set();

		$this->schedules = new cer_ScheduleHandler();
		//print_r($schedules);exit();
		if(!is_array($this->slas))  {
			$this->error_message = "Unable to obtain results";
		}
			
	}
	
	function get_result_set() {
		$result_data =& $this->db->get("sla", "get_slas", array());
		$slas = array();
		$current_sla_id = NULL;
		$current_team_id = NULL;
		foreach($result_data AS $row) {
			if($row['sla_id'] != $current_sla_id) {
				unset($sla);
				$sla->id = $row['sla_id'];
				$sla->name = $this->escape($row['sla_name']);
				$sla->teams = array();
				$current_sla_id = $row['sla_id'];
				$slas[] = $sla;
				$current_team_id = NULL;
			} 
			if($row['team_id'] != $current_team_id) {
				unset($team);
				$team->id = $row['team_id'];
				$team->name = $this->escape($row['team_name']);
				$team->response_time = $row['response_time'];
				$team->schedule = NULL;
				if($row['schedule_id'] != "") {
					$team->schedule->id = $row['schedule_id'];
					$team->schedule->sun_open= $row['sun_open'];
					$team->schedule->sun_close= $row['sun_close'];
					$team->schedule->mon_open= $row['mon_open'];
					$team->schedule->mon_close= $row['mon_close'];
					$team->schedule->tue_open= $row['tue_open'];
					$team->schedule->tue_close= $row['tue_close'];
					$team->schedule->wed_open= $row['wed_open'];
					$team->schedule->wed_close= $row['wed_close'];
					$team->schedule->thu_open= $row['thu_open'];
					$team->schedule->thu_close= $row['thu_close'];
					$team->schedule->fri_open= $row['fri_open'];
					$team->schedule->fri_close= $row['fri_close'];
					$team->schedule->sat_open= $row['sat_open'];
					$team->schedule->sat_close= $row['sat_close'];
					
					$team->schedule->sun_hrs = $row['sun_hrs'];
					$team->schedule->mon_hrs = $row['mon_hrs'];
					$team->schedule->tue_hrs = $row['tue_hrs'];
					$team->schedule->wed_hrs = $row['wed_hrs'];
					$team->schedule->thu_hrs = $row['thu_hrs'];
					$team->schedule->fri_hrs = $row['fri_hrs'];
					$team->schedule->sat_hrs = $row['sat_hrs'];
				}
				$sla->teams[] = $team;
				$current_team_id = $row['team_id'];
			}
		}
		
		
		return $slas;
	}
	
	function escape($str) {
		return stripslashes($str);
	}
	
	function get_slas() {
		return $this->slas;
	}	
}

