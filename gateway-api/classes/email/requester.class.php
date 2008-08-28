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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/object/xml_object.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/address.class.php");

class requester2
{
	/**
	 * 
	@var String
    */
	var $ticket_id;
	/**
	 * email adddress and id
	 *
	 * @var address
	 */
	var $address;

	function requester2($ticket_id, $address) {
		$this->ticket_id = $ticket_id;
		$this->address = $address;
	}
	
	/**
	 * Creates a requester2 object from a requester xml object
	 *
	 * @param xml_object $xml_obj
	 * @return requester2
	 */
	function createFromXML(&$xml_obj) {
		$ticket_elm =& $xml_obj->get_child("ticket",0);
		$ticket_id = $ticket_elm->get_attribute("id", FALSE);
		$address = address::createFromXML($xml_obj->get_child("address", 0));
		return new requester2($ticket_id, $address);

	}

}

