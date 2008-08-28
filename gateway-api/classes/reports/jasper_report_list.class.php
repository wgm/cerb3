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

class jasper_report_list
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	var $db;
   
	function jasper_report_list() {
		$this->db =& database_loader::get_instance();
		
	}
	
	function get_report_list() {

		$sql = "SELECT distinct jasper_report_id, guid, report_name, summary, version, author, jc.category_id, 
			jc.name category_name, if(isnull(report_source) or report_source='', '0','1') has_report_source,
			if(isnull(scriptlet_source) or scriptlet_source='', '0','1') has_scriptlet_source,
			if(isnull(report_obj) or report_obj='', '0','1') has_report,
			if(isnull(scriptlet) or scriptlet='', '0','1') has_scriptlet,
			jra_all.team_id
			FROM jasper_reports jr 
			INNER JOIN jasper_reports_acl jra ON jr.jasper_report_id = jra.report_id 
			INNER JOIN team_members m ON jra.team_id = m.team_id
			INNER JOIN jasper_reports_acl jra_all ON jr.jasper_report_id = jra_all.report_id
			INNER JOIN jasper_report_categories jc ON jr.category_id = jc.category_id
			WHERE m.agent_id = '%d'
			ORDER BY category_id ";

		$result = $this->db->direct->GetAll(sprintf($sql, general_users::get_user_id()));

		$report_list = array();
		$currentId = "";
		$j=-1;
		for ($i=0; $i < sizeof($result); $i++) {
			if($result[$i]['jasper_report_id'] != $currentId) {
				
				unset($tmp);
				$tmp = $result[$i];
				$tmp['teams'] = array();
				
				$report_list[++$j] = $tmp;
				
				$currentId = $result[$i]['jasper_report_id'];
			}
			
			$report_list[$j]['teams'][] = $result[$i]['team_id'];
		}
		
		//print_r($report_list);exit();
		return $report_list;
	}	
}

