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

if(!isset($module)) $module = "";
switch ($module) {
	
	/*
	 * Helpdesk
	 */ 
	case "helpdesk":
	case "cron_config":
	case "cron_tasks":
	case "sla":
	case "schedules":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_helpdesk.php");
		break;

	case "global":
	case "settings":
	case "statuses":
	case "branding":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_settings.php");
		break;

	case "workflow":
	case "tags":
	case "ws_teams":
	case "ws_tags":
	case "ws_sets":
	case "users":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_workflow.php");
		break;
		
	case "customfields":
	case "custom_fields":
	case "custom_field_bindings":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_customfields.php");
		break;
		
	case "searchindex":
	case "search_index":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_searchindexes.php");
		break;
		
	case "development":
	case "bug":
	case "feedback":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_development.php");
		break;
		
	case "workstation":
	case "ws_key":
	case "ws_config":
	case "ws_routing":
	case "ws_reports":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_workstation.php");
		break;
		
	case "maint":
	case "maintenance":
	case "maintenance_optimize":
	case "maintenance_repair":
	case "maintenance_attachments":
	case "maintenance_tempdir":
	case "export":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_maintenance.php");
		break;
		
		
	/*
	 * E-mail Parser
	 */ 
	case "mail_settings":
	case "parser":
	case "parser_fails":
	case "parser_manual":
	case "pop3":
	case "queues":
	case "queue_catchall":
	case "rules":
	case "log":
	case "addresses":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_parser.php");
		break;
	
	
	/*
	 * Support Center
	 */ 
	case "support_center":
	case "public_gui_profiles":
	case "public_gui_fields":
	case "plugins":
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_supportcenter.php");
		break;
	
		
	/*
	 * Main Menu
	 */
	default:
		include_once(FILESYSTEM_PATH . "includes/elements/menu/config_menu_main.php");
		break;
}