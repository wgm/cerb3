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
include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * 
 *
 */
class quick_assign_tickets_handler extends xml_parser
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
   function quick_assign_tickets_handler(&$xml) {
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

      $limit = $this->xml->get_child_data("limit", 0);
      $limit = intval($limit);
      
      $teams_xml = $this->xml->get_child('teams', 0);
      $children =& $teams_xml->get_children();

      $team_ids = array();
      if(is_array($children['team'])) {
         foreach($children['team'] as $key=>$team_elm) {
         	$team_id = $team_elm->get_attribute('id', FALSE);
            $team_ids[$team_id] = $team_id;
         }
      }
      
      //[mdf] for now always just use the logged in user according to the server, rather than the xml param
      $agent_id = general_users::get_user_id();
      
      
	if(CerWorkstationTickets::quickAssignToAgent($team_ids,$agent_id,$limit)) {
		//$cerMyTickets = CerWorkstationTickets::getMyTickets($user_id);
	} else { // error
		xml_output::error(0, 'Flag tickets failed');
	}
    xml_output::success();
   }        
}