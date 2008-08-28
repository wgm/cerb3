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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/dispatcher.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting a list of departments
 *
 */
class get_dispatcher_toolset_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
	var $xml;
	var $dispatcher;
   /**
    * Class constructor
    *
    * @param object $xml
    * @return get_list_handler
    */
	function get_list_handler(&$xml) {
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

		$this->dispatcher = new general_dispatcher();
		
		if($this->dispatcher->error_msg != "") {
			xml_output::error(0, 'Failed to get department');
		}
		else {
			$this->output_xml();
		}

   }
   
	function output_xml() {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$departments =& $data->add_child("departments", xml_object::create("departments", NULL, array("name"=>"Departments")));
		if(is_array($this->dispatcher->departments_list)) {
			foreach($this->dispatcher->departments_list as $department_row) {
				$department =& $departments->add_child("department", xml_object::create("department", NULL, array("id"=>$department_row['department_id'])));
				$department->add_child("name", xml_object::create("name", $department_row['department_name']));
			}
		}

		$teams =& $data->add_child("teams", xml_object::create("teams", NULL, array("name"=>"Teams")));
		if(is_array($this->dispatcher->teams_list)) {
			foreach($this->dispatcher->teams_list as $team_row) {
				$team =& $teams->add_child("team", xml_object::create("team", NULL, array("id"=>$team_row['team_id'])));
				$team->add_child("name", xml_object::create("name", $team_row['team_name']));
			}
		}

		$members =& $data->add_child("members", xml_object::create("members", NULL, array("name"=>"People")));
		if(is_array($this->dispatcher->members_list)) {
			foreach($this->dispatcher->members_list as $member_row) {
				$member =& $members->add_child("member", xml_object::create("member", NULL, array("id"=>$member_row['member_id'])));
				$member->add_child("name", xml_object::create("name", $member_row['user_name']));
			}
		}

		$slas =& $data->add_child("slas", xml_object::create("slas", NULL, array("name"=>"SLA")));
		if(is_array($this->dispatcher->sla_list)) {
			foreach($this->dispatcher->sla_list as $sla_row) {
				$sla =& $slas->add_child("sla", xml_object::create("sla", NULL, array("id"=>$sla_row['id'])));
				$sla->add_child("name", xml_object::create("name", $sla_row['name']));
			}
		}
		
		$priorities =& $data->add_child("priorities", xml_object::create("priorities", NULL, array("name"=>"Priorities")));
		if(is_array($this->dispatcher->priorities_list)) {
			foreach($this->dispatcher->priorities_list as $key=>$val) {
				$priority =& $priorities->add_child("priority", xml_object::create("priority", NULL, array("id"=>$key)));
				$priority->add_child("name", xml_object::create("name", $val));
			}
		}		
		
		xml_output::success();

	}
}
