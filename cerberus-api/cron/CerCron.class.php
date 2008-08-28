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
require_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCronTask.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");

define("CER_CRON_MODE_MANUAL",0);
define("CER_CRON_MODE_INTERNAL",1);
define("CER_CRON_MODE_EXTERNAL",2);

class CerCron {
	var $valid_ips = array();
	var $tasks = array();
	var $_lockTime = 0;
	var $_pollMode = 0;
	
	/**
	* @return CerCron
	* @desc 
	*/
	function CerCron() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
//		 [JAS]: Standard settings
		$sql = "SELECT `cron_poll_mode`,`lock_time` FROM `cron_settings` LIMIT 0,1";
		$res = $db->query($sql);
		
		if($row = $db->grab_first_row($res)) {
			$this->setPollMode(@$row["cron_poll_mode"]);
			$this->setLockTime(@$row["lock_time"]);
		}
		
		// [JAS]: Valid IPs
		$sql = "SELECT `ip_mask` FROM `cron_valid_ips`";
		$res = $db->query($sql);

		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$this->valid_ips[] = @$row["ip_mask"];
		}
		
		// [JAS]: Tasks
		$sql = "SELECT `id`,`enabled`,`minute`,`hour`,`day_of_month`,`day_of_week`,`title`,`script`,`next_runtime`,`last_runtime` ".
			"FROM `cron_tasks` ";
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$task = $this->_getTaskObjFromRow($row);
				$this->tasks[] = $task;
			}
		}
	}

	function _getTaskObjFromRow($row) {
		$task = new CerCronTask();
			$task->setId($row["id"]);
			$task->setEnabled($row["enabled"]);
			$task->setMinute($row["minute"]);
			$task->setHour($row["hour"]);
			$task->setDayOfMonth($row["day_of_month"]);
			$task->setDayOfWeek($row["day_of_week"]);
			$task->setTitle(stripslashes($row["title"]));
			$task->setScript(stripslashes($row["script"]));
			$task->setLastRuntime($row["last_runtime"]);
			$task->setNextRuntime($row["next_runtime"]);
			
		return $task;
	}
	
	function getTaskById($tid) {
		settype($id,"integer");
		
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		// [JAS]: Tasks
		$sql = sprintf("SELECT `id`,`enabled`,`minute`,`hour`,`day_of_month`,`day_of_week`,`title`,`script`,`next_runtime`,`last_runtime` ".
			"FROM `cron_tasks` ".
			"WHERE `id` = %d",
				$tid
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			$row = $db->fetch_row($res);
			$task = $this->_getTaskObjFromRow($row);
			return $task;
		}
		
		return NULL;
	}
	
	function saveTask(&$task) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		/* @var $task CerCronTask */
		if(!is_a($task,"cercrontask"))
			return FALSE;

		// [JAS]: Do we need to insert a row first?
		if($task->getId() == "0") {
			$sql = "INSERT INTO `cron_tasks` (`title`) VALUES ('')";
			$db->query($sql);
			$newId = $db->insert_id();
			if(empty($newId))
				return FALSE;
				
			$task->setId($newId);
		}

		$sql = sprintf("UPDATE `cron_tasks` SET ".
			"`enabled`=%d, ".
			"`minute`=%s, ".
			"`hour`=%s, ".
			"`day_of_month`=%s, ".
			"`day_of_week`=%s, ".
			"`title`=%s, ".
			"`script`=%s, ".
			"`last_runtime`=%d, ".
			"`next_runtime`=%d ".
			"WHERE `id` = %d",
				$task->getEnabled(),
				$db->escape($task->getMinute()),
				$db->escape($task->getHour()),
				$db->escape($task->getDayOfMonth()),
				$db->escape($task->getDayOfWeek()),
				$db->escape($task->getTitle()),
				$db->escape($task->getScript()),
				$task->getLastRuntime(),
				$task->getNextRuntime(),
				$task->getId()
		);
		$db->query($sql);
	}
	
	function deleteTaskId($id) {
		settype($id,"integer");
		
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("DELETE FROM `cron_tasks` WHERE `id` = %d",
			$id
		);
		$db->query($sql);
	}
	
	/**
	 * Experimental
	 * [TODO] This could probably be done cleaner
	 *
	 * @param CerCronTask $task
	 * @return int
	 */
	function calculateNextRuntime($task) {
		/* @var $task CerCronTask */
		if(!is_a($task,"cercrontask"))
			return FALSE;
		
		$secsToAdd = 0;
		
//		$task->setDayOfWeek(5);
//		$task->setDayOfMonth("*");
//		$task->setHour("*");
//		$task->setMinute("40");

		$weekDay = $task->getDayOfWeek();
		$monthDay = $task->getDayOfMonth();
		$hourNum = $task->getHour();
		$minStr = $task->getMinute();
		
		// [JAS]: One or the other here
		if("*" != $weekDay) {
			// Add the number of days til midnight on this day of week
			
			// [JAS]: If the same day as today, get a week from now
			if($task->getDayOfWeek() == date("w"))
				$dayPrefix = "Next ";
				
			$secsTil = strtotime($dayPrefix . cer_DateTimeFormat::getDayAsString($weekDay));
			$secsDiff = mktime(0,0,0,date("m",$secsTil),date("d",$secsTil),date("Y",$secsTil)) - mktime(); //midnight
			$secsToAdd += $secsDiff;
			unset($secsTil);
			unset($secsDiff);
		} elseif ("*" != $monthDay) {
			// Add the number of seconds til midnight on this day of month
			$secsTil = strtotime(date(sprintf("Y-m-%d 00:00:00",$monthDay)),mktime());

			// If we're on or passed the day already, we want next month
			if($monthDay <= date("d"))
				$secsTil = strtotime("+1 month",$secsTil);
			
			$secsDiff = $secsTil - mktime();
			$secsToAdd += $secsDiff;
			unset($secsTil);
			unset($secsDiff);
		}
		
		$newHour = "";
		$newMin = "";
		
		if("*" != $hourNum) {
			// If we're still in today, and this hour is passed, we mean tomorrow
			if((date("Y-m-d",mktime()+$secsToAdd) == date("Y-m-d")) // today
				&& (date("G") >= $hourNum)
				&& (substr($minStr,0,1)=="*") // wildcards
				) { // hour passed
					$secsTil = strtotime("+1 day", mktime()+$secsToAdd);
					$secsDiff = $secsTil - mktime();
					$secsToAdd += $secsDiff;
			}
			$newHour = $hourNum;
			unset($secsTil);
			unset($secsDiff);
		}
		
		if("*" == $newHour || "" == $newHour) {
			$newHour = date("H",mktime() + $secsToAdd);
		}
		
		if("*" != $minStr) {
			if(substr($minStr,0,2) == "*/") { // div
				$minDiv = substr($minStr,2);
				if($minDiv > 30) { // [JAS]: We won't divide into 60 more than once over 31
					$newMin = $minDiv;
				} else {
					$minSteps = floor(60 / $minDiv);
					$futureMin = date("i",mktime()+$secsToAdd);
					for($x=1;$x<=$minSteps;$x++) {
						if(($x*$minDiv) > $futureMin) {
							$newMin = $x*$minDiv;
							if($newMin == 60) {
								$newMin="0";
								$newHour++;
							}
							break;
						}
					}
				}
			} else {
				$newMin = $minStr;
				
				// [JAS]: If the dates are the same, same hour, and the minute is passed
				if((date("Y-m-d",mktime()+$secsToAdd) == date("Y-m-d")) // same day
					&& $newHour == date("G") // same hour
					&& (date("i") >= sprintf("%02d",$newMin)) // min passed
					) { 
						if($hourNum == "*") { // wildcards, +1hr
							$newHour++;
						} else { // fixed, +1 day
							$secsTil = strtotime("+1 day", mktime()+$secsToAdd);
							$secsDiff = $secsTil - mktime();
							$secsToAdd += $secsDiff;
						}
				}
			}
			
		}
		
		$futureSecs = mktime() + $secsToAdd;
		
		if("*" == $newMin || "" == $newMin) {
			$newMin = date("i",$futureSecs);
		}
		
		$futureDate = mktime(
			$newHour,
			$newMin,
			0,
			date("m",$futureSecs),
			date("d",$futureSecs),
			date("Y",$futureSecs)
		);
		
		// Add at least a minute
		if(($futureDate - mktime()) < 60) {
			$futureDate = mktime() + 60;
		}
		
		return $futureDate;
	}
	
	/**
	* @return void
	* @param array $ips
	* @desc 
	*/
	function setValidIps($ips) {
		if(is_string($ips)) {
			$ips = str_replace(array("\r"," "),"",$ips);
			$this->valid_ips = split("\n",$ips);
		} elseif(is_array($ips)) {
			$this->valid_ips = $ips;	
		}
		
		unset($ips);
	}
	
	/**
	* @return array
	* @desc 
	*/
	function getValidIps() {
		if(!is_array($this->valid_ips))
			return array();
			
		return $this->valid_ips;
	}
	
	function setPollMode($mode) {
		settype($mode,"integer");
		$this->_pollMode = $mode;
	}
	
	function getPollMode() {
		return $this->_pollMode;
	}

	function setLockTime($time) {
		settype($time,"integer");
		$this->_lockTime = $time;
	}
	
	function getLockTime() {
		return $this->_lockTime;
	}
	
	function _get_active_sessions() {
		$cfg = CerConfiguration::getInstance();
		$db = cer_Database::getInstance();
		
		$sql = sprintf("SELECT s_id, session_id, session_ip, session_timestamp ".
			"FROM `session` ".
			"WHERE UNIX_TIMESTAMP(`session_timestamp`) > (UNIX_TIMESTAMP(NOW())-%d)",
			$cfg->settings['session_lifespan']
		);
		$res = $db->query($sql);
		
		$sessions = array();
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$session = array();
				$session['id'] = stripslashes($row['session_id']);
				$session['ip'] = stripslashes($row['session_ip']);
				$session['heartbeat'] = $row['session_timestamp'];
				$sessions[] = $session;
			}
		}
		
		return $sessions;
	}
	
	/**
	* @return boolean
	* @param string $ip
	* @desc Tests if an IP should pass session security
	*/
	function isValidIp($ip) {
		global $session;
		
		// [JAS]: Test logged in IPs first
		$sessions = $this->_get_active_sessions();
		if(is_array($sessions))
		foreach($sessions as $s) {
			if($s['ip'] == $ip)
				return true;
		}
		
		// [JAS]: Test all our IPs for a wildcard match
		if(is_array($this->valid_ips))
		foreach($this->valid_ips as $mask) {
			if(empty($mask)) continue;
			if(0 == strcmp(substr($ip,0,strlen($mask)),$mask)) {
				return true;
			}
		}
			
		return false;
	}
	
	/**
	* @return void
	* @desc Saves settings to the database
	*/
	function saveSettings() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "DELETE FROM `cron_settings`";
		$db->query($sql);
		$sql = "DELETE FROM `cron_valid_ips`";
		$db->query($sql);
		
		settype($this->_pollMode, "integer");
		
		$sql = sprintf("INSERT INTO `cron_settings` (`cron_poll_mode`,`lock_time`) VALUES ('%d','%d')",
			$this->_pollMode,
			$this->_lockTime
		);
		$db->query($sql);
		
		if(is_array($this->valid_ips)) {
			foreach($this->valid_ips as $ip) {
				$sql = sprintf("INSERT INTO `cron_valid_ips` (`ip_mask`) VALUES (%s)",
					$db->escape($ip)
				);
				$db->query($sql);
			}
		}
		
		unset($sql);
	}
	
};