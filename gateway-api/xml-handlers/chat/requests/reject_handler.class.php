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
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/visitor.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class rejects a chat request
 *
 */
class reject_handler extends xml_parser
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
    * @return reject_handler
    */
   function reject_handler(&$xml) {
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
      
      $req_obj =& new chat_visitor();   
      
      $request_id = $this->xml->get_child_data("request_id", 0);
      if(empty($request_id) || $request_id < 1) {
         xml_output::error(0, 'Request ID is required!');
      }
      
      if($req_obj->reject_chat_request($request_id) === FALSE) {
         xml_output::error(0, 'Failed to reject chat request');
      }
      else {
         xml_output::success();
      }
   }
}