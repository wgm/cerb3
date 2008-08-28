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
|		Mike Fogg			(mike@webgroupmedia.com)		[MDF]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");

class CerWorkstationReports {
	
	function getList() {
		$db = cer_Database::getInstance();
		$reports = array();
		
		$sql = sprintf("SELECT `jasper_report_id`,`report_name`,`report_obj`,`scriptlet` ".
			"FROM `jasper_reports` ORDER BY `report_name`");
		$res = $db->query($sql);

		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$report = new CerWorkstationReport();
				$report->report_id = intval($row['jasper_report_id']);
				$report->report_title = stripslashes($row['report_name']);
				$report->report_blob = stripslashes($row['report_obj']);
				$report->scriptlet_blob = stripslashes($row['scriptlet']);
				$reports[$report->report_id] = $report;
			}
		}
		
		$sql = sprintf("SELECT `report_id`,`team_id` FROM `jasper_reports_acl`");
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$acl_report_id = intval($row['report_id']);
				$acl_team_id = intval($row['team_id']);
				
				if(isset($reports[$acl_report_id])) {
					$reports[$acl_report_id]->teams[$acl_team_id] = $acl_team_id;
				}
			}
		}
		
		return $reports;
	}

	function create($title,$data_file,$scriptlet_file='',$teams) {
		$db = cer_Database::getInstance();
		
		$sql = sprintf("INSERT INTO `jasper_reports` (`report_name`,`report_obj`,`scriptlet`) ".
			"VALUES (%s,'','')",
				$db->escape($title)
		);
		$db->query($sql);
		
		$report_id = $db->insert_id();

		CerWorkstationReports::save($report_id,$title,$data_file,$scriptlet_file,$teams);
		
		return $report_id;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return CerWorkstationReport
	 */
	function getById($id) {
		$reports = CerWorkstationReports::getList();
		if(isset($reports[$id])) return $reports[$id];

		return FALSE;
	}
	
	function save($id,$title="New Report",$data_file=null,$scriptlet_file=null,$teams=array()) {
		$db = cer_Database::getInstance();
		
		$fields = array();
		
		// Title
		$fields[] = sprintf("`report_name`=%s",
			$db->escape($title)
		);
		
		if(!empty($data_file)) {
			$data = file_get_contents($data_file);
			if(!$data) break;
			
			$fields[] = sprintf("`report_obj`=%s",
				$db->escape($data)
			);
		}
		if(!empty($scriptlet_file)) {
			$scriptlet = file_get_contents($scriptlet_file);
			if(!$scriptlet) break;
			
			$fields[] = sprintf("`scriptlet`=%s",
				$db->escape($scriptlet)
			);
		}
		
		$sql = sprintf("UPDATE `jasper_reports` SET %s WHERE `jasper_report_id` = %d ",
			implode(',', $fields),
			$id
		);
		$res = $db->query($sql);
		
		if(is_array($teams)) {
			// delete previous ACL
			$sql = sprintf("DELETE FROM `jasper_reports_acl` WHERE `report_id` = %d",
				$id
			);
			$db->query($sql);
			
			foreach($teams as $teamId => $team) {
				// insert ACL
				$sql = sprintf("INSERT INTO `jasper_reports_acl` (`report_id`,`team_id`) ".
					"VALUES (%d,%d) ",
						$id,
						$team
				);
				$db->query($sql);
			}
		}
		
		return TRUE;
	}
	
	function delete($id) {
		$db = cer_Database::getInstance();

		$sql = sprintf("DELETE FROM `jasper_reports` WHERE `jasper_report_id` = %d",
			$id
		);
		$db->query($sql);
	}
	
	/**
	 * Enter description here...
	 *
	 * @return CerWorkstationReports
	 */
	function getInstance() {
		static $instance = null;
		
		if(null == $instance) {
			$instance = new CerWorkstationReports();
		}
		
		return $instance;
	}
	
};

class CerWorkstationReport {
	var $report_id = 0;
	var $report_title = null;
	var $report_blob = null;
	var $scriptlet_blob = null;
	var $teams = array();
};