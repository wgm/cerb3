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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
|		Jeff Standen 		  (jeff@webgroupmedia.com)     [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class general_departments
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_departments() {
      $this->db =& database_loader::get_instance();
   }


//   function assign_team($department_id, $team_id) {
//      if(!$this->db->Save("departments", "assign_team", array("id"=>$department_id, "team_id"=>$team_id))) {
//         return FALSE;
//      }
//      return $this->get_department($department_id);
//   }

//   function unassign_team($department_id, $team_id) {
//      if(!$this->db->Save("departments", "unassign_team", array("id"=>$department_id, "team_id"=>$team_id))) {
//         return FALSE;
//      }
//      return $this->get_department($department_id);
//   }

//   function get_departments_list() {
//      $departments_list = $this->db->Get("departments", "get_departments_list", array());
//      if(!is_array($departments_list)) {
//         return FALSE;
//      }
//      $xml =& xml_output::get_instance();
//      $data =& $xml->get_child("data", 0);
//      $departments =& $data->add_child("departments", xml_object::create("departments"));
//      foreach($departments_list as $department_item) {
//         $department =& $departments->add_child("department", xml_object::create("department", NULL, array("id"=>$department_item['department_id'])));
//         $department->add_child("name", xml_object::create("name", $department_item['department_name']));
//         $department->add_child("usage", xml_object::create("usage", $department_item['department_usage']));
//         $teams_list = $this->db->Get("departments", "get_teams_list", array("id"=>$department_item['department_id']));
//         $teams =& $department->add_child("teams", xml_object::create("teams"));
//         foreach($teams_list as $team_item) {
//            $team =& $teams->add_child("team", xml_object::create("team", NULL, array("id"=>$team_item['team_id'])));
//            $team->add_child("name", xml_object::create("name", $team_item['team_name']));
//            $members_list = $this->db->Get("teams", "get_members", array("team_id"=>$team_item['team_id']));
//            $members =& $team->add_child("members", xml_object::create("members"));
//            if(is_array($members_list)) {
//               foreach($members_list as $member_item) {
//                  $member =& $members->add_child("member", xml_object::create("member", NULL, array("id"=>$member_item['member_id'], "agent_id"=>$member_item['agent_id'], "login"=>$member_item['user_login'])));
//                  $member->add_child("name", xml_object::create("name", $member_item['user_name']));
//               }
//            }
//         }
//      }
//      return TRUE;
//   }

}