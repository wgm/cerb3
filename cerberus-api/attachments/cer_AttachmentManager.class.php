<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

class cer_AttachmentManager {
	
	var $db = null;
	
	function cer_AttachmentManager() {
		$this->db = cer_Database::getInstance();
	}
	
	function getTotalAttachments() {
		$sql = "SELECT count(f.file_id) AS total_attachments FROM thread_attachments f";
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res)) {
			return $row["total_attachments"];
		}
		
		return 0;
	}
	
	function getTotalAttachmentsSize() {
		$sql = "SELECT sum(f.file_size) AS total_attachments_size FROM thread_attachments f";
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res)) {
			return $row["total_attachments_size"];
		}
		
		return 0;
	}
	
	function getAttachmentsByFilters($filters=array(),$max=100) {
		/* @var $db cer_Database */
		
		if(empty($filters))
			return array();
		
		$sql = "SELECT f.file_id, f.file_name, f.file_size, UNIX_TIMESTAMP(th.thread_date) AS file_date, th.thread_id, th.ticket_id, t.ticket_subject ".
			"FROM (`thread_attachments` f, `thread` th, `ticket` t) ".
			"WHERE f.thread_id = th.thread_id AND t.ticket_id = th.ticket_id ";
		
		foreach($filters as $f => $v) {
			switch($f) {
				case "name":
					$val = $v["value"];
					if(empty($val))
						break;
						
					if($v["oper"]=="equal") {
						$sql .= sprintf("AND f.file_name = %s",
							$this->db->escape($val)
						);
					} else { // like
						$sql .= sprintf("AND f.file_name LIKE %s",
							$this->db->escape("%" . $val . "%")
						);
					}
					break;
				case "size":
					$size = $v["value"];
					$unit = $v["unit"];
					$base = ($unit=="kb") ? 1000 : 1000000;
					$oper = $v["oper"];
					settype($size, "integer");
					settype($base, "integer");
					$val = $size * $base;

					settype($val, "integer");
					
					if(empty($val))
						break;
					
					if($oper=="gte") {
						$sql .= sprintf("AND f.file_size >= %d ",
							$val
						);
					} else {
						$sql .= sprintf("AND f.file_size <= %d ",
							$val
						);
					}
					break;
				case "date":
					$val = @date("Y-m-d 00:00:00", cer_DateTime::parseDateTime($v["value"]));
					if(empty($val))
						return;
						
					if($v["oper"]=="gte") {
						$sql .= sprintf("AND th.thread_date >= %s ",
							$this->db->escape($val)
						); 
					} else {
						$sql .= sprintf("AND th.thread_date <= %s ",
							$this->db->escape($val)
						);
					}
					break;
				case "resolved":
					$val = $v["value"];
					
					if(!empty($val)) {
						$sql .= "AND t.is_closed = 1";
					} else {
						$sql .= "AND t.is_closed = 0";
					}
					break;
			}
		}
		
		$sql .= " ORDER BY f.file_size DESC LIMIT 0,$max";
		
		$res = $this->db->query($sql);
		
		$attachments = array();
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$file = array();
					$file["id"] = $row["file_id"];
					$file["name"] = stripslashes($row["file_name"]);
					$file["size"] = $row["file_size"];
					$file["date"] = $row["file_date"];
					$file["thread_id"] = $row["thread_id"];
					$file["ticket_id"] = $row["ticket_id"];
					$file["ticket_subject"] = stripslashes($row["ticket_subject"]);
				$attachments[$file["id"]] = $file;
			}
		}
		
		return $attachments;
	}
	
};

?>
