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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * 
 *
 */
class flag_tickets_handler extends xml_parser
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
    * @return flag_tickets_handler
    */
   function flag_tickets_handler(&$xml) {
      $this->xml =& $xml;
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
   	/* @var $agent_xml xml_object */
   	/* @var $tickets_xml xml_object */
   	
      $users_obj =& new general_users();
      if($users_obj->check_login() === FALSE) {
         xml_output::error(0, 'Not logged in. Please login before proceeding!'); 
      }
      
      $email_obj =& new email_tickets();
          
      $tickets = array();
           
      $agent_xml = $this->xml->get_child('agent', 0);      
      $agent_id = $agent_xml->get_attribute('id', FALSE);

      $override = $this->xml->get_child_data("override", 0);
      $override = ($override=="1") ? true : false;
      
      $tickets_xml = $this->xml->get_child('tickets', 0);
      $children =& $tickets_xml->get_children();
      
      if(is_array($children['ticket'])) {
         foreach($children['ticket'] as $key=>$ticket_node) {
         	$ticket_id = $ticket_node->get_attribute('id', FALSE);
            $tickets[$ticket_id] = $ticket_id;
         }
      }
      
      //[mdf] for now always just use the logged in user according to the server, rather than the xml param
      $agent_id = general_users::get_user_id();
      $flagged_by_others = $email_obj->flag_tickets($tickets, $agent_id, $override);
      
      if($flagged_by_others === FALSE) {
         xml_output::error(0, 'Flag tickets failed'); 
      }
      else {
      	//[mdf] gets the subject and user names (+more) of who has these tickets flagged
		$conflicts = $email_obj->get_ticket_conflict_info($flagged_by_others, $agent_id);

      	$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$elm_conflicts =& $data->add_child("ticket_flag_conflict", xml_object::create("ticket_flag_conflicts"));
		
		foreach ($conflicts as $conflict) {
			$elm_conflict =& $elm_conflicts->add_child("conflict", xml_object::create("conflict"));
			$elm_conflict->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$conflict['ticket_id'])));
			$elm_conflict->add_child("subject", xml_object::create("subject", $conflict['subject']));
			$elm_conflict->add_child("user_str", xml_object::create("user_str", $conflict['user_str']));
		}
		
		xml_output::success();
      }
   }        
}