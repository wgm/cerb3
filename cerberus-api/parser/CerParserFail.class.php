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

class CerParserFail {
	
	function logFailureHeaders($to,$from,$subj,$error_msg,$size) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("INSERT INTO `parser_fail_headers` (header_to,header_from,header_subject,date_created,error_msg,message_size) ".
			"VALUES (%s,%s,%s,%d,%s,%d) ",
				$db->escape($to),
				$db->escape($from),
				$db->escape($subj),
				gmmktime(),
				$db->escape($error_msg),
				$size
		);
		$db->query($sql);
		
		$fail_id = $db->insert_id();
		
		return $fail_id;
	}
	
	function logFailureBody($header_id,&$body) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(empty($header_id))
			return FALSE;
		
		$failFile = tempnam(realpath(FILESYSTEM_PATH . "tempdir"),"fail_"); 
//		$failFile = tempnam(FILESYSTEM_PATH . "tempdir", "fail_");
		
		$fp = fopen($failFile, "wb");
		if(!$fp) {
			return FALSE;
		}

		fwrite($fp,$body,strlen($body));
		fclose($fp);
		
		// [Philipp Kolmann]: make file world readable
		chmod($failFile, 0644);

		@$shortName = split('[\/\\]',$failFile);
		@$shortName = $shortName[count($shortName)-1];
		
		if(empty($shortName))
			return FALSE;
		
		$sql = sprintf("UPDATE `parser_fail_headers` SET `message_source_filename` = %s WHERE `id` = %d",
				$db->escape($shortName),
				$header_id
		);
		$db->query($sql);

		return TRUE;
	}
	
};