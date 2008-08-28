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

class reports_tags
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	var $db;
	var $date_start;
	var $date_end;
	var $report_data;
	var $error_message;
   
	function reports_tags($start_date, $end_date) {

		$this->error_message = "";
		
		$this->db =& database_loader::get_instance();
		
		$this->date_start = $start_date;
		$this->date_end = $end_date;
		
		$this->report_data =& $this->get_result_set();
		
		if(!is_array($this->report_data))  {
			$this->error_message = "Unable to obtain results";
		}
			
	}
	
	function get_result_set() {
		$result_data =& $this->db->get("reports", "get_tag_report_data", array("date_start"=>$this->date_start, "date_end"=>$this->date_end));
		return $result_data;
	}
	
	function get_report_data() {
		return $report_data;
	}	
}

