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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/visitors.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the visitor referrer report
 *
 */
class get_referrer_report_handler extends xml_parser
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
    * @return get_referrer_report_handler
    */
   function get_referrer_report_handler(&$xml) {
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
      
      $obj =& new reports_visitors();
      
      $range = $this->xml->get_child_data("range", 0);
      if(empty($range) || !is_numeric($range) || $range < 1) {
         $range = 1;
      }
      
      $host_limit = $this->xml->get_child_data("host_limit", 0);
      if(empty($host_limit) || !is_numeric($host_limit) || $host_limit < 1) {
         $host_limit = 5;
      }
      
      $url_limit = $this->xml->get_child_data("url_limit", 0);
      if(empty($url_limit) || !is_numeric($url_limit) || $url_limit < 1) {
         $url_limit = 250;
      }
      
      if($obj->get_referrer_report($range, $host_limit, $url_limit) === FALSE) {
         xml_output::error(0, 'Failed to get visitor referrer report!');
      }
      else {
         xml_output::success();
      }
   }
}