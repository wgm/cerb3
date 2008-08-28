<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: ticket_list.php
|
| Purpose: List the tickets in a queue or in a search.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted		(ben@webgroupmedia.com)		[BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");
require_once(FILESYSTEM_PATH . "cerberus-api/i18n/languages.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/search/ticket_search.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/notification/CerNotification.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTicketTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_translate = new cer_translate;
$cerberus_disp = new cer_display_obj;

$queueHandler = cer_QueueHandler::getInstance();
$queues = $queueHandler->getQueues();

$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
$statuses = $cerStatuses->getList();

$customfields = new cer_CustomFieldGroupHandler();
$customfields->loadGroupTemplates();

$cerTeams = CerWorkstationTeams::getInstance();
$teams = $cerTeams->getTeams();

$cer_tpl->assign("queues", $queues);
$cer_tpl->assign("statuses", $statuses);

@$search_submit = $_REQUEST["search_submit"];
@$form_submit = $_REQUEST["form_submit"];

@$status_id = $_REQUEST["status_id"];
@$action_id = $_REQUEST["action_id"];

@$override = $_REQUEST['override'];
@$view_submit = $_REQUEST["view_submit"];
@$assign_type = $_REQUEST["assign_type"];

// [JAS]: Saved searches
$search = new CerSearch();
@$filter_rows =  intval($_REQUEST['search_limit']);

if(empty($filter_rows)) {
	if(empty($session->vars["login_handler"]->user_prefs->view_prefs->vars['sv_filter_rows'])) {
		$filter_rows = 50;
	} else {
		$filter_rows = $session->vars["login_handler"]->user_prefs->view_prefs->vars['sv_filter_rows'];
	}
}
$session->vars["login_handler"]->user_prefs->view_prefs->vars['sv_filter_rows'] = $filter_rows;

$checkboxes_exist=0; // determines if we'll draw drop-down controls at the bottom of the page

$cerberus_format = new cer_formatting_obj;

$cer_tpl->assign_by_ref('customfields',$customfields);

$cer_tpl->assign('search_id',((isset($search_id))?$search_id:0));

$extra_where = "";
$extra_from_thread="";
$extra_select="";
$where_queue="";

$in_tickets = "";

//=============================================================================
// [JAS]: FORM ACTIONS
//=============================================================================
if(!empty($form_submit))
{
	include (FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView_modify.include.php");		

	switch($form_submit)
	{
		case "save_layout":
			$layout_view_options_sv = (isset($_REQUEST["layout_view_options_sv"])) ? $_REQUEST["layout_view_options_sv"] : 0;

			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_sv"] = $layout_view_options_sv;			
			
			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			
			$errorcode = "Page layout saved!";
			
			break;
			
	} // end switch
}

//=============================================================================
// [JAS]: END FORM ACTIONS
//=============================================================================


//=============================================================================
// [JAS]: SEARCH QUERY
//=============================================================================

if(!empty($override)) {
	$override_type = substr($override,0,1);
	$override_value = substr($override,1);
	
	$session->vars['search_builder']->reset();
	
	switch($override_type) {
		case "q": // queue
			$params['queues'] = array($override_value=>@$queues[$override_value]->queue_name);
			$session->vars['search_builder']->add('queue',$params);
			$session->vars['search_builder']->add('status',array('status'=>1)); // active
			break;
		case "t": // team
			$tq = $teams[$override_value]->queues;
			$queue_list = array();
			if(!empty($tq) && is_array($tq))
			foreach($tq as $tqi => $tqv) {
				$queue_list[$tqi] = @$queues[$tqi]->queue_name;
			}
			$params['queues'] = $queue_list;
			$session->vars['search_builder']->add('queue',$params);
			$session->vars['search_builder']->add('status',array('status'=>1)); // active
			break;
		case "r": // requester
			$params['requester'] = $override_value;
			$session->vars['search_builder']->add('requester',$params);
			$session->vars['search_builder']->add('status',array('status'=>0)); // any
			break;
		case "i": // id/mask
			$params['mask'] = $override_value;
			$session->vars['search_builder']->add('mask',$params);
			$session->vars['search_builder']->add('status',array('status'=>0)); // any
			break;
		case "s": // subject
			$params['subject'] = $override_value;
			$session->vars['search_builder']->add('subject',$params);
			$session->vars['search_builder']->add('status',array('status'=>0)); // any
			break;
		case "c": // content
			$params['content'] = $override_value;
			$session->vars['search_builder']->add('content',$params);
			$session->vars['search_builder']->add('status',array('status'=>0)); // any
			break;
		case "v": // view
			// [JAS]: [TODO] Someday this could filter views by owning agents
			$params = CerTicketViewHelper::getSchema($override_value);
			$session->vars['search_builder']->criteria = $params['criteria'];
			break;
		case "h": // header my/suggested
			if(empty($override_value)) { // flagged
				$params['flag_mode'] = 1;
				$params['flags'] = array($session->vars['login_handler']->user_id => $session->vars['login_handler']->user_name);
				$session->vars['search_builder']->add('flag',$params);
				$session->vars['search_builder']->add('status',array('status'=>1)); // any active
			} else { // suggested
				$params['agents'] = array($session->vars['login_handler']->user_id=>$session->vars['login_handler']->user_name);
				$flag_params = array();
				$flag_params['flag_mode'] = 0;
				$session->vars['search_builder']->add('flag',$flag_params);
				$session->vars['search_builder']->add('workflow',$params);
				$session->vars['search_builder']->add('status',array('status'=>1)); // any active
			}
			break;
	}
	
	$session->vars['override_type'] = $override_type;
}

$params['criteria'] = $session->vars['search_builder']->criteria;

if(!empty($search_submit)) {
		$session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_p"] = 0; // reset paging
}
//	print_r($params['criteria']);
	
	/*
	$params = array();
	$session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_p"] = 0;
	
	if(empty($searchTags) && !empty($tid)) {
		$searchTags = array($tid);
	}
	
	if(!empty($searchTags) && is_array($searchTags)) {
		$copyTags = array();
		foreach($searchTags as $search_tag) {
			$copyTags[$search_tag] = $search_tag;
		}
		$searchTags = $copyTags;
	}
	
	$acl = CerACL::getInstance();
	
	if($acl->has_priv(PRIV_VIEW_UNASSIGNED) && !empty($search_assigned) && $search_assigned==2) { // if looking for no teams set
	} else {
		if(!empty($searchTeams) && is_array($searchTeams)) {
			$copyTeams = array();
			foreach($searchTeams as $search_team) {
				if(isset($acl->teams[$search_team])) // permissions check
					$copyTeams[$search_team] = $search_team;
			}
			$searchTeams = $copyTeams;
		} else { // limit to assigned teams, unless the unassigned bit exists
			$searchTeams = $acl->teams;
		}
	}
	
	if(!empty($searchAgents) && is_array($searchAgents)) {
		$copyAgents = array();
		foreach($searchAgents as $search_agent) {
			$copyAgents[$search_agent] = $search_agent;
		}
		$searchAgents = $copyAgents;
	}
	
	if(!empty($search_flags) && !empty($searchFlags) && is_array($searchFlags)) {
		$copyFlagged = array();
		foreach($searchFlags as $search_flag) {
			$copyFlagged[$search_flag] = $search_flag;
		}
		$searchFlags = $copyFlagged;
	} else {
		$searchFlags = array();
	}
	
	if(isset($searchTags)) $params["search_tags"] = $searchTags;
	if(isset($searchAgents)) $params["search_agents"] = $searchAgents;
	if(isset($searchTeams)) $params["search_teams"] = $searchTeams;
	if(isset($searchFlags)) $params["search_flags"] = $searchFlags;

	// [JAS]: Quick Search Options
	if(isset($search_advanced)) $params["search_advanced"] = $search_advanced;
	if(isset($search_status)) $params["search_status"] = $search_status;
	if(isset($search_sender)) $params["search_sender"] = $search_sender;
	if(isset($search_subject)) $params["search_subject"] = $search_subject;
	if(isset($search_content)) $params["search_content"] = $search_content;
	if(isset($search_company)) $params["search_company"] = $search_company;
	if(isset($search_queue)) $params["search_queue"] = $search_queue;
	if(isset($search_workflow)) $params["search_workflow"] = $search_workflow;
	if(isset($search_flagged)) $params["search_flagged"] = $search_flagged;
	if(isset($search_assigned)) $params["search_assigned"] = $search_assigned;
	if(isset($queue_view)) $params["queue_view"] = $queue_view;
	
	// [JAS]: Advanced Search Options
	if($search_advanced) {
		if(isset($search_date)) $params["search_date"] = $search_date;
		if(isset($search_fdate)) $params["search_fdate"] = $search_fdate;
		if(isset($search_tdate)) $params["search_tdate"] = $search_tdate;
	}
	
	if($search_advanced && !empty($search_field_ids)) {
		$params["search_field_ids"] = $search_field_ids;
		$ids = explode(',',$search_field_ids);
		
		// [JAS]: Populate filled in search fields in the persistent query.
		foreach($ids as $id) {
			$params["search_field_" . $id] = $_REQUEST["search_field_" . $id];
		}
	}
	
	unset($session->vars["psearch"]);
	
	if($search_id) { // existing
		if($search_mode==0) { // new
			$search_id = 0;
			$params['search_id'] = $search_id;
		} elseif ($search_mode==1) { // save
			$search->saveSearch($params,$search_id);
		} elseif ($search_mode==2) { // delete
			$search->deleteSearch($search_id);
			$search_id = 0;
			$params = array();
		}
	} else { // new
		if($search_mode==0) { // don't save
		} elseif($search_mode==1) {
			@$title = $_REQUEST['search_title'];
			if(empty($title)) $title = "New Search";
			$search_id = $search->createSearch($params,$title,$session->vars["login_handler"]->user_id);
		}
	}
	*/
	
//	$params['search_id'] = $search_id;
//}

// [JAS]: Check if we're modifying a search
//if(isset($params)) {
//	if(isset($session->vars["psearch"]) && method_exists($session->vars["psearch"],"updateParams") && !empty($params)) {
//		$session->vars["psearch"]->updateParams($params); // update
//	}
//	else { // [JAS]: Otherwise we're making a new search or queue list.
//		$session->vars["psearch"] = new cer_TicketPersistentSearch();
//		$session->vars["psearch"]->updateParams($params); // populate
//	}
//}

//$savedSearches = $search->getList($session->vars['login_handler']->user_id);
//$cer_tpl->assign_by_ref('saved_searches',$savedSearches);

//=============================================================================
// [JAS]: SEARCH VIEW CONTROL
//=============================================================================
//@$sv = $_REQUEST["sv"]; // assigned view
@$sv = 0;
@$sv_sort_by = $_REQUEST["sv_sort_by"];
@$sv_asc = $_REQUEST["sv_asc"];
@$sv_p = $_REQUEST["sv_p"];
//if(isset($sv)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv"] = $sv; $sv_p = 0; }
if(isset($sv_sort_by)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_sort_by"] = $sv_sort_by; }
if(isset($sv_asc)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_asc"] = $sv_asc; }
if(isset($sv_p)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_p"] = $sv_p; }
//$s_view = new cer_TicketViewSearch($session->vars["login_handler"]->user_prefs->view_prefs->vars["sv"],$session->vars["psearch"]->params);
$s_view = new cer_TicketViewSearch($session->vars["login_handler"]->user_prefs->view_prefs->vars["sv"],$params);
$cer_tpl->assign_by_ref('s_view',$s_view);

// [JAS]: Search Box Functionality
$search_box = new CER_TICKET_SEARCH_BOX();
$cer_tpl->assign_by_ref('search_box',$search_box);

// [JAS]: We're giving the user a popup, remove the 'new' flag from messages so it doesn't keep popping up
if($session->vars["login_handler"]->has_new_pm)
{
	$cer_tpl->assign('new_pm',$session->vars["login_handler"]->has_new_pm);
}

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$acl = CerACL::getInstance();
$cer_tpl->assign_by_ref('acl',$acl);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);
$cer_tpl->assign_by_ref('cerberus_disp',$cerberus_disp);

$tags = new CerWorkstationTicketTags();
$cerAgents = CerAgents::getInstance(); /* @var $cerAgents CerAgents */
$agents = $cerAgents->getList("RealName");

$cer_tpl->assign_by_ref("tags",$tags);
$cer_tpl->assign_by_ref("agents",$agents);
$cer_tpl->assign_by_ref("teams",$teams);

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
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php"),
			  'save_layout' => "javascript:savePageLayout();"
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "ticket_list.php";
$cer_tpl->assign("page",$page);

$cer_tpl->assign_by_ref('errorcode',$errorcode);

$cer_tpl->display("ticket_list.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
