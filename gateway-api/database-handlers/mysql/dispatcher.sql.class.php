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
 * Database abstraction layer for dispatcher
 *
 */
class dispatcher_sql
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
    * @return dispatcher_sql
    */
   function dispatcher_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Clears something from the dispatcher_delays
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function clear_from_delay_queue($params) {
   	extract($params);
      $sql = "DELETE FROM dispatcher_delays WHERE delay_id = '%d'";
      return $this->db->Execute(sprintf($sql, $delay_id));
   }
   
   /**
    * Gets a list of tickets which others have scheduled to resurrect to them
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_scheduled_others($params) {
   	extract($params);
   	$ticket_list = "'" . implode("','", $ticket_ids) . "'";
      $sql = "SELECT DISTINCT ticket_id FROM dispatcher_delays WHERE ticket_id IN ( %s ) AND agent_id != '%d' AND (expire_timestamp != '1' OR delay_type != %d)";
      return $this->db->GetAll(sprintf($sql, $ticket_list, $user_id, DISPATCHER_DELAY_DATE));
   }
   
   /**
    * Gets a list of tickets which I have ignored or rejected
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_ignored_or_rejected($params) {
   	extract($params);
      $sql = "SELECT DISTINCT ticket_id FROM dispatcher_delays WHERE agent_id = '%d' AND delay_type = '%d'";
      return $this->db->GetAll(sprintf($sql, $user_id, DISPATCHER_DELAY_DATE));
   }

   /**
    * Purges the ignored tickets whose time has expired (leaving permanent rejects in tact)
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function purge_ignored_tickets($params) {
   	extract($params);
      $sql = "DELETE FROM dispatcher_delays WHERE delay_type = '%d' AND expire_timestamp != 1 AND expire_timestamp < UNIX_TIMESTAMP()";
      return $this->db->Execute(sprintf($sql, DISPATCHER_DELAY_DATE));
   }
   
   /**
    * Saves the teams an agent pulled tickets from
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function save_pulled_teams($params) {
   	extract($params);
   	$teams_list = "'" . implode("','", $teams) . "'";
      $sql = "UPDATE team_members SET ticket_pull = 1 WHERE agent_id = '%d' AND team_id IN (%s)";
      return $this->db->Execute(sprintf($sql, $user_id, $teams_list));
   }
   
   /**
    * Clears the pulled ticket's mark on all teams for an agent
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function clear_ticket_pulled_teams($params) {
   	extract($params);
   	$sql = "UPDATE team_members SET ticket_pull = 0 WHERE agent_id = '%d'";
      return $this->db->Execute(sprintf($sql, $user_id, $teams_list));
   }
   
   function hide_ticket($params) {
      extract($params);
      $sql = "INSERT INTO dispatcher_delays (ticket_id, agent_id, delay_type, added_timestamp, expire_timestamp, reason) VALUES ('%d', '%d', '%d', UNIX_TIMESTAMP(), 1, %s)";
      return $this->db->Execute(sprintf($sql, $ticket_id, $user_id, DISPATCHER_DELAY_DATE, $this->db->qstr($reason)));
   }
   
   function delay_ticket($params) {
      extract($params);
      
      $sql = " DELETE from dispatcher_delays WHERE ticket_id='%d' AND agent_id='%d' ";
      $this->db->Execute(sprintf($sql, $ticket_id, $user_id));
      
      $delay_expire = 0;
      if(isset($permanent) && $permanent == 1) {
      	$delay_expire = 1609372800;// use expire 12/31/2020 as the date for tickets to permanently hide
      }
      elseif(isset($timestamp) && $timestamp!="" && $timestamp!=null) {
      	$delay_expire = $timestamp;
      }
      else {
      	$delay_expire = time() + $mins*60;
      }
      $sql = "INSERT INTO dispatcher_delays (ticket_id, agent_id, delay_type, added_timestamp, expire_timestamp, reason) VALUES ('%d', '%d', '%d', UNIX_TIMESTAMP(), %d, %s)";
      //echo sprintf($sql, $ticket_id, $user_id, DISPATCHER_DELAY_DATE, $delay_expire, $this->db->qstr($reason));exit();
      return $this->db->Execute(sprintf($sql, $ticket_id, $user_id, DISPATCHER_DELAY_DATE, $delay_expire, $this->db->qstr($reason)));
   }
}
