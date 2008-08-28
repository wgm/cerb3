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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/slas/slas_retriever.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class gets the ticket steps for a ticket
 *
 */
class get_sla_list_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
   var $xml;
   var $sla_retriever;

   /**
    * Class constructor
    *
    * @param object $xml
    * @return get_listeners_handler
    */
   function get_sla_list_handler(&$xml) {
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

		$this->slas_retriever = new slas_retriever();
		//$slas = $slas_retriever->get_slas();
		
		if($this->slas_retriever->error_message !== "") {
			xml_output::error(0, "Failed to retrieve slas");
		}
		else {
			$this->output_xml();
		}
		
   }
   
	function output_xml() {
		$slas =& $this->slas_retriever->get_slas();
		$schedules = $this->slas_retriever->schedules->schedules;
		//print_r($this->slas_retriever->schedules->schedules);exit();
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$slas_elm =& $data->add_child("slas", xml_object::create("slas"));
		
		if(is_array($slas))
		foreach ($slas as $sla) {
			$sla_elm =& $slas_elm->add_child("sla", xml_object::create("sla", NULL, array("id"=>$sla->id)));
			$sla_elm->add_child("name", xml_object::create("name", $sla->name));
		}
		
		$schedules_elm =& $data->add_child("schedules", xml_object::create("schedules"));
		if(is_array($schedules))
		foreach($schedules AS $schedule) {
					$schedule_elm =& $schedules_elm->add_child("schedule", xml_object::create("schedule", NULL, array("id"=>$schedule->schedule_id)));
					$schedule_elm->add_child("name", xml_object::create("name", $schedule->schedule_name));
					$weekday_hours_elm =& $schedule_elm->add_child("day_hours", xml_object::create("day_hours"));
					
					foreach($schedule->weekday_hours as $daynum=>$hours) {
						$day_elm =& $weekday_hours_elm->add_child("day", xml_object::create("day", NULL, array("num"=>$daynum+1)));
						$day_elm->add_child("name", xml_object::create("name", $hours->day_abbrev));
						$day_elm->add_child("type", xml_object::create("type", $hours->hrs));
						$day_elm->add_child("open", xml_object::create("open", $hours->open));
						$day_elm->add_child("closed", xml_object::create("close", $hours->close));
					}
		}
		
		xml_output::success();
	}
}



