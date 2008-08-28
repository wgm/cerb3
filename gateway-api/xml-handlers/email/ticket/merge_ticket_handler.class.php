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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php"); //contains CER_TICKET_MERGE class
require_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php"); //contains CER_PARSER class
require_once(FILESYSTEM_PATH . "cerberus-api/log/audit_log.php"); //contains CER_AUDIT_LOG class


if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * Merges two tickets into one
 *
 */
class merge_ticket_handler extends xml_parser
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
	* @return get_ticket_category_list_handler
	*/
	function merge_ticket_handler(&$xml) {
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
		
		$source_elm =& $this->xml->get_child('source', 0);
		$source_children = $source_elm->get_children();
		$source_tickets = array();
		if(is_array($source_children['ticket'])) {
			foreach($source_children['ticket'] as $key=>$ticket_obj) {
				$source_tickets[] = $ticket_obj->get_attribute('id', FALSE);
			}
		}
		
		$dest_elm =& $this->xml->get_child('destination', 0);
		$dest_ticket_elm =& $dest_elm->get_child('ticket', 0);
		$dest_ticket_id = $dest_ticket_elm->get_attribute('id', FALSE);
		
		$merger = new CER_TICKET_MERGE();
		$result = $merger->do_merge_into($source_tickets, $dest_ticket_id);

		if($result === FALSE) {
			xml_output::error(0, 'Failed to merge tickets');
		}
		else {
			xml_output::success();
		}
	}
}