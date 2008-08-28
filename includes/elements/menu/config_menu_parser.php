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

$menuTitle = "E-mail Parser";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module="));

if($acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_settings.gif", "Mail Settings", "Configure Mail Settings", cer_href("configuration.php?module=mail_settings"));

if($acl->has_priv(PRIV_CFG_POP3_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_POP3_DELETE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "POP3 Accounts", "External Mailboxes to Monitor", cer_href("configuration.php?module=pop3"));

if($acl->has_priv(PRIV_CFG_QUEUES_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_QUEUES_DELETE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_queues.gif", "Mailboxes", "Incoming Mail Folders", cer_href("configuration.php?module=queues"));

if($acl->has_priv(PRIV_CFG_QUEUES_CATCHALL,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_queues.gif", "Catchall Rules", "Default Mail Routing", cer_href("configuration.php?module=queue_catchall"));
	
if($acl->has_priv(PRIV_CFG_RULES_CHANGE,BITGROUP_2) || $acl->has_priv(PRIV_CFG_RULES_DELETE,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Mail Rules", "Pre/Post Parser Actions", cer_href("configuration.php?module=rules"));

if($acl->has_priv(PRIV_CFG_PARSER_FAILED,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Failed Messages", "Parser Failure Queue", cer_href("configuration.php?module=parser_fails"));

if($acl->has_priv(PRIV_CFG_PARSER_IMPORT,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Import Raw Message", "Manually Parse a Message", cer_href("configuration.php?module=parser_manual"));
	
if($acl->has_priv(PRIV_CFG_PARSER_LOG,BITGROUP_2))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Parser Log", "Logged Parser Events", cer_href("configuration.php?module=log"));

if($acl->has_priv(PRIV_BLOCK_SENDER))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_parser.gif", "Block Senders", "Ban E-mail Addresses", cer_href("configuration.php?module=addresses"));

