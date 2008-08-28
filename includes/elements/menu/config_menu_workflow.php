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

$menuTitle = "Workflow";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module="));

if($acl->has_priv(PRIV_CFG_AGENTS_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_AGENTS_DELETE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/icone/16x16/businessman2.gif", "Agents", "Manage User Accounts", cer_href("configuration.php?module=users"));

if($acl->has_priv(PRIV_CFG_TEAMS_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_TEAMS_DELETE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Teams", "User Teams", cer_href("configuration.php?module=ws_teams"));

if($acl->has_priv(PRIV_CFG_TAGS_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_TAGS_DELETE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/icone/16x16/bookmark.gif", "Tags", "Content Tagging", cer_href("configuration.php?module=tags"));

	