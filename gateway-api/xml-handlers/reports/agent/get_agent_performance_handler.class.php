<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/reports_agent_performance.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the visitor referrer report
 *
 */
class get_agent_performance_handler extends xml_parser
{
	/**
	* XML data packet from client GUI
	*
	* @var object
	*/
	var $xml;
	var $report_obj;
   
	/**
	* Class constructor
	*
	* @param object $xml
	*/
	function get_agent_performance_handler(&$xml) {
		$this->xml =& $xml;
	}

	/**
	* main() function for this class. 
	*
	*/
	function process() {
		$users_obj =& new general_users();
		if($users_obj->check_login() === FALSE) {
			xml_output::error(0, 'Not logged in. Please login before proceeding!');
		}

		$date_range_elm =& $this->xml->get_child("date_range",0);

		$start_date = $date_range_elm->get_attribute("start", FALSE);
		$end_date = $date_range_elm->get_attribute("end", FALSE);
		
		$agents_elm =& $this->xml->get_child("agents",0);
		if($agents_elm == null) {
			xml_output::error(0, 'No agents specified!');
			return;
		}
		$agent_elms =& $agents_elm->get_children("agent");
		$agentIds = array();
		if(is_array($agent_elms)) {
			foreach($agent_elms AS $agent_elm) {
				$agentIds[]  = $agent_elm->get_attribute("id", FALSE);
			}
		}
		
		$first_day_of_week = $this->xml->get_child_data("first_day_of_week");
		
		if($start_date == "") {
			$start_date = null;
		}
		if($end_date == "") {
			$end_date = null;
		}
		
		$date_group_type = $this->xml->get_child_data("date_group_type");
		
		$this->report_obj = new reports_agent_performance($start_date, $end_date, $date_group_type, $first_day_of_week, $agentIds);
		
      if($report_obj->error_message != "") {
			xml_output::error(0, 'Failed to get visitor referrer report!');
		}
		else {
			$this->output_xml();
			//xml_output::success();
		}
	}
   
	function output_xml() {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$report =& $data->add_child("report", xml_object::create("report"));

		$report->add_child("title", xml_object::create("title", "Agent Performance Report"));
		
		$report_performance_elm =& $report->add_child("report_performance", xml_object::create("report_performance"));
		//print_r($this->report_obj);exit();
		$currentDate=NULL;
		$currentAgent=NULL;
		$date_period_elm = NULL;
		$agent_elm = NULL;
		$agents_elm = NULL;
		if(is_array($this->report_obj->report_data)) {
			foreach ($this->report_obj->report_data AS $val) {
				if($currentDate != $val['ticket_date']) {
					$date_period_elm =& $report_performance_elm->add_child("date_period", xml_object::create("date_period"));
					$date_period_elm->add_child("date", xml_object::create("date", $val['ticket_date']));
					$agents_elm =& $date_period_elm->add_child("agents", xml_object::create("agents"));
					$currentDate = $val['ticket_date'];
					$currentAgent = NULL;
				}
				if($currentAgent != $val['user_id']) {
					$agent_elm =& $agents_elm->add_child("agent", xml_object::create("agent", NULL, array("id"=>$val['user_id']) ));
					$currentAgent = $val['user_id'];
				}
				$agent_elm->add_child("name", xml_object::create("name", $val['user_name']));
				$agent_elm->add_child("reply_count", xml_object::create("reply_count", $val['ticket_count']));
			}
		}
		
		xml_output::success();
	}
}