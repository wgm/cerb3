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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/window.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the iframe HTML for chatcontents
 *
 */
class getiframe_chatcontents_handler
{  
   /**
    * Class constructor
    *
    * @return getiframe_chatcontents_handler
    */
   function getiframe_chatcontents_handler() {
      // Nothing needed right now
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      $class_obj = new chat_window();
      
      $GUID = get_var('chatVisitor', FALSE, get_var('visitor', FALSE, NULL));
                     
      if($class_obj->getiframe_chatcontents($GUID) === FALSE) {
         html_output::error(0, 'Failed to get iframe for chatcontents!'); 
      }
      else {
         html_output::success();
      }
   }        
}