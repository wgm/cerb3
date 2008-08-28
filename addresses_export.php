<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: addresses_export.php
|
| Purpose: Exports the stored e-mail addresses from the database in the 
|   format and with the options selected by the user. 
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

// [JAS]: Includes
require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

// [JAS]: Set up the local variable scope from the scope objects.
@$form_submit = $_REQUEST["form_submit"];
@$qs = $_REQUEST["queues"];
CerSecurityUtils::integerArray($qs);
@$queues = implode(",",$qs);
@$delimiter = $_REQUEST["delimiter"];
@$file_type = $_REQUEST["file_type"];

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_DATA_IO,BITGROUP_2)) {
	die("Permission denied.");
}

if(count($queues)==0) { echo "CERBERUS [ERROR]: No queues selected."; exit; }

// [JAS]: If we're exporting to a file, and not screen, kill cache.
if($file_type=="file") session_cache_limiter('public');

if(!empty($form_submit)) // [JAS]: process incoming form
	{
	if(DEMO_MODE) exit;
	
	if($file_type == "screen") echo "<PRE>";
	 // [JAS]: Load up current user preferences

		if ($file_type == "file"){
			header("Content-Type: application/download\n");
      header("Content-Disposition: inline; filename=\"" . "cerberus_address_dump.txt" . "\"");
		} 
  
	 	$sql = "SELECT DISTINCT a.address_address FROM ticket t INNER JOIN address a ON (t.opened_by_address_id=a.address_id) WHERE t.ticket_queue_id IN ($queues) ORDER BY a.address_address ASC;";
		$result_tickets = $cerberus_db->query($sql,false);																
		   $rows=0;
					if($cerberus_db->num_rows($result_tickets) > 0){
     		   while($ticketrow = $cerberus_db->fetch_row($result_tickets)){
								$rows++;
						 if ($delimiter == "comma"){
						  if(!empty($ticketrow[0])) {
              		echo $ticketrow[0];
									if($rows<$cerberus_db->num_rows($result_tickets)) echo ",";
                }
						 } else {
						  echo $ticketrow[0]; echo ($file_type=="screen") ? "\r\n" : "\r\n";
						 }
					}
			}
	if($file_type == "screen") echo "</PRE>";
exit;	
}	
