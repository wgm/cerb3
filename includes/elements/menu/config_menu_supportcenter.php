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

$menuTitle = "Support Center";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module="));

if($acl->has_priv(PRIV_CFG_SC_PROFILES,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Profiles", "Public Interfaces", cer_href("configuration.php?module=public_gui_profiles"));

if($acl->has_priv(PRIV_CFG_SC_PROFILES,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/icone/16x16/form_green.gif", "Custom Fields", "Custom User Input", cer_href("configuration.php?module=public_gui_fields"));
	
if($acl->has_priv(PRIV_CFG_SC_PROFILES,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_plugins.gif", "Plugins", "Login Managers", cer_href("configuration.php?module=plugins"));
