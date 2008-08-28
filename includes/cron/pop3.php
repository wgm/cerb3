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
| File: parser.php
|
| Purpose: E-mail parsing / XML classes
|
| Developers involved with this file:
|               Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|               Ben Halsted   (ben@webgroupmedia.com)   [BGH]
|
| ______________________________________________________________________
|       http://www.cerberusweb.com        http://www.webgroupmedia.com/
***********************************************************************/

//define("NO_SESSION",true); // [JAS]: Leave this true

if(!defined("FILESYSTEM_PATH") || !defined("CER_CRON_RUNTIME")) {
	die("This script should not be run directly.  Run cron.php");
}

define("POP3_MAX_MSGS", 10);
define("POP3_MAX_SIZE", 2000000);
$verbose = !empty($_REQUEST['verbose']) ? 1 : 0;
$show_bodies = !empty($_REQUEST['show_bodies']) ? 1 : 0;

require_once(FILESYSTEM_PATH . "cerberus-api/parser/CerProcessEmail.class.php");

$process = new CerProcessEmail();

require_once(FILESYSTEM_PATH . "cerberus-api/pop3/cer_Pop3.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/pop3/CerPop3Accounts.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/entity/model/CerPop3Account.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/CerPop3RawEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/CerParserFail.class.php");

$pop3acct = new CerPop3Accounts();
$pop3list = $pop3acct->getList();

$start = microtime();
$timeout_secs = intval(ini_get("max_execution_time"));
$total_bytes = 0;

if(is_array($pop3list))
foreach($pop3list as $pop3info) /* @var $pop3info CerPop3Account */
{
	$max_messages = ($pop3info->getMaxMessages()) ? $pop3info->getMaxMessages() : POP3_MAX_MSGS;
	$max_size = ($pop3info->getMaxSize()) ? $pop3info->getMaxSize() : POP3_MAX_SIZE;
	
	// [JAS]: POP3 mailbox lock check
	if(($pop3info->getLockTime() + (5*60)) > mktime()) { // lock for 5 mins
		if($verbose) echo "Locked by another process.<br>";
		continue;
	}
	
	/* @var $pop3info CerPop3Account */
	if(!$pop3info->getDisabled()) {
		$client = new cer_Pop3Client($pop3info->getHost(),$pop3info->getPort(),$pop3info->getLogin(),$pop3info->getPass(),30);
		$client->setDebug(false);

		if($client->connect()) {
//			if($client->debug) echo "Connected...<br>";
			if($client->pop3_user()) {
//				if($client->debug) echo "Sent User...<br>";
				if($client->pop3_pass()) {
//					if($client->debug) echo "Sent Password...<br>";
					if($client->pop3_stat()) {
//						if($client->debug) echo "Stat... " . $client->messageCount . " messages on the server.<br>";
						
						$pop3info->setLockTime(mktime());
						$pop3acct->save($pop3info);

						$runs = 0;
						if(is_array($client->messageListArray))
						foreach($client->messageListArray as $id => $size) {
							if($runs >= $max_messages)
								continue;
							if($size > $max_size) // [TODO] log to disk/db/report?
								continue;

							if($verbose) { echo "Retrieving message " . $id . " ($size bytes)...<br>"; flush(); }
							
							// [JAS]: Decide if we have enough time for another message based on timeout and our average
							$runtime = microtime_diff($start,microtime());
							$bps = ($total_bytes/$runtime);
							$est_process_secs = ($bps) ? ($size/$bps) : 0;
							if($verbose) { echo "We've run for " . $runtime . " secs (timeout: $timeout_secs secs, bytes: $total_bytes) (bps: " . $bps . ")<BR>"; flush(); }
							if($verbose) { echo "Estimated process time: " . $est_process_secs  . " secs<br>"; flush(); }
							
							// [JAS]: If we have a time limit, see if this message will fit.  If less than a second left always bail out.
							if($timeout_secs && (($timeout_secs-$runtime <= 1) || ($timeout_secs - $runtime) < (1.2 * $est_process_secs))) {
								if($verbose) { echo "Guessing there isn't enough time left to process properly.  Shutting down this run early.<br>"; flush(); }
								break;
							}

							$email = $client->pop3_retr($id);
							
							if($verbose && $show_bodies) { echo "<textarea rows=10 cols=80>" . htmlentities($email) . "</textarea><br>"; flush(); }
							
							if($verbose) { echo "Pre-rawemail " . $id . "...<br>"; flush(); }
							$pop3email = new CerPop3RawEmail($email);
							if($verbose) { echo "Post-rawemail " . $id . "...<br>"; flush(); }

							$from = $pop3email->headers->from;
							$to = $pop3email->headers->_to_raw;
							$subj = $pop3email->headers->subject;
							
							if($verbose) { echo "<b>To:</b> " . $to . "<BR>"; flush(); }
							if($verbose) { echo "<b>From:</b> " . $from . "<BR>"; flush(); }
							if($verbose) { echo "<b>Subject:</b> " . $subj . "<BR>"; flush(); }

//							echo "RAW EMAIL: "; print_r($email); flush();
							
							// Hand off the single raw e-mail to the e-mail processing class.
							if($verbose) { echo "Pre-process " . $id . "...<br>"; flush(); }
							$result = $process->process($pop3email);
							if($verbose) { echo "Post-process " . $id . "...<br>"; flush(); }
							
							if(!$result) { // failed on message
								if($verbose) { echo "<font color='red'>ERROR: ".$process->last_error_msg."</font><br>"; flush(); }
								$size = intval(strlen($email) / 1000);
								$fail_id = CerParserFail::logFailureHeaders($to,$from,$subj,$process->last_error_msg,$size);

								if(empty($fail_id))
									return;
									
								CerParserFail::logFailureBody($fail_id,$email);
								
							} else { // success!
							}

							// [JAS]: delete either for success or fail
							if($pop3info->getDelete()) { // delete on success
								 $client->pop3_dele($id);
								 if($verbose) echo "Deleting message $id from mailbox.<br>";
							}
							
							flush();
							if($verbose) echo "<HR>";
							
							unset($email);
							unset($pop3email);
							$runs++;
							$total_bytes += $size;
						}
						
						$pop3info->setLastPolled(mktime());
						$pop3info->setLockTime(0);
						$pop3acct->save($pop3info);
					}
				}
			}
			
			$client->pop3_quit();
		}

		// [bgh] disconnect from the server
		$client->disconnect();
	}
}