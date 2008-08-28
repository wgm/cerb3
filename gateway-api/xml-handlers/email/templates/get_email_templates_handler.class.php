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
 * This class handles getting queue list system data
 *
 */
class get_email_templates_handler extends xml_parser
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
   function get_email_templates_handler(&$xml) {
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
      
      $email_templates_obj = new CER_EMAIL_TEMPLATES();
      $email_templates = $email_templates_obj->getTemplates();
     // print_r($email_templates);exit();
//t.template_id, t.template_name, t.template_description from email_templates t ORDER BY t.template_name;		
      
		$xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $email_templates_elm =& $data->add_child("email_templates", xml_object::create("email_templates", NULL, array()));
      
      if(is_array($email_templates)) {
	      foreach($email_templates as $email_template) {
	      	$id = $email_template->id;
	      	//$name = $user_sig["template_name"];
	      	$name = stripslashes($email_template->name);
	      	$description = stripslashes($email_template->description);
	      	$body = stripslashes($email_template->body);
	      	//echo $email_template["template_name"];exit();
	      	$email_template_elm =& $email_templates_elm->add_child("template", xml_object::create("template", NULL, array("id"=>$id)));
	      	$email_template_elm->add_child("name", xml_object::create("name", $name));
	      	$email_template_elm->add_child("description", xml_object::create("description", $description));
	      	$email_template_elm->add_child("body", xml_object::create("body", $body));
	      }
	      xml_output::success();
      }
      else {
      	xml_output::error(0, 'Email template download failed!');
      }
      
   }
}