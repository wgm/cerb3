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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/elements/menu/CerMenuItem.class.php");

$menuTitle = "Workstation&trade;";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module=helpdesk"));

if($acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "License", "Add Users", cer_href("configuration.php?module=ws_key"));

if($acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Settings", "Settings / IP Security", cer_href("configuration.php?module=ws_config"));

if($acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/icone/16x16/document.gif", "Reports", "Desktop Custom Reporting", cer_href("configuration.php?module=ws_reports"));
	