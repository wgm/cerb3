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

class jasper_report_saver
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	var $db;
	
	var $reportId;
	var $gid;
	var $name;
	var $summary;
	var $version;
	var $author;
	var $report_data;
	var $scriptlet;
	var $report_source;
	var $scriptlet_source;
	var $teams;
	
	var $status;
	
   
	function jasper_report_saver($reportId, $gid, $name, $summary, $version, $author, $report_data, $scriptlet, $teams, $report_source, $scriptlet_source) {
		$this->db =& database_loader::get_instance();
		$this->gid = $gid;
		$this->reportId = $reportId;
		
		if(!settype($this->reportId, "integer")) {
			$this->reportId = 0;
		}
		
		if(!settype($this->gid, "integer")) 
			$this->gid = 0;
		
		$this->name = $this->db->direct->qstr(trim($name));
		$this->summary = $this->db->direct->qstr(trim($summary));
		$this->version = trim($version);
		$this->author = $this->db->direct->qstr(trim($author));
		$this->report_data = $this->db->direct->qstr(base64_decode($report_data));
		$this->scriptlet = $this->db->direct->qstr(base64_decode($scriptlet));
		$this->report_source = $this->db->direct->qstr(base64_decode($report_source));
		$this->scriptlet_source = $this->db->direct->qstr(base64_decode($scriptlet_source));
		$this->teams = $teams;
	}
	
	function save_report() {
		//gettype($this->reportId);exit();
		//echo "-".is_int($this->reportId);exit();
		if($this->reportId != 0) {
			//we are updating an existing report (but not installing from report center catalog)
			
			$sql = "UPDATE jasper_reports SET report_name = " . $this->name . " " .
			", summary = " . $this->summary . 
			", author = " . $this->author . 
			", version = " . $this->db->direct->qstr($this->version) ;
			
			if($this->report_data != "''") {
				$sql .= ", report_obj = " . $this->report_data;
			}
			if($this->scriptlet != "''") {
				$sql .= ", scriptlet = " . $this->scriptlet;
			}
			if($this->report_source != "''") {
				$sql .= ", report_source = " . $this->report_source;
			}
			if($this->scriptlet_source != "''") {
				$sql .= ", scriptlet_source = " . $this->scriptlet_source;
			}			
			
			$sql .= " WHERE jasper_report_id = '" . $this->reportId . "'";
//			print_r($this->teams);exit();
//			echo $sql;exit();
			$this->db->direct->Execute($sql);
			$this->status = "updated";
			$this->set_team_permissions($this->reportId);
			return;
		}
		
		
		$existing_row = null;
		
		//at this point we are uploading a new report... or installing a report (which may or may not already exist)
		
		//if gid then we are installing a report (which may or may not already exist)
		if($this->gid != "" && $this->gid != 0 && $this->gid != null) {
			//get the version and local report id if this report already exists
			$existing_row = $this->get_existing_version();
		}
		
		if(!is_array($existing_row)) {
			//no existing report was found... put default values...we will insert a new report
			$existing_version = "";
			$report_id = 0;
		}
		else {
			//an existing report exists... store the version and report id for use later
			$existing_version = $existing_row['version'];
			$report_id = $existing_row['jasper_report_id'];
		}
		
		if($existing_version == "") {
			// this is a newly uploaded report, or we are installing a report which hasn't been installed before
			
			$insertFields = array("report_name"=>$this->name, "report_obj"=>$this->report_data, "summary"=>$this->summary, 
			"version"=>$this->db->direct->qstr($this->version), "author"=>$this->author, "category_id"=>1);
			
			if($this->scriptlet != "") {
				$insertFields['scriptlet'] = $this->scriptlet;
			}
			if($this->gid != "") {
				$insertFields['guid'] = $this->gid;
			}
			if($this->report_source != "") {
				$insertFields['report_source'] = $this->report_source;
			}
			if($this->scriptlet_source != "") {
				$insertFields['scriptlet_source'] = $this->scriptlet_source;
			}
			
			$fieldStr = implode(',', array_keys($insertFields));
			$valueStr = implode(',', $insertFields);
			
			$sql = sprintf(" INSERT INTO jasper_reports (%s) VALUES (%s) ", $fieldStr, $valueStr);
			
			//echo $sql;exit();
			$this->db->direct->Execute($sql);
			$report_id = $this->db->direct->insert_id();
			$this->status = "inserted";
			
		}
		elseif($existing_row['version'] < $this->version) {
			//this report is a report center catalog report (has a guid) and the we are installing a newer version
			//...so overwrite.

			$sql = "UPDATE jasper_reports SET report_name = " . $this->name . " " .
			", report_obj = " . $this->report_data . 
			", scriptlet = " . $this->scriptlet . 
			", summary = " . $this->summary . 
			", version = " . $this->db->direct->qstr($this->version) . 
			", author = " . $this->author . 
			", report_source = " . $this->report_source . 
			", scriptlet_source = " . $this->scriptlet_source .
			 " WHERE guid = '" . $this->gid . "'";
			//echo $sql;
			
			$this->db->direct->Execute($sql);
			$this->status = "updated";
		}
		else {
			//echo "same report, old or same version";
			$this->status = "ignored";
		}
		
		//set up the team permissions for this report 
		$this->set_team_permissions($report_id);
		
	}
	
	function set_team_permissions($report_id) {
		//set up the team permissions for this report 
		if(is_array($this->teams)) {
			// delete previous ACL
			$sql = sprintf("DELETE FROM `jasper_reports_acl` WHERE `report_id` = %d",
				$report_id
			);
			$this->db->direct->Execute($sql);
			
			
			foreach($this->teams as $team_id) {
				// insert ACL
				$sql = sprintf("INSERT INTO `jasper_reports_acl` (`report_id`,`team_id`) ".
					"VALUES (%d,%d) ",
						$report_id,
						$team_id
				);
				$this->db->direct->Execute($sql);
			}		
		}		
	}
	
	function get_existing_version() {
		$sql = sprintf("SELECT version, jasper_report_id FROM jasper_reports WHERE guid = '%d'", $this->gid);
		$row = $this->db->direct->GetRow($sql);
		
		return $row;
	}

	
	function get_status() {
		return $this->status;
	}
	
}

