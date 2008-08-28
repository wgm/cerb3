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
|		Mike Fogg		mike@webgroupmedia.com		[mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/email_templates/cer_email_templates.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles parsing an email template for a ticket
 *
 */
class parse_template_handler extends xml_parser
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
    * @return get_sender_profiles_handler
    */
   function parse_template_handler(&$xml) {
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
      
      
		$ticket_elm =& $this->xml->get_child('ticket', 0);
		$ticket_id = $ticket_elm->get_attribute('id', FALSE);
		settype($ticket_id, 'integer');
		
		$template_elm =& $this->xml->get_child('template', 0);
		$template_id = $template_elm->get_attribute('id', FALSE);
		settype($template_id, 'integer');

		if($ticket_id==0) {
			xml_output::error(0, 'Could not parse email template.  Ticket id specified is not a valid integer (or it was 0)');
			exit();
		}
		if($template_id==0) {
			xml_output::error(0, 'Could not parse email template.  Template id specified is not a valid integer (or it was 0)');
			exit();
		}

		$email_templates_obj = new CER_EMAIL_TEMPLATES();
		$parsedBody = $email_templates_obj->parse_canned_template($template_id, $ticket_id);
      
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		
		$parsedElm =& $data->add_child("parsed", xml_object::create("parsed", $parsedBody));
		xml_output::success();
	}
}