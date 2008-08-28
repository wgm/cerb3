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

//require_once(FILESYSTEM_PATH . "gateway-api/classes/email/contacts.inc.php");

class address
{
	/**
	
    */
	var $id;
	var $email;

	function address($id, $email) {
		$this->id = $id;
		$this->email = $email;
	}
	
	/**
	 * @param $xml_object xml_object
	 * @return address
	**/
	function createFromXML(&$xml_object) {
		$id = $xml_object->get_attribute("id", FALSE);
		$email = $xml_object->get_data();
		return new address($id, $email);
	}

}

