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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/arrays.inc.php");

class email_sigs
{
	/**
    * DB abstraction layer handle
    *
    * @var object
    */
	var $db;

	function email_sigs() {
		$this->db =& database_loader::get_instance();
	}

	/*
	*/
	function update_sender_profile($profile_id, $nickname, $reply_to, $signature) {

		$args = array(
			"profile_id"=>$profile_id,
			"nickname"=>$nickname,
			"reply_to"=>$reply_to,
			"signature"=>$signature
		);
		
      $profile_id = $this->db->get("sigs", "update_sender_profile", $args);
		
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $profile =& $data->add_child("profile_id", xml_object::create("profile_id", $profile_id, array()));
	}
	
	function delete_sender_profile($profile_id) {

		$args = array(
			"profile_id"=>$profile_id
		);
		
      return $this->db->get("sigs", "delete_sender_profile", $args);
	}

	function default_sender_profile($profile_id) {

		$args = array(
			"profile_id"=>$profile_id
		);
		
      return $this->db->get("sigs", "default_sender_profile", $args);
	}

   function get_sender_profiles($agent_id) {
	   $user_data = $this->db->Get("sigs", "get_sender_profiles", array("agent_id"=>$agent_id));
		
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $profiles =& $data->add_child("profiles", xml_object::create("profiles", NULL, array()));
      
      if(is_array($user_data))
      foreach($user_data as $user_sig) {
      	$s_id = $user_sig["id"];
      	$s_default = $user_sig["is_default"];
      	$s_nickname = stripslashes($user_sig["nickname"]);
      	$s_reply_to = stripslashes($user_sig["reply_to"]);
      	$s_sig = stripslashes($user_sig["signature"]);
      	
      	$profile =& $profiles->add_child("profile", xml_object::create("profile", NULL, array("id"=>$s_id,"default"=>$s_default)));
      	$nickname =& $profile->add_child("nickname", xml_object::create("nickname", $s_nickname, array()));
      	$reply_to =& $profile->add_child("reply_to", xml_object::create("reply_to", $s_reply_to, array()));
      	$sig =& $profile->add_child("signature", xml_object::create("signature", $s_sig, array()));
      }
	   
   }
	
	
}

