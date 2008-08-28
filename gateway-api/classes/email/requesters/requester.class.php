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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/arrays.inc.php");

class requester
{
	/**

    */
	var $ticket_id;
	var $address_id;
	var $address;
	var $public_user_id;
	var $first_name;
	var $last_name;
	var $company_name;
	var $company_id;
	var $is_primary;

	function requester($ticket_id, $address_id, $address, $public_user_id, $first_name, $last_name, $company_name, $company_id, $is_primary) {
		$this->ticket_id = $ticket_id;
		$this->address_id = $address_id;
		$this->address = $address;
		$this->public_user_id = $public_user_id;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->company_name = $company_name;
		$this->company_id = $company_id;
		$this->is_primary = $is_primary;
	}

}

