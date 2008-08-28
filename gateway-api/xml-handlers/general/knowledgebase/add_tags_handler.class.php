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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/knowledgebase.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting articles detailed info
 *
 */
class add_tags_handler extends xml_parser
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
    * @return add_tags_handler
    */
   function add_tags_handler(&$xml) {
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
      
		$article_elm =& $this->xml->get_child('article', 0);
		$article_id = $article_elm->get_attribute("id", FALSE);

		$tags_elm =& $this->xml->get_child('tags', 0);
		$tags_children = $tags_elm->get_children();
		$tag_ids = array();
		if(is_array($tags_children['tag'])) {
			foreach($tags_children['tag'] as $key=>$tag_elm) {
				$tag_ids[] = $tag_elm->get_attribute('id', FALSE);
			}
		}
      
      $obj = new general_knowledgebase();   
      
      if($obj->add_tags($article_id, $tag_ids) === FALSE) {
         xml_output::error(0, 'Failed to add tags to knowledgebase article');
      }
      else {
         xml_output::success();
      }
   }        
}