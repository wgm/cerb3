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

$menuTitle = "Search Indexes";
$menuLinks = array();

$menuLinks[] = new CerMenuItem("includes/images/spacer.gif", "&lt;&lt; Back", "", cer_href("configuration.php?module=helpdesk"));

if($acl->has_priv(PRIV_CFG_INDEXES,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_search_index.gif", "Re-index Articles", "Rebuild Knowledgebase Index", cer_href("configuration.php?module=search_index&action=articles"));

if($acl->has_priv(PRIV_CFG_INDEXES,BITGROUP_3))
	$menuLinks[] = new CerMenuItem("includes/images/config/icon_search_index.gif", "Re-index E-mail", "Rebuild E-mail Index", cer_href("configuration.php?module=search_index&action=threads"));
