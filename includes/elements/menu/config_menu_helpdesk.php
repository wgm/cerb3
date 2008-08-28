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

$menuTitle = "Helpdesk";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module="));

if($acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Settings &gt;", "Global Settings / Branding", cer_href("configuration.php?module=global"));

if($acl->has_priv(PRIV_CFG_SCHED_TASKS,BITGROUP_2))	
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_sched_task.gif", "Scheduled Tasks", "Timed Behavior", cer_href("configuration.php?module=cron_config"));

if($acl->has_priv(PRIV_CFG_MAINT_REPAIR,BITGROUP_2) || $acl->has_priv(PRIV_CFG_MAINT_PURGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_MAINT_ATTACH,BITGROUP_2) || $acl->has_priv(PRIV_DATA_IO,BITGROUP_2))	
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_maintenance.gif", "Maintenance &gt;", "Empty Trash / Fix Database", cer_href("configuration.php?module=maint"));

if($acl->has_priv(PRIV_CFG_CUSTOM_FIELDS,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/icone/16x16/form_green.gif", "Custom Fields &gt;", "Add Custom Data", cer_href("configuration.php?module=customfields"));

if($acl->has_priv(PRIV_CFG_SLA_CHANGE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_sla.gif", "Service Level (SLA)", "Response Times", cer_href("configuration.php?module=sla"));

if($acl->has_priv(PRIV_CFG_SCHEDULES,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_sla.gif", "Schedules", "Working Hours", cer_href("configuration.php?module=schedules"));

if($acl->has_priv(PRIV_CFG_INDEXES,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_search_index.gif", "Search Indexes &gt;", "Rebuild Indexes", cer_href("configuration.php?module=searchindex"));

if($acl->has_priv(PRIV_CONFIG))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_development.gif", "Development", "Report Bug / Feedback", cer_href("configuration.php?module=development"));

if($acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/icone/16x16/workstation_network.gif", "Workstation&trade;", "Setup", cer_href("configuration.php?module=workstation"));

