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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Database abstraction layer for team data
 *
 */
class teams_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;

   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return teams_sql
    */
   function teams_sql(&$db) {
      $this->db =& $db;
   }
   
   function add_team($params) {
      extract($params);
      $sql = "INSERT INTO team (team_name, team_acl1, team_acl2, team_acl3) VALUES (%s, '%d', '%d', '%d')";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($name), $acl1, $acl2, $acl3))) {
         return FALSE;
      }
      return $this->db->Insert_ID();
   }
   
   function add_member($params) {
      extract($params);
      $sql = "INSERT INTO team_members (team_id, agent_id) VALUES ('%d', '%d')";
      return $this->db->Execute(sprintf($sql, $team_id, $agent_id));
   }
   
   function get_team_info($params) {
      extract($params);
      $sql = "SELECT t.*, q.* FROM team t LEFT JOIN queue q USING (queue_id) WHERE t.team_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $team_id));
   }
   
   function get_team_list($params) {
   	extract($params);
      $sql = "
			SELECT t.team_id, t.team_name 
			FROM team t 
			ORDER BY t.team_name
      ";
      
      $teamRes = $this->db->GetAll(sprintf($sql));
      
      $sql = "
      	SELECT tm.member_id, tm.team_id, tm.agent_id, a.user_id, a.user_name, a.user_display_name 
      	FROM team_members tm 
			LEFT JOIN team t USING ( team_id ) 
			INNER JOIN user a ON ( a.user_id = tm.agent_id) 
      	ORDER BY a.user_display_name 
      ";

      $memberRes = $this->db->GetAll(sprintf($sql));
      
      // [JAS]: Load the team list into an array
      $teamsAry = array();
      if(is_array($teamRes)) {
      	foreach($teamRes as $teamRow) {
      		$team_id = $teamRow["team_id"];
      		$teamsAry[$team_id] = array(
      			"id" => $team_id,
      			"name" => $teamRow["team_name"],
      			"members" => array()
      		);
      	}
      }
      
      // [JAS]: Load the members into the teams
      if(is_array($memberRes)) {
      	foreach($memberRes as $memberRow) {
      		$team_id = $memberRow["team_id"];
      		$member_id = $memberRow["member_id"];
      		$agent_id = $memberRow["agent_id"];
      		$member_name = $memberRow["user_name"];
      		$member_display_name = $memberRow["user_display_name"];
      		
      		if(!isset($teamsAry[$team_id]))
      			continue;
      		
				$teamsAry[$team_id]["members"][$member_id] = array(
					"id" => $member_id,
					"agent_id" => $agent_id,
					"name" => (empty($member_display_name) ? $member_name : $member_display_name)
				);
      	}
      }
      
   	return $teamsAry;
   }
   
   function get_team_list_for_agent($params) {
      extract($params);
      $sql = "SELECT tm.ticket_pull, t.team_name, t.team_id 
               FROM team_members tm
               INNER JOIN team t USING ( team_id )
               WHERE tm.agent_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $user_id));
   }
   
   // [JAS]: [TODO] We really need an entirely separate db call for this?
   function get_team_name($params) {
   		extract($params);
      $sql = "SELECT team_name FROM team WHERE t.team_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $team_id));
   }
   
   function get_member_info($params) {
      extract($params);
      $sql = "SELECT tm.*, u.user_name, u.user_login FROM team_members tm LEFT JOIN user u ON (tm.agent_id = u.user_id) WHERE tm.team_id = '%d' AND tm.agent_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $team_id, $agent_id));
   }
   
   function remove_member($params) {
      extract($params);
      $sql = "DELETE FROM team_members WHERE team_id = '%d' AND member_id = '%d'";
      return $this->db->Execute(sprintf($sql, $team_id, $member_id));
   }
   
   function remove_team($params) {
      extract($params);
      $sql = "DELETE FROM department_teams WHERE team_id = '%d'";
      $this->db->Execute(sprintf($sql, $team_id));
      $sql = "DELETE FROM team WHERE team_id = '%d'";
      return $this->db->Execute(sprintf($sql, $team_id));
   }
   
   function update_team($params) {
      extract($params);
      $sql = "UPDATE team SET team_name = %s, team_acl1 = '%d', team_acl2 = '%d', team_acl3 = '%d' WHERE team_id = '%d'";
      return $this->db->Execute(sprintf($sql, $this->db->qstr($name), $acl1, $acl2, $acl3, $team_id));
   }
   
   function get_members($params) {
      extract($params);
      $sql = "SELECT tm.*, u.user_name, u.user_login FROM team_members tm LEFT JOIN user u ON (tm.agent_id = u.user_id) WHERE tm.team_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $team_id));
   }  

   function get_all_members($params) {
      $sql = "SELECT tm.*, u.user_name, u.user_login FROM team_members tm INNER JOIN user u ON (tm.agent_id = u.user_id) ORDER BY u.user_name ";
      return $this->db->GetAll($sql);
   }           
     
}