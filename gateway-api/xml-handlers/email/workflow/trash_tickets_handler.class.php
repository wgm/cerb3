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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class gets the ticket steps for a ticket
 *
 */
class trash_tickets_handler extends xml_parser
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
    * @return trash_tickets_handler
    */
   function trash_tickets_handler(&$xml) {
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
      if($email_obj->trash_tickets($tickets, $agent_id) === FALSE) {
         xml_output::error(0, 'Trash tickets failed'); 
      }
      else {
         xml_output::success();
      }
   }        
}
/* 
<?xml version="1.0" encoding="UTF-8"?>
<cerberus_xml>
   <channel>email</channel>
   <module>workflow</module>
   <command>trash_tickets</command>
   <data>
   <agent id="1"/>
   <tickets>
	  <ticket id="2"/>
	</tickets>
   </data>
</cerberus_xml>
*/

