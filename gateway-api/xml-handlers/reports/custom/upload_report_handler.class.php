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
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/jasper_report_saver.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the weekly sales revenue based on the
 * close_date in opportunities
 *
 */
class upload_report_handler extends xml_parser
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
    * @return ticket_age_report_handler
    */
   function upload_report_handler(&$xml) {
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
		
		$reportId = $this->xml->get_child_data("reportid"); //only exists if uploading data for an existing (already installed) report
		$gid = $this->xml->get_child_data("gid");
		$name = $this->xml->get_child_data("title");
		$summary = $this->xml->get_child_data("summary");
		$version = $this->xml->get_child_data("version");
		$author = $this->xml->get_child_data("author");
		$report_data = $this->xml->get_child_data("report");
		$scriptlet = $this->xml->get_child_data("scriptlet");
		$report_source = $this->xml->get_child_data("report_source");
		$scriptlet_source = $this->xml->get_child_data("scriptlet_source");
		
		
		$team_ids = array();
		$teamsElm =& $this->xml->get_child("teams", 0);
		$teamElms =& $teamsElm->get_children("team");
		if(is_array($teamElms))
			foreach ($teamElms AS $teamElm) {
				$team_ids[] = $teamElm->get_attribute("id", false);
			}

		$jasper_report_saver = new jasper_report_saver($reportId, $gid, $name, $summary, $version, $author, $report_data, $scriptlet, $team_ids, $report_source, $scriptlet_source);
		$jasper_report_saver->save_report();
		
		//gets "inserted", "updated", or "ignored", depending on version and gid conflicts
		$status = $jasper_report_saver->get_status();
		
		
		$xmlout =& xml_output::get_instance();
		$dataout =& $xmlout->get_child("data", 0);
		$dataout->add_child("status", xml_object::create("status", $status));
		
		xml_output::success();
	}
}

