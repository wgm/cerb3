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

$menuTitle = "Global Settings";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module=helpdesk"));

if($acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Global Settings", "Configure Helpdesk", cer_href("configuration.php?module=settings"));

if($acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Custom Statuses", "Manage Custom Statuses", cer_href("configuration.php?module=statuses"));

if($acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Upload Logo", "Branding", cer_href("configuration.php?module=branding"));

