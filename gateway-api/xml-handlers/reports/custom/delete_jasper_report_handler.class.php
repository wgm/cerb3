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
require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/mysql/custom_report_mappings.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_deleter.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 *
 *
 */
class delete_jasper_report_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var $xml xml_object
    */
   var $xml;

   /**
    * Class constructor
    *
    * @param object $xml
    * @return delete_custom_report_handler
    */
   function delete_jasper_report_handler(&$xml) {
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
		
		$db =& database_loader::get_instance();
		
		$reportElm =& $this->xml->get_child("report", 0);
		$id = $reportElm->get_attribute("id",FALSE);

		$sql = "DELETE FROM jasper_reports WHERE jasper_report_id = %d";
		if($db->direct->Execute(sprintf($sql, $id))) {
			$xmlout =& xml_output::get_instance();
			$dataout =& $xmlout->get_child("data", 0);
			xml_output::success();
		}
		else {
			xml_output::error(1, "The server failed to delete the report");
		}
	}
}

