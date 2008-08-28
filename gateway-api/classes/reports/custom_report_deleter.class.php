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
|		Jeff Standen			jeff@webgroupmedia.com		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");

class custom_report_deleter
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;
   var $id;

   function custom_report_deleter($id) {
		$this->id = $id;
      $this->db =& database_loader::get_instance();
   }

	function delete_report() {
		return $this->db->Get("reports", "delete_saved_report", array("id"=>$this->id));
   }
}
