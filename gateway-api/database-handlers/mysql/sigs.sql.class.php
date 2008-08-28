<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2006, WebGroup Media LLC
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
|		Jeff Standen		jeff@webgroupmedia.com		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Database abstraction layer for user data
 *
 */
class sigs_sql
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
    * @return sigs_sql
    */
   function sigs_sql(&$db) {
      $this->db =& $db;
   }

   function get_sender_profiles($params) {
      extract($params);
      
      $sql = sprintf("SELECT s.id, s.is_default, s.agent_id, s.nickname, s.reply_to, s.signature ".
	      	"FROM `agent_sender_profile` s ".
	      	"INNER JOIN `user` u ON (u.user_id=s.agent_id) ".
	      	"WHERE s.agent_id = %d ".
	      	"ORDER BY s.nickname ASC ",
	      		$agent_id
      	);
      
		return $this->db->GetAll($sql);   	
   }
   
   function delete_sender_profile($params) {
	  	/* @var $db ADOConnection */
   	extract($params);

      $user_data = $_SESSION["user_data"];
      $user_id = $user_data['user_id'];

      $sql = sprintf("DELETE FROM `agent_sender_profile` WHERE `agent_id` = %d AND `id` = %d",
      	$user_id,
      	$profile_id
      );
      
      return $this->db->Execute($sql);
   }
   
   function default_sender_profile($params) {
	  	/* @var $db ADOConnection */
   	extract($params);

      $user_data = $_SESSION["user_data"];
      $user_id = $user_data['user_id'];

      $sql = sprintf("UPDATE `agent_sender_profile` SET `is_default` = 0 WHERE `agent_id` = %d",
      	$user_id
      );
      $this->db->Execute($sql);
      
      $sql = sprintf("UPDATE `agent_sender_profile` SET `is_default` = 1 WHERE `agent_id` = %d AND `id` = %d",
      	$user_id,
      	$profile_id
      );
      
      return $this->db->Execute($sql);
   }
   
   function update_sender_profile($params) {
	  	/* @var $db ADOConnection */
   	extract($params);

      $user_data = $_SESSION["user_data"];
      $user_id = $user_data['user_id'];
   	
   	if(empty($profile_id)) {
   		$sql = sprintf("INSERT INTO `agent_sender_profile` (agent_id) VALUES (%d)",
   			$user_id
   		);
   		$this->db->Execute($sql);
   		$profile_id = $this->db->Insert_ID();
   	}
   	
   	if(empty($profile_id))
   		return FALSE;
   		
   	$sql = sprintf("UPDATE `agent_sender_profile` ".
   		"SET `nickname`=%s, `reply_to`=%s, `signature`=%s ".
   		"WHERE `agent_id`=%d AND `id`=%d",
   			$this->db->qstr($nickname),
   			$this->db->qstr($reply_to),
   			$this->db->qstr($signature),
   			$user_id,
   			$profile_id
   		);
   	$this->db->Execute($sql);
   	
   	return $profile_id;
   }

}