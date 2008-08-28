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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . 'cerberus-api/parser/CerRawEmail.class.php');
require_once(FILESYSTEM_PATH . 'cerberus-api/mail/mimeDecode.php');

class CerPop3RawEmail extends CerRawEmail {
	var $structure;

	function CerPop3RawEmail($emailText) {
		$this->CerRawEmail();

		$params = array('include_bodies' => true, 'decode_bodies' => true, 'decode_headers' => true);
		$emailText = str_replace(chr(0),"",$emailText);
		
		if(FALSE!==$email) {
			$decoder = new Mail_mimeDecode($emailText);
			$this->structure = $decoder->decode($params);
//echo("<hr>");
//print_r($this->structure);
//echo("<hr>");
			$this->_loadEmail();
			$this->build_message();
		}
	}

	function _getHeaderValue($header) {
		if(is_array($header)) {
			return implode(" ",$header);
		} else {
			return $header;
		}
	}


	function _loadEmail() {

		// [bgh]  load the email headers
		/* @var $headers CerRawEmailHeaders */
		$strHeaders = $this->structure->headers;
		
		if(null !== $strHeaders) {
			foreach($strHeaders as $key => $value) {

				unset($ptr);
				$ptr = null;

				switch (strtolower($key)) {
					case "to":
						$ptr =& $this->headers->_to_raw;
						break;
					case "cc":
						$ptr =& $this->headers->cc_raw;
						break;
					case "bcc":
						break;
					case "delivered-to":
						$ptr =& $this->headers->delivered_to;
						break;
					case "return-path":
						break;
					case "reply-to":
						$ptr =& $this->headers->reply_to;
						break;
					case "received":
						$ptr =& $this->headers->received;
						break;
					case "message-id":
						$ptr =& $this->headers->message_id;
						break;
					case "date":
						$ptr =& $this->headers->date;
						break;
					case "from":
						$ptr =& $this->headers->from;
						break;
					case "user-agent":
						break;
					case "subject":
						$ptr =& $this->headers->subject;
						break;
					case "envelope-to":
						$ptr =& $this->headers->envelope_to;
						break;
					case "in-reply-to":
						$ptr =& $this->headers->in_reply_to;
						break;
					case "references":
						$ptr =& $this->headers->references;
						break;
					default:
						break;
				}

				if(null !== $ptr) {
					$ptr .= $this->_getHeaderValue($value);
				}
			}
		}

		// [bgh] load the email bodies
		if(isset($this->structure->parts)) {
			$this->_readMultipartEmail($this->structure->parts);
		} else {
			if(isset($this->structure->ctype_secondary) && "html"==$this->structure->ctype_secondary) { // html
				$this->html_body .= $this->structure->body;
				$this->_savePartAsAttachment($this->structure,"html_mime_part.html");
			} elseif(isset($this->structure->ctype_secondary) && "base64"==$this->structure->ctype_secondary) { // file
				if(isset($this->structure->d_parameters['filename'])) {
					$filename = $this->structure->d_parameters['filename'];
					$this->_savePartAsAttachment($this->structure,$filename);
				}
			} else {
				$bounceToken = "--- Below this line is the original bounce.";
				if(false !== strpos(strtoupper($this->headers->from),"MAILER-DAEMON@") 
					&& false !== ($pos = strpos($this->structure->body,$bounceToken))) { // bounce
						$this->body = substr($this->structure->body,0,$pos);
						$bounceBody = substr($this->structure->body,$pos);
						
						$params = array();
						$params['content_type'] = 'text/plain';
						$params['encoding']     = '8bit';
						$params['disposition']  = 'attachment';
						$bouncePart = new Mail_mimePart('', $params);
						$bouncePart->body = $bounceBody;
						$this->_savePartAsAttachment($bouncePart,"bounce.txt");
				} else { // body
					$this->body .= $this->structure->body;
				}
			}
		}

		require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");
		$strip_html = new cer_StripHTML();
		$this->headers->subject = $strip_html->strip_html($this->headers->subject);

		// [JAS]: If we don't have a plaintext message body, use the HTML part (bad e-mail client!)
		if(empty($this->body) && !empty($this->html_body))
		{
			$this->body = $strip_html->strip_html($this->html_body);
		}
	}

