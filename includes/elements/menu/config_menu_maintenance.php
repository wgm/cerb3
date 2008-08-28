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

$menuTitle = "Maintenance";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module=helpdesk"));

if($acl->has_priv(PRIV_CFG_MAINT_REPAIR,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Optimize Database", "Recover Space / Sort", cer_href("configuration.php?module=maintenance_optimize"));

if($acl->has_priv(PRIV_CFG_MAINT_REPAIR,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Repair Database", "Fix Broken Tables", cer_href("configuration.php?module=maintenance_repair"));

if($acl->has_priv(PRIV_CFG_MAINT_PURGE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Empty Trash", "Purge Dead Tickets", cer_href("configuration.php?module=maintenance"));

if($acl->has_priv(PRIV_CFG_MAINT_ATTACH,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Clean-up Attachments", "Purge Old Attachments", cer_href("configuration.php?module=maintenance_attachments"));

if($acl->has_priv(PRIV_CFG_MAINT_PURGE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Clean-up Temporary Files", "Purge Temporary Workspace", cer_href("configuration.php?module=maintenance_tempdir"));

if($acl->has_priv(PRIV_DATA_IO,BITGROUP_2) && !DEMO_MODE)
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Export Addresses", "Save E-mail Address List", cer_href("configuration.php?module=export"));
