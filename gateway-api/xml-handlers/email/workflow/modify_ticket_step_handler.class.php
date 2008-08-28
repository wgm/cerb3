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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/ticket_steps.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the listeners for a ticket (ie requesters and watchers
 *
 */
class modify_ticket_step_handler extends xml_parser
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
    * @return get_listeners_handler
    */
   function modify_ticket_step_handler(&$xml) {
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

		$step_obj =& $this->xml->get_child('ticket_step', 0);
		$step_id = $step_obj->get_attribute('id', FALSE);
		
		//$date_created = $step_obj->get_child_data("date_created");
		$note = $step_obj->get_child_data("note");
		
		$ticket_steps =& new ticket_steps();
		$step = $ticket_steps->modify_step($step_id, $note);
		
		if($step == null) {
			xml_output::error(0, 'Failed modifying ticket step.');
		}
		else {
			xml_output::success();
		}
      
   }
   
/* expects this format for input:
<?xml version="1.0" encoding="UTF-8"?>
<cerberus_xml>
   <channel>email</channel>
   <module>workflow</module>
   <command>modify_ticket_step</command>
   <data>
<ticket_step id="15">
<note>This is a step modified!!!!.</note>
</ticket_step>
</data>
</cerberus_xml>
*/
}