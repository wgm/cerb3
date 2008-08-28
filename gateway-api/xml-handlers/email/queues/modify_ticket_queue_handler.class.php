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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class modifies the queues for one or more tickets
 *
 */
class modify_ticket_queue_handler extends xml_parser
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
    * @return modify_ticket_queue_handler
    */
   function modify_ticket_queue_handler(&$xml) {
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

      $tickets = array();
      $elm_queue =& $this->xml->get_child("queue", 0);
      $queue_id = $elm_queue->get_attribute("id", FALSE);
      
      $tickets_xml =& $this->xml->get_child("tickets", 0);
      if(is_object($tickets_xml)) {
         $ticket_array = $tickets_xml->get_child("ticket");
         if(is_array($ticket_array)) {
            foreach($ticket_array as $ticket_xml) {
               $tickets[] = $ticket_xml->get_attribute("id", FALSE);
            }
         }
      }
      
      if(!CerWorkstationTickets::setTicketMailbox($tickets, $queue_id)) {
      	xml_output::error(0, 'Failed to modify ticket mailbox.');
      }
      else {
         xml_output::success();
      }      
   }
}