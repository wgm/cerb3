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
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/reports_tags.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the visitor referrer report
 *
 */
class get_tag_report_handler extends xml_parser
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
	* @return get_referrer_report_handler
	*/
	function get_tag_report_handler(&$xml) {
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
		
		if($start_date == "") {
			$start_date = null;
		}
		if($end_date == "") {
			$end_date = null;
		}
		
		$this->report_obj =& new reports_tags($start_date, $end_date);
		
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

		$report->add_child("title", xml_object::create("title", "Tags Report"));
		
		$report_tags =& $report->add_child("report_tags", xml_object::create("report_tags"));
		//print_r($this->report_obj);exit();
		foreach ($this->report_obj->report_data AS $val) {
			$report_tag =& $report_tags->add_child("report_tag", xml_object::create("report_tag", NULL, array("id"=>$val['tag_id'])));
			$report_tag->add_child("name", xml_object::create("name", $val['tag_name']));
			$report_tag->add_child("ticket_count", xml_object::create("ticket_count", $val['ticket_count']));
		}
		
		xml_output::success();
	}
}