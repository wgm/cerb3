<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: index.php
|
| Purpose: The main page of the helpdesk system, providing navigation to
|   all functional areas of the system.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/dashboard/CerDashboards.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/i18n/languages.php");
require_once(FILESYSTEM_PATH . "cerberus-api/acl/CerACL.class.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/search/ticket_search.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/whos_online.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/notification/CerNotification.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");

log_user_who_action(WHO_ON_HOME);

// [JAS]: Create the Cerberus objects we'll need on the page and includes
$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_format = new cer_formatting_obj;
$cerberus_translate = new cer_translate;
$cerberus_disp = new cer_display_obj;
$cerberus_db = cer_Database::getInstance();
$acl = CerACL::getInstance();

$customfields = new cer_CustomFieldGroupHandler();
$customfields->loadGroupTemplates();
$cer_tpl->assign_by_ref('customfields',$customfields);

@$dashid = $_REQUEST["dashid"];

$dashboards = new CerDashboards();
$search = new CerSearch();

if(!empty($dashid)) {
	$session->vars['dashboard_id'] = $dashid;
} elseif (!empty($session->vars['dashboard_id'])) {
	$dashid = $session->vars['dashboard_id'];
} elseif(!empty($session->vars["login_handler"]->user_prefs->page_layouts["default_dashboard_id"])) {
	$dashid = $session->vars["login_handler"]->user_prefs->page_layouts["default_dashboard_id"];
	$session->vars['dashboard_id'] = $dashid;
} else {
	$dashid = 0;
	$session->vars['dashboard_id'] = $dashid;
}

$cer_tpl->assign("dashboard_id",$dashid);

// [JAS]: Setup up the local variables from the scope objects
@$view_submit = $_REQUEST["view_submit"];

@$form_submit = $_REQUEST["form_submit"];

$errorcode = isset($_REQUEST["errorcode"]) ? strip_tags($_REQUEST["errorcode"]) : "";
$errorvalue = isset($_REQUEST["errorvalue"]) ? strip_tags($_REQUEST["errorvalue"]) : "";

if(!empty($form_submit))
{
	switch($form_submit) {
		
		case "create_ticket":
			if(!$acl->has_priv(PRIV_TICKET_CHANGE))
				return;
			
			@$nt_to = stripslashes($_REQUEST['nt_to']);
			@$nt_from = stripslashes($_REQUEST['nt_from']);
			@$nt_subject = stripslashes($_REQUEST['nt_subject']);
			@$nt_body = stripslashes($_REQUEST['nt_body']);
			@$nt_send_copy = $_REQUEST['nt_send_copy'];
			@$nt_no_autoreply = $_REQUEST['nt_no_autoreply'];
			@$nt_no_notifications = $_REQUEST['nt_no_notifications'];
			@$files = $_FILES['replyFile'];
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
			
			$options = array(
				"NO_AUTOREPLY" => !empty($nt_no_autoreply) ? true : false,
				"CC_REQUESTER" => !empty($nt_send_copy) ? true : false,
				"NO_NOTIFICATIONS" => !empty($nt_no_notifications) ? true : false
			);
			
			$attachments = array();
			if(is_array($files)) {
				for($x=0;$x<count($files[name]);$x++) {
			      $attachment = array();
			      $attachment['file_name'] = $files['name'][$x];
			      $attachment['file_type'] = $files['type'][$x];
			      $attachment['tmp_file'] = $files['tmp_name'][$x];
			      if($files['name'][$x]) { // strlen($attachment['data']) > 0
			         $attachments[] = $attachment;
			      }
			   }
			}
			
			$id = CerWorkstationTickets::create($nt_to,$nt_subject,$nt_body,$nt_from,$attachments,$options);
			$ticket_url = sprintf(cer_href("display.php?ticket=%d"),$id);

			// [JAS]: [TODO] Check user privs for queue.
//			if(isset($acl->queues[])) {
				echo sprintf("<html><head><meta http-equiv='refresh' content='1;url=%s'><link rel='stylesheet href='includes/cerberus_2006.css?v=' type='text/css'></head><body><a href='%s'>Ticket %d created! Redirecting...</a></body></html>",
					$ticket_url,
					$ticket_url,
					$id
				);
				exit;
//			}
			
			break;
		
		case "save_layout":
			$default_dashboard_id = (isset($_REQUEST["default_dashboard_id"])) ? $_REQUEST["default_dashboard_id"] : 0;
			$session->vars["login_handler"]->user_prefs->page_layouts["default_dashboard_id"] = $default_dashboard_id;			
		
			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			$errorcode = "Page layout saved!";
			break;
			
		case "add_view":
			$uid = $session->vars["login_handler"]->user_id;
			$vid = $dashboards->createView($dashid,$uid);
			$builder = new CerSearchBuilder();
			$builder->add('status',array('status'=>1)); // active
			CerTicketViewHelper::saveParams($vid,$builder->criteria);
			break;
		
		case "default_dashboard":
			$uid = $session->vars["login_handler"]->user_id;
			$agent_name = $session->vars["login_handler"]->user_name;
			
			// [JAS]: New Dashboard
			$dashid = $dashboards->create("My Dashboard",$uid);
			$session->vars['dashboard_id'] = $dashid;
			
			// [JAS]: View: Available work
			$vid = $dashboards->createView($dashid,$uid,'Unassigned Tickets',1);
			$builder = new CerSearchBuilder();
			$builder->add('status',array('status'=>1)); // active
			$builder->add('flag',array('flag_mode'=>0,'flags'=>array())); // no flags
			CerTicketViewHelper::saveParams($vid,$builder->criteria);

			// [JAS]: View: My Tickets
			$vid = $dashboards->createView($dashid,$uid,'My Tickets',0);
			$builder = new CerSearchBuilder();
			$builder->add('status',array('status'=>1)); // active
			$builder->add('flag',array('flag_mode'=>1, 'flags'=>array($uid=>$agent_name))); // flagged by me
			CerTicketViewHelper::saveParams($vid,$builder->criteria);
			
			// [Philipp Kolmann]: Save Page Layout
			$session->vars["login_handler"]->user_prefs->page_layouts["default_dashboard_id"] = $dashid;			
			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			break;	
		
		case "create_dashboard":
			$uid = $session->vars["login_handler"]->user_id;
			@$newDashboardName = $_REQUEST['newDashboardName'];
			$dashid = $dashboards->create($newDashboardName,$uid);
			break;
			
		case "edit_dashboard":
			$uid = $session->vars["login_handler"]->user_id;
			@$dashboardName = stripslashes($_REQUEST['dashboard_name']);
			@$dashboardHideTeams = $_REQUEST['dashboard_hide_teams'];
			@$dashboardTeams = $_REQUEST['dashboard_teams'];
			@$dashboardHideQueues = $_REQUEST['dashboard_hide_queues'];
			@$dashboardQueues = $_REQUEST['dashboard_queues'];
			@$dashboardReload = $_REQUEST['dashboard_reload'];
			
			if(is_array($dashboardHideTeams) && is_array($dashboardTeams)) {
				foreach($dashboardHideTeams as $idx => $dtid) {
					if(in_array($dtid,$dashboardTeams))
						unset($dashboardHideTeams[$idx]);
				}
			}
			if(is_array($dashboardHideQueues) && is_array($dashboardQueues)) {
				foreach($dashboardHideQueues as $idx => $dqid) {
					if(in_array($dqid,$dashboardQueues))
						unset($dashboardHideQueues[$idx]);
				}
			}
			
			$dashboards->save($dashid,$uid,$dashboardName,$dashboardHideTeams,$dashboardHideQueues,$dashboardReload);
			break;
			
		case "delete_dashboard":
			$uid = $session->vars["login_handler"]->user_id;
			$dashboards->delete($dashid,$uid);
			$dashid = 0;
			break;
			
		default:
			include (FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView_modify.include.php");		
			break;
	}
}

// [JAS]: We're giving the user a popup, remove the 'new' flag from messages so it doesn't keep popping up
if($session->vars["login_handler"]->has_new_pm)
	$cer_tpl->assign('new_pm',$session->vars["login_handler"]->has_new_pm);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

////
include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

$qh = cer_QueueHandler::getInstance();
$queues = $qh->getQueues();
$cer_tpl->assign_by_ref('queues',$queues);

$cerTeams = CerWorkstationTeams::getInstance(); /* @var $cerTeams CerWorkstationTeams */
$teams = $cerTeams->getTeams();
$cer_tpl->assign_by_ref("teams",$teams);
$cer_tpl->assign_by_ref("cerTeams",$cerTeams);

$cerTags = new CerWorkstationTags(); /* @var $cerTags CerWorkstationTags */
$cer_tpl->assign_by_ref("tags",$cerTags);

$cerAgents = new CerAgents();
$agents = $cerAgents->getList("RealName");
$cer_tpl->assign_by_ref("agents",$agents);
////

// [JAS]: Who's Online Functionality
$cer_who = new CER_WHOS_ONLINE();
$cer_tpl->assign_by_ref('cer_who',$cer_who);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign_by_ref('acl',$acl);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);
$cer_tpl->assign_by_ref('cerberus_disp',$cerberus_disp);

include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
$counts = CerWorkstationTickets::getAgentCounts($session->vars['login_handler']->user_id);
$cer_tpl->assign("header_flagged",$counts['flagged']);
$cer_tpl->assign("header_suggested",$counts['suggested']);

$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php"),
			  'save_layout' => "javascript:savePageLayout();",
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib")
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "index.php";
$cer_tpl->assign("page",$page);

//=============================================================================
// [JAS]: DASHBOARD VIEW CONTROLS
//=============================================================================
@$view_submit = $_REQUEST['view_submit'];

if(!empty($view_submit))
{
	@$view_submit_mode = $_REQUEST['view_submit_mode'];
	@$vid = $_REQUEST["vid"];

	switch($view_submit_mode) { 
		case 1: // delete
			$uid = $session->vars["login_handler"]->user_id;
			$dashboards->deleteView($vid,$uid);
			break;

		case 2: // params
			@$label = $_REQUEST['label'];
			if(substr($label,0,4) != "view")
				break;
				
			$vid = substr($label,4);
			
			if(isset($session->vars[$label . '_builder'])) {
				$builder = $session->vars[$label . '_builder']; /* @var $builder CerSearchBuilder */
				CerTicketViewHelper::saveParams($vid,$builder->criteria);
			}
			break;
			
		case 0: // save
			@$filter_rows =  $_REQUEST[$view_submit."_filter_rows"];
			if(empty($filter_rows)) $filter_rows = 10;
			$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_filter_rows"] = $filter_rows;
			
			if($vid) {
				@$filter_cols = $_REQUEST[$view_submit . "_columns"];
				@$filter_order =  $_REQUEST[$view_submit."_order"];
				@$view_name = $_REQUEST[$view_submit . "_name"];
				
				CerTicketViewHelper::saveSchema($vid, $view_name, $filter_cols, $filter_order);
			}
		
			// [JAS]: Reset paging
			$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_p"] = 0;
			break;
	}
}

$dashboardList = $dashboards->getList($session->vars["login_handler"]->user_id);
$cer_tpl->assign_by_ref('dashboards',$dashboardList);

$savedSearches = $search->getList($session->vars["login_handler"]->user_id);
$cer_tpl->assign_by_ref('savedSearches',$savedSearches);

$selDashboard =& $dashboardList[$dashid]; /* @var $selDashboard CerDashboard */
$cer_tpl->assign_by_ref('selDashboard',$selDashboard);

$dashboardViews = array();
if(!empty($selDashboard) && is_array($selDashboard->views)) {
	foreach($selDashboard->views as $dvi=>$dvv) {
		$view_name = "view".$dvi;
		$dashboardViews[$dvi] = new cer_TicketDashboardView($dvi,$view_name);
		
		// [JAS]: Load dashboard view params for dynamic editing.
		$session->vars[$view_name . '_customize_builder'] = new CerSearchBuilder();
		$session->vars[$view_name . '_customize_builder']->criteria = $dvv->params['criteria'];
	}
	$cer_tpl->assign('dashboardViews',$dashboardViews);
}


// [JAS]: Dashboards ============
		// ACL
		$dash_queues = $queues;
		
		if(is_array($dash_queues))
		foreach($dash_queues as $idx => $q) {
			if(!isset($acl->queues[$idx]) || isset($selDashboard->hide_queues[$idx])) {
				unset($dash_queues[$idx]);
			}
		}

		$qids = array_keys($acl->queues);
		$dash_teams = $cerTeams->getTeamsWithRelativeLoads($user_id,$qids);

		// ACL
		if(is_array($dash_teams))
		foreach($dash_teams as $idx => $t) {
			if(!isset($acl->teams[$idx]) || isset($selDashboard->hide_teams[$idx])) {
				unset($dash_teams[$idx]);
			}
		}
		
		$cer_tpl->assign('total_team_hits',$cerTeams->total_hits);
		$cer_tpl->assign_by_ref("dashboard_teams",$dash_teams);

		$cer_tpl->assign("total_queue_hits",$qh->total_active_tickets);
		$cer_tpl->assign_by_ref("dashboard_queues",$dash_queues);

//==========

switch($errorcode) {
	case "NOACCESS":
		$errorcode = "You do not have access to the requested ticket: " . $errorvalue;
		break;
	default:
		break;
}
$cer_tpl->assign_by_ref('errorcode',$errorcode);

$time_now = new cer_DateTime(date("Y-m-d H:i:s"));
$cer_tpl->assign("time_now",$time_now->getUserDate());

/**** Scheduled Tasks **/
require_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCron.class.php");
$cron = new CerCron();
$lockHold = $cron->getLockTime() + (60*5); // 5 min from lock
if((intval($session->vars["cron_cooldown"]) < mktime())
	&& $cron->getPollMode() == CER_CRON_MODE_INTERNAL 
	&& $lockHold < mktime()
	&& $cron->isValidIp($_SERVER['REMOTE_ADDR'])
	) {
		// [JAS]: Don't ask the same user for another minute.
		$session->vars["cron_cooldown"] = mktime() + 60;
		$cer_tpl->assign("run_cron",true);
}
/************************/

$cer_tpl->display('home.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************