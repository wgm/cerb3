<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted	  (ben@webgroupmedia.com)	[BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/mail/RFC822.php");

class CerRawEmailAttachment
{
	var $post_names;
	var $tmp_files;
	var $filename;
	var $filesize;
	var $content_type;
	
	function CerRawEmailAttachment()
	{
		$this->post_names = array();
		$this->tmp_files = array();
		$this->filename = "";
		$this->filesize = 0;
		$this->content_type = "";
	}
	
	function add_postname()
	{
		$tmp_postname = "";
		array_push($this->post_names,$tmp_postname);
	}
};

class CerRawEmailHeaders
{
	var $cc;
	var $cc_raw;
	var $delivered_to;
	var $envelope_to;
	var $from;
	var $in_reply_to; // [BGH] added for proper proxying of watcher emails
 	var $message_id;
 	var $received;
	var $references; // [BGH] added for proper proxying of watcher emails
	var $reply_to; // [bgh] changed from return_path
	var $subject;
	var $date;
	/**
	 * Array of destination addresses
	 *
	 * @var array
	 */
	var $to;
	var $_to_raw;
	var $all = array(); // [JAS]: All email headers from the original ticket, assoc array
	
	function CerRawEmailHeaders()
	{
//					case "return-path":
//					case "date":
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->cc_raw = "";
		$this->bcc_raw = "";
		$this->delivered_to = "";
		$this->envelope_to = "";
		$this->from = "";
		$this->message_id = "";
		$this->received = "";
		$this->reply_to = ""; // [bgh] changed from return_path
		$this->subject = "";
		$this->_to_raw = "";
		$this->in_reply_to = "";
		$this->references = "";
		$this->user_agent = "";
		$this->date = "";
	}
	
};

class CerRawEmail
{
	var $headers;
	var $cerbmail_file;
	var $body;
	var $html_body;
	/**
	 * @var CerRawEmailAttachment[]
	 */
	var $attachments;
	var $message_type;
	var $parser_version = null;
	
	function CerRawEmail()
	{
		$this->clear();
	}
	
	function add_attachment()
	{
		$tmp_attachment = new CerRawEmailAttachment();
		array_push($this->attachments,$tmp_attachment);
	}
	
	function build_message()
	{
		$this->header_escape();
		$this->parse_addresses();
		$this->validate_headers();
		$this->writeParserVersion();
		$this->build_attachment_data();
	}

	function build_attachment_data() {
		/* OVERRIDE ME */
	}
	
	function header_escape()
	{
		$this->headers->subject = addslashes($this->headers->subject);
	}
	
	function validate_headers()
	{
		// [JAS]: Make sure we have content in the subject line -- if not, set a default
		if(empty($this->headers->subject)) $this->headers->subject = "no subject";
	}
	
	function generate_message_id() {
		global $_SERVER;
		srand((double)microtime()*10000000);
		return sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);	
	}
	
	function parse_addresses()
	{
  	$RFC822 = new Mail_RFC822();
		$address = $RFC822->parseAddressList(trim($this->headers->_to_raw), 'localhost', TRUE);
		if(is_array($address)) {
			foreach($address as $addy) {
				array_push($this->headers->to, $addy->mailbox . "@" . $addy->host); 
			}
		}

		if(!empty($this->headers->cc_raw)) {
			$address = $RFC822->parseAddressList(trim($this->headers->cc_raw), 'localhost', TRUE);
			if(is_array($address))
				foreach($address as $addy) { 
					array_push($this->headers->cc, $addy->mailbox . "@" . $addy->host);
				}
		}
		
		if(!empty($this->headers->envelope_to)) {
			$address = $RFC822->parseAddressList(trim($this->headers->envelope_to), 'localhost', TRUE);
			$this->headers->envelope_to = $address[0]->mailbox . "@" . $address[0]->host;
		}

		if(!empty($this->headers->delivered_to)) {
			$address = $RFC822->parseAddressList(trim($this->headers->delivered_to), 'localhost', TRUE);
			$this->headers->delivered_to = $address[0]->mailbox . "@" . $address[0]->host;
		}

		if(!empty($this->headers->from)) {
			$address = $RFC822->parseAddressList(trim($this->headers->from), 'localhost', TRUE);
			$this->headers->from = $address[0]->mailbox . "@" . $address[0]->host;
		}

		if(!empty($this->headers->reply_to)) { // [bgh] changed from return_path
			$address = $RFC822->parseAddressList(trim($this->headers->reply_to), 'localhost', TRUE); // [bgh] changed from return_path
			$this->headers->reply_to = $address[0]->mailbox . "@" . $address[0]->host; // [bgh] changed from return_path
		}
	}
	
	function writeParserVersion() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if (!empty($this->parser_version)) {
			$sql = sprintf("UPDATE `configuration` SET `parser_version` = %s",
				$db->escape($this->parser_version)
			);
			$db->query($sql);
		}
	}
	
	function clear()
	{
		$this->cerbmail_file = null;
		$this->body = null;
		$this->html_body = null;
		$this->headers = new CerRawEmailHeaders();
		$this->message_type = null;
		$this->attachments = array();
	}

	function import_thread_attachments($thread)
	{
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$tmp_dir_path = FILESYSTEM_PATH . "tempdir";
		
		$sql = sprintf("SELECT f.file_id, f.file_name, f.file_size FROM thread_attachments f ".
			"LEFT JOIN thread th ON (f.thread_id = th.thread_id) ".
			"WHERE th.thread_id = %d",
				$thread
		);
		$file_res = $db->query($sql);
		
		if($db->num_rows($file_res))
		while($file_row = $db->fetch_row($file_res))
		{
			if($file_row["file_name"] == "message_source.xml"
			|| $file_row["file_name"] == "html_mime_part.html"
			|| $file_row["file_name"] == "message_headers.txt")
				continue;
				
			$new_attachment = new CerRawEmailAttachment();
			$new_attachment->filename = $file_row["file_name"];
			$new_attachment->filesize = $file_row["file_size"];
			
			$sql = "SELECT p.part_id, p.file_id, p.part_content FROM thread_attachments_parts p ".
				sprintf("WHERE p.file_id = %d ",$file_row["file_id"]) . 
				"ORDER BY p.part_id ";
			$part_res = $db->query($sql);
			
			if($db->num_rows($part_res))
			while($part_row = $db->fetch_row($part_res))
			{
				$tmp_name = tempnam(realpath(FILESYSTEM_PATH . "tempdir"),"cerb"); 
//				$tmp_name = tempnam($tmp_dir_path, "cerb");
				$fp = fopen($tmp_name,"w");
				if($fp)
				{
					fwrite($fp,$part_row["part_content"],strlen($part_row["part_content"]));
					fclose($fp);
					array_push($new_attachment->tmp_files,$tmp_name);
				}
			}
			
			array_push($this->attachments,$new_attachment);
		}
		
		unset($file_res);
		unset($part_res);
	}
	
	/**
	 * Nuke any resources our message has tied up (temporary files, etc)
	 *
	 */
	function _cleanupResources() {
		if(!empty($this->attachments)) {
			foreach($this->attachments as $file) { /* @var $file CerRawEmailAttachment */
				if(empty($file->tmp_files))
					continue;
				
				foreach($file->tmp_files as $tmp_file) {
					@unlink($tmp_file);
				}
			}
		}
	}
	
};
