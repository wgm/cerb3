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

class custom_report_saver
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;
   var $id;
   var $name;
   var $data;

   function custom_report_saver($id, $name, $data) {
     $this->db =& database_loader::get_instance();
	  $this->id = $id;
	  $this->name = $name;
	  $this->data = $data;
   }

	function save_report() {
		$result = $this->db->Get("reports", "get_saved_report_by_id", array("id"=>$this->id));
		
		if(empty($result)) { 
			$this->id = $this->db->Get("reports", "create_saved_report", array());
		}
		else {
			$this->id = $result[0]['report_id'];
		} 

		$this->db->Get("reports", "update_saved_report", array("id"=>$this->id, "name"=>$this->name, "data"=>$this->data));

		return $this->id;
   }
}
