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
|		Jeff Standen		jeff@webgroupmedia.com		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/sigs.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 *
 */
class update_sender_profile_handler extends xml_parser
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
    * @return update_sender_profile_handler
    */
   function update_sender_profile_handler(&$xml) {
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
      
      $profile_id = $this->xml->get_child_data("profile_id", 0);
      $nickname = $this->xml->get_child_data("nickname", 0);
      $reply_to = $this->xml->get_child_data("reply_to", 0);
      $signature = $this->xml->get_child_data("signature", 0);

      $sigs_obj =& new email_sigs();
      
      if($sigs_obj->update_sender_profile($profile_id, $nickname, $reply_to, $signature) === FALSE) {
         xml_output::error(0, 'Update sender profile failed!');
      }
      else {
         xml_output::success();
      }
   }
}