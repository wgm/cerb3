<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| File: parser.php
|
| Purpose: E-mail parsing / XML classes
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted   (ben@webgroupmedia.com)   [BGH]
|
| Contributors:
|       Jeremy Johnstone (jeremy@scriptd.net)   [JSJ]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_SESSION",true); // [JAS]: Leave this true

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/gui_parser_log.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/CerProcessEmail.class.php");

// [JAS]: Pull in and clean the MTA parser's XML POST data
@$post_xml = $_REQUEST["xml"];
@$user = $_REQUEST["user"];
@$password = $_REQUEST["password"];

$xml_data = stripslashes(rawurldecode($post_xml));
unset($post_xml);

$o_raw_email = new CerXmlRawEmail(); //  new CERB_RAW_EMAIL();
$xml_handler = new CERB_XML_EMAIL_HANDLER($o_raw_email);
$process = new CerProcessEmail();
$cfg = CerConfiguration::getInstance();

// [JAS]: If we're running the "C" parser in secure mode, we must match the login and password from GUI:Configuration:Global Settings
if($cfg->settings["parser_secure_enabled"])
{
	if($cfg->settings["parser_secure_user"] == $user &&
	$cfg->settings["parser_secure_password"] == $password)
	{} // pass
	else // fail
	{
		$cer_log = new CER_GUI_LOG();
		$error_msg = sprintf("CERBERUS PARSER [ERROR]: Parser in Secure Mode did not match login/password during XML packet post.  Aborting without parsing.");
		$cer_log->log($error_msg);
		die($error_msg);
	}
}

$xml_handler->parser->read_xml_string($xml_data);
$o_raw_email->build_message(); // [JAS]: Populate attachments from their POST variables, RFC822 addresses, etc.

// Hand off the single raw e-mail to the e-mail processing class.
$process->process($o_raw_email);

$xml_handler->parser->free();
?>