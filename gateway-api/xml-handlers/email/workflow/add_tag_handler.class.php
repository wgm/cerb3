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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 *
 */
class add_tag_handler extends xml_parser
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
    * @return ticket_add_tag_handler
    */
   function add_tag_handler(&$xml) {
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
		
		$tag_elm =& $this->xml->get_child('tag', 0);
		
		//$tag_id = $tag_elm->get_attribute("id", FALSE);
		$parent_tag_id = $tag_elm->get_attribute("parent_id", FALSE);
		$tag_name = $tag_elm->get_data();
		
		$cer_workstation_tags = new CerWorkstationTags();
		$newId = $cer_workstation_tags->addTag($tag_name, $parent_tag_id);
		
		if($newId === FALSE) {
			xml_output::error(0, 'Failed adding tag to ticket.');
		}
		else {
			$xml =& xml_output::get_instance();
			$data =& $xml->get_child("data", 0);

			$data->add_child("tag", xml_object::create("tag", NULL, array("id"=>$newId)));
			xml_output::success();
		}
      
   }
   
}