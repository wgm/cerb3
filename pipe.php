<?php
define("NO_SESSION",true); // [JAS]: Leave this true

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/gui_parser_log.php");
include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerPop3RawEmail.class.php");
include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerProcessEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/CerParserFail.class.php");

// [ddh] If mysql is not loaded in the CLI php, piping will fail silently
if (!extension_loaded("mysql")) exit("mysql extension not loaded.  Unable to continue.");

$process = new CerProcessEmail();
$email = "";
$stdin = fopen('php://stdin', 'r');
while (!feof($stdin)) {
	$email .= fread($stdin, 8192);
}

if(!empty($email)) {
	$pop3email = new CerPop3RawEmail($email);
	$result = $process->process($pop3email);
	if(!$result) { // re-fail...
		// [Philipp Kolmann]: write failure to log
		$size = intval(strlen($email) / 1000);
		$from = $pop3email->headers->from;
		$to = $pop3email->headers->_to_raw;
		$subj = $pop3email->headers->subject;

		$fail_id = CerParserFail::logFailureHeaders($to,$from,$subj,$process->last_error_msg,$size);

		if(empty($fail_id))
		return;

		CerParserFail::logFailureBody($fail_id,$email);
	}
}
?>
