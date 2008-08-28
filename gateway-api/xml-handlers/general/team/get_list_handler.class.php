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
|		Jeff Standen			(jeff@webgroupmedia.com)		[JAS]
|		Jeremy Johnstone		(jeremy@webgroupmedia.com)		[JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/teams.class.php");
include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting a list of teams
 *
 */
class get_list_handler extends xml_parser
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

		$obj = new general_teams();
      
		$cwTeams =& CerWorkstationTeams::getInstance();
		$teams =& $cwTeams->getTeams();
      
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);

		$teams_elm =& $data->add_child("teams", xml_object::create("teams", NULL));
      
		foreach ($teams AS $teamId=>$team) {
      	
	      	
			$team_elm =& $teams_elm->add_child("team", xml_object::create("team", NULL, array("id"=>$teamId)));
			$team_elm->add_child("name", xml_object::create("name", stripslashes($team->name)));
			
			$members_elm =& $team_elm->add_child("members", xml_object::create("members", NULL));
			
			$members = $team->members;
			foreach($members as $memberId => $member) {
				$member_elm =& $members_elm->add_child("member", xml_object::create("member", NULL, array("id"=>$memberId, "agent_id"=>$member->agent_id)));
				$member_elm->add_child("name", xml_object::create("name", stripslashes($member->user_name)));
			}
	      	
			$queues_elm =& $team_elm->add_child("queues", xml_object::create("queues", NULL));
	      	$teamQueues = $team->queues;
	      	foreach ($teamQueues AS $queueId=>$queue) {
				$queue_elm =& $queues_elm->add_child("queue", xml_object::create("queue", NULL, array("id"=>$queueId)));
	      	}
      }
      
      xml_output::success();

   }
}