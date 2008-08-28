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
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");
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

log_user_who_action(WHO_IN_TEAMWORK);

// [JAS]: Create the Cerberus objects we'll need on the page and includes
$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_format = new cer_formatting_obj;
$cerberus_translate = new cer_translate;
$cerberus_disp = new cer_display_obj;
$cerberus_db = cer_Database::getInstance();
$acl = CerACL::getInstance();

@$mode = $_REQUEST["mode"];

if(!empty($mode)) {
	$session->vars['work_mode'] = $mode;
} elseif (!empty($session->vars['work_mode'])) {
	$mode = $session->vars['work_mode'];
} else {
	$mode = "quick_assign";
	$session->vars['work_mode'] = $mode;
}

$cer_tpl->assign("mode",$mode);

// [JAS]: Clear the monitor if we're loading the mode
if($mode == "monitor") {
	unset($session->vars['monitor_epoch']);
}

// [JAS]: Setup up the local variables from the scope objects
@$view_submit = $_REQUEST["view_submit"];

@$form_submit = $_REQUEST["form_submit"];
//@$bids = $_REQUEST["bids"];

$errorcode = isset($_REQUEST["errorcode"]) ? strip_tags($_REQUEST["errorcode"]) : "";
$errorvalue = isset($_REQUEST["errorvalue"]) ? strip_tags($_REQUEST["errorvalue"]) : "";

//if(!empty($form_submit))
//{
//	switch($form_submit) {
//		case "save_layout":
//			$layout_home_show_queues = (isset($_REQUEST["layout_home_show_queues"])) ? $_REQUEST["layout_home_show_queues"] : 0;
//			$layout_home_show_search = (isset($_REQUEST["layout_home_show_search"])) ? $_REQUEST["layout_home_show_search"] : 0;
//		
//			$session->vars["login_handler"]->user_prefs->page_layouts["layout_home_show_queues"] = $layout_home_show_queues;			
//			$session->vars["login_handler"]->user_prefs->page_layouts["layout_home_show_search"] = $layout_home_show_search;			
//			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_av"] = $layout_view_options_av;
//			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_uv"] = $layout_view_options_uv;			
//			
//			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
//					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
//					$session->vars["login_handler"]->user_id
//				);
//			$cerberus_db->query($sql);
//			
//			$errorcode = "Page layout saved!";
//			
//			break;
//			
//		default:
////			include (FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView_modify.include.php");		
//			break;
//	}
//}

// [JAS]: We're giving the user a popup, remove the 'new' flag from messages so it doesn't keep popping up
if($session->vars["login_handler"]->has_new_pm)
	$cer_tpl->assign('new_pm',$session->vars["login_handler"]->has_new_pm);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$cerTeams = CerWorkstationTeams::getInstance(); /* @var $cerTeams CerWorkstationTeams */
$teams = $cerTeams->getTeams();
$cer_tpl->assign_by_ref("teams",$teams);

// [JAS]: Search Box Functionality
//$search_box = new CER_TICKET_SEARCH_BOX();
//$cer_tpl->assign_by_ref('search_box',$search_box);

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

$page = "getwork.php";
$cer_tpl->assign("page",$page);

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

$cer_tpl->display('getwork.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************