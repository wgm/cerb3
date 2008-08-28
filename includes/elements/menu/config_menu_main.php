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

$menuTitle = "Components";
$menuLinks = array();

if($acl->has_priv(PRIV_CONFIG))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "E-mail / Parser &gt;", "Mail Gateway &amp; Accounts", cer_href("configuration.php?module=parser"));

if($acl->has_priv(PRIV_CONFIG))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Cerberus Helpdesk&trade; &gt;", "Web Edition / Workstation&trade; (Desktop)", cer_href("configuration.php?module=helpdesk"));

if($acl->has_priv(PRIV_CFG_AGENTS_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_TAGS_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_TEAMS_CHANGE))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_configuration.gif", "Workflow &gt;", "Tags / Users / Teams", cer_href("configuration.php?module=workflow"));
	
if($acl->has_priv(PRIV_CONFIG))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_support_center.gif", "Support Center &gt;", "Customer Web Portal", cer_href("configuration.php?module=support_center"));

