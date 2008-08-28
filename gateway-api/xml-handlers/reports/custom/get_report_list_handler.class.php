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
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/jasper_report_list.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles logins
 *
 */
class get_report_list_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
   var $xml;
   
   /**
    * Class constructor
    *
    * @param object $xml
    * @return login_handler
    */
   function get_report_list_handler(&$xml) {
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
   	
		$report_list = new jasper_report_list();
		$result = $report_list->get_report_list();
		
		$xmlout =& xml_output::get_instance();
		$dataout =& $xmlout->get_child("data", 0);
		
		$categories =& $dataout->add_child("categories", xml_object::create("categories"));

		$current_category="";
		if(is_array($result)) {
			for($i=0; $i < sizeof($result); $i++) {
				if($current_category != $result[$i]['category_id']) {
					$category =& $categories->add_child("category", xml_object::create("category", NULL, array("id"=>$result[$i]['category_id'])));
					$category->add_child("name", xml_object::create("name", $result[$i]['category_name']));
					$reports =& $category->add_child("reports", xml_object::create("reports"));
					$current_category = $result[$i]['category_id'];
				}
				
				$report =& $reports->add_child("report", xml_object::create("report", NULL, array("id"=>$result[$i]['jasper_report_id'], "installed"=>"1")));
				$report->add_child("name", xml_object::create("name", $result[$i]['report_name']));
				$report->add_child("summary", xml_object::create("summary", $result[$i]['summary']));
				$report->add_child("version", xml_object::create("version", $result[$i]['version']));
				$report->add_child("author", xml_object::create("author", $result[$i]['author']));
				$report->add_child("guid", xml_object::create("guid", $result[$i]['guid']));
				$report->add_child("has_report_source", xml_object::create("has_report_source", $result[$i]['has_report_source']));
				$report->add_child("has_scriptlet_source", xml_object::create("has_scriptlet_source", $result[$i]['has_scriptlet_source']));
				$report->add_child("has_report", xml_object::create("has_report", $result[$i]['has_report']));
				$report->add_child("has_scriptlet", xml_object::create("has_scriptlet", $result[$i]['has_scriptlet']));
				$teams_elm =& $report->add_child("teams", xml_object::create("teams"));
				foreach ($result[$i]['teams'] AS $team_id) {
					$teams_elm->add_child("team", xml_object::create("team", NULL, array("id"=>$team_id)));
				}
				
			}				
		}		
		
		xml_output::success();
        //xml_output::error(0, $error_msg); 
   }        
}