	function _readMultipartEmail($parts) {

		$partArray[] = $parts;

		while(null!=($slice=array_shift($partArray))) {
			foreach ($slice as $part) {
				if(isset($part->parts)) {
					$partArray[] = $part->parts;
				} else {
					// [bgh] text or file
					if(!$this->_isPartFile($part)) {

						// [bgh] is a text block
						if(isset($part->ctype_secondary) && "html"==$part->ctype_secondary) { // html
							$this->html_body .= $part->body;
							$this->_savePartAsAttachment($part);
						} else { // plaintext
							$this->body .= $part->body;
						}
					} else { // file
						$this->_savePartAsAttachment($part);
					}
				}
			}
		}
	}

	/*
	 * [JAS]: [TODO] This needs to split up attachments into 512KB chunks like the C Parser
	 */
	function _savePartAsAttachment($part,$filename="") {
		$attachment = new CerRawEmailAttachment();
		$fptr = null;

		$filesize = strlen($part->body);
		$chunksize = 500000;
		$chunks = ceil($filesize / $chunksize);
		$bytes = 0;
		$end = 0;
		
		for($x=0;$x<$chunks;$x++) {
			$tmpfile = tempnam(realpath(FILESYSTEM_PATH . "tempdir"),"cerbmime"); 
//			$tmpfile = tempnam(FILESYSTEM_PATH . 'tempdir','cerbmime');
			$fptr = fopen($tmpfile, "wb");
			$attachment->tmp_files[] = $tmpfile;
			$pos = ($end>0) ? $end : 0;
			$end = $pos+$chunksize;
			$buffer = ($x+1==$chunks) ? substr($part->body,$pos) : substr($part->body,$pos,$chunksize);
			$bytes += fwrite($fptr,$buffer,strlen($buffer));
			@fclose($fptr);
		}

		$attachment->filesize = $bytes;
		$attachment->filename = (empty($filename)?$this->_getPartFileName($part):$filename);
		$attachment->content_type = $this->_getPartContentType($part);

		$this->attachments[] = $attachment;
	}

	function _isPartFile($part) {
		if( (isset($part->ctype_parameters['name']) || isset($part->d_parameters['filename']))
		||
		!(isset($part->ctype_primary) && "text"==$part->ctype_primary)
		) {
			return true;
		} else {
			return false;
		}
	}

	function _getPartFileName($part) {
		if(!empty($part->ctype_parameters['name'])) {
			return $part->ctype_parameters['name'];
		} else if(!empty($part->d_parameters['filename'])) {
			return $part->d_parameters['filename'];
		} else {
			// [bgh] make temp name

			$filename = rand(10000,99999);

			if(!empty($part->ctype_primary)) {
				$filename = $part->ctype_primary . '_' . $filename;
			}

			if(!empty($part->ctype_secondary)) {
				$filename .= '.' . $part->ctype_secondary;
			} else {
				$filename .= '.___';
			}

			return $filename;
		}
	}

	function _getPartContentType($part) {
		$contentType = "";

		if(isset($part->ctype_primary) && 0<strlen(isset($part->ctype_primary))) {
			$contentType = $part->ctype_primary . '/';
		} else {
			$contentType = "application/";
		}

		if(isset($part->ctype_secondary) && 0<strlen(isset($part->ctype_secondary))) {
			$contentType .= $part->ctype_secondary;
		} else {
			$contentType .= 'octet-stream';
		}
		
		return $contentType;
	}

	// [ddh]: the pop3 parser uses _saveAttachment to get the
	// attachments off the email, but we need to use this to grab the
	// headers and save them as if the config option is set.
	function build_attachment_data()
	{
		// [ddh]: Attach headers if config demands it
		$cfg = CerConfiguration::getInstance();
		$strHeaders = var_export($this->structure->headers, true);

		if("1" == $cfg->settings["save_message_xml"])
		{
			$header_attachment = new CerRawEmailAttachment();
			$header_attachment->filename = "message_headers.txt";
			$header_attachment->filesize = strlen($strHeaders);
			$header_attachment->content_type = "text/plain";
			$tmp_name = tempnam(realpath(FILESYSTEM_PATH . "tempdir"),"cerb"); 
			$fp = fopen($tmp_name,"w");
			if($fp)
			{
				fwrite($fp,$strHeaders,strlen($strHeaders));
				fclose($fp);
				array_push($header_attachment->tmp_files,$tmp_name);
				array_push($this->attachments,$header_attachment);
			}
		}
	}	
	
}
