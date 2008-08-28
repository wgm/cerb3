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
| File: configuration.php
|
| Purpose: The core page for handling configuration changes.  This page
|   displays the different configuration sections and processes their
|   changes.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|		Trent Ramseyer		(trent@webgroupmedia.com)		[TAR]
| 		Ben Halsted			(ben@webgroupmedia.com)			[BGH]
|
| Contributors:
|		J. X Demel			Forums							[JXD]
|		Philipp Kolmann     (kolmann@zid.tuwien.ac.at)		[PK]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");

require_once(FILESYSTEM_PATH . "cerberus-api/mail/cerbHtmlMimeMail.php");

require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicGUISettings.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/login/cer_LoginPluginHandler.class.php");

log_user_who_action(WHO_CONFIG);

function display_bytes_size($bytes_size) {
	$in_MB = false;
	if($bytes_size == 0) return "0KB";
	$str_size = sprintf("%d",$bytes_size/1000);
	if($str_size == 0) $str_size = "&lt; 1";
	if($str_size >= 1000) { $str_size = sprintf("%0.2f",$str_size/1048); $in_MB = true; } // turn to MB
	return sprintf("%s%s",(is_numeric($str_size)?sprintf("%0.1f",$str_size):$str_size), (($in_MB)?"MB":"KB"));
}

//*************************************************
// [JAS]: Set Variable Scopes
require_once(FILESYSTEM_PATH . "includes/elements/config_main_scope.php");

// [JAS]: Verify that the connecting user has access to modify configuration/queue values
$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CONFIG))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$new_pm = $session->vars["login_handler"]->has_new_pm;
$page = "configuration.php";

// [JAS]: Check to see if we're processing a form from one of the configuration
//		options
require_once(FILESYSTEM_PATH ."includes/elements/config_main_submit.php");

?>
<html>
<head>
	<title><?php echo LANG_HTML_TITLE; ?></title>
	<META HTTP-EQUIV="content-type" CONTENT="<?php echo LANG_CHARSET; ?>">

	<style>
		<?php require("cerberus.css"); ?>
	</style>

	<link rel="stylesheet" href="skins/fresh/cerberus-theme.css" type="text/css">
	<link rel="stylesheet" href="includes/cerberus_2006.css" type="text/css">
	<link rel="stylesheet" href="includes/css/container.css?v=<?php echo GUI_BUILD; ?>" type="text/css"> 
	
	<script language="javascript1.2" src="includes/scripts/listbox.js"></script>
	<script type="text/javascript" src="includes/scripts/yahoo/YAHOO.js?v=<?php echo GUI_BUILD; ?>" ></script>
	<script type="text/javascript" src="includes/scripts/yahoo/event.js?v=<?php echo GUI_BUILD; ?>" ></script>
	<script type="text/javascript" src="includes/scripts/yahoo/connection.js?v=<?php echo GUI_BUILD; ?>" ></script>
	<script type="text/javascript" src="includes/scripts/yahoo/dom.js?v=<?php echo GUI_BUILD; ?>" ></script>
	<script type="text/javascript" src="includes/scripts/yahoo/autocomplete.js?v=<?php echo GUI_BUILD; ?>" ></script>
	<script type="text/javascript" src="includes/scripts/yahoo/dragdrop.js?v=<?php echo GUI_BUILD; ?>" ></script>
	<script type="text/javascript" src="includes/scripts/yahoo/container.js?v=<?php echo GUI_BUILD; ?>" ></script>
	
	<script type="text/javascript" src="includes/scripts/cerb3/knowledgebase.js?v=<?php echo GUI_BUILD; ?>" ></script>
	
</head>

<body bgcolor="#FFFFFF">

<?php require(FILESYSTEM_PATH . "header.php"); ?>
<br>

<table width="100%" border="0" cellspacing="1" cellpadding="5">
  <tr>
    <td width="175" valign="top">
    	<?php require_once(FILESYSTEM_PATH ."includes/elements/menu/config_menu_chooser.php"); ?>
    	<?php require_once(FILESYSTEM_PATH ."includes/elements/menu/config_menu_draw.php"); ?>
    </td>
    <td valign="top">
    	<?php require_once(FILESYSTEM_PATH ."includes/elements/config_main_frame.php"); ?>
	</td>
 </tr>
</table>

<?php require(FILESYSTEM_PATH . "footer.php"); ?>

</body>
</html>
