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
|		Jeff Standen		jeff@webgroupmedia.com		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
//require_once(FILESYSTEM_PATH . "gateway-api/classes/general/system.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/acl/CerACL.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 *
 */
class get_acl_handler extends xml_parser
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
	* @return get_license_handler
	*/
	function get_acl_handler(&$xml) {
		$this->xml =& $xml;
	}

	/**
	* main() function for this class. 
	*
	*/
	function process() {
		$users_obj =& new general_users();
		if($users_obj->check_login() === FALSE) {
			xml_output::error(0, 'Not logged in. Get license failed! Please login before proceeding!');
		}
      
		$acl =& CerACL::getInstance();
      
		$xmlout =& xml_output::get_instance();
		$dataout =& $xmlout->get_child("data", 0);

		$acl_elm =& $dataout->add_child("acl", xml_object::create("acl"));
		
		$acl_elm->add_child("acl1", xml_object::create("acl1", $acl->acl1));
		$acl_elm->add_child("acl2", xml_object::create("acl2", $acl->acl2));
		$acl_elm->add_child("acl3", xml_object::create("acl3", $acl->acl3));
		$acl_elm->add_child("super", xml_object::create("super", $acl->is_superuser));
		$teams_elm =& $acl_elm->add_child("teams", xml_object::create("teams"));
		foreach ($acl->teams AS $team_id) {
			$teams_elm->add_child("team", xml_object::create("team", NULL, array("id"=>$team_id)));
		}

		$queues_elm =& $acl_elm->add_child("queues", xml_object::create("queues"));
		foreach ($acl->queues AS $queue_id) {
			$queues_elm->add_child("queue", xml_object::create("queue", NULL, array("id"=>$queue_id)));
		}
		
		$tagsets_elm =& $acl_elm->add_child("tagsets", xml_object::create("tagsets"));
		foreach($acl->tagsets AS $tag_id) {
			$tagsets_elm->add_child("set", xml_object::create("set", NULL, array("id"=>$tag_id)));
		}
		
         xml_output::success();

	}
}

/*
    [acl1] => 1026
    [acl2] => 2621440
    [acl3] => 0
    [user_id] => 3
    [is_superuser] => 0
    [teams] => Array
        (
            [1] => 1
            [4] => 4
        )

    [queues] => Array
        (
            [2] => 2
        )
*/

