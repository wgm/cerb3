<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/structs.php");

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketViewProcs.func.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

class cer_TicketView {
	var $db = null;

	var $view_id = 0; 											// view id from db
	var $view_name = null; 										// view name
	var $view_slot = null; 										// the variable to track the view prefs with (sorting/asc,desc/page/etc)

	var $view_title_style = null;

	var $view_exclusive = false;								// Require in_tids()

	var $view_adv_2line=1;										// Show two line ticket listings, subject on its own.  1=yes 0=np
	var $view_adv_controls=1;									// Show ticket batch controls + checkboxes.  1=yes 0=np
	var $view_adv_exclusions=0;									// How many columns we're forcing (checkbox + subject, etc)

	var $view_colspan = 0; 										// stores colspan for display
	var $view_colspan_subject = 0; 								// stores colspan for first line display

	var $view_options = array(); 								// array of all views available for drop-down

	var $view_page = 0;
	var $view_next_url = null;
	var $view_prev_url = null;

	var $view_bind_page = null;									// page name: index.php, display.php, ticket_list.php, ...
	var $show_options = 0;

	var $filter_rows = 0;
	var $view_order = 0;
//	var $filter_responded = 0;

//	var $teams = null; 										// team string, comma-delimited; or '*' for all

	var $column_string = null; 									// raw column string, comma-delimited
	var $columns = array(); 									// view columns
	var $rows = array(); 										// view rows

	var $params = array();

	var $page_name = null;
	var $page_args = "x=";

	var $show_next = false;
	var $show_prev = false;
	var $show_from = 0;
	var $show_to = 0;
	var $show_of = 0;
	var $show_new_view = false;
	var $show_edit_view = false;

	var $show_modify = null;
	var $show_mass = null;
	var $show_batch_actions = null;
	var $show_chowner = null;
	var $show_chowner_options = null;
	var $show_chstatus = null;
	var $show_chaction = null;

	var $field_handler = null;

	var $heap_name = null;
	
	var $tables = array();

	function cer_TicketView($vid="",$vslot="",$params=array()) {
		global $_SERVER; //* \todo clean
		global $session;

		$this->db = cer_Database::getInstance();
		$this->heap_name = "temp_" . $session->vars["login_handler"]->user_id;
		
		$this->view_slot = $vslot;

		$this->params = $params;

		$this->field_handler = new cer_CustomFieldGroupHandler();
		$this->field_handler->loadGroupTemplates();

		$this->page_name = $_SERVER['PHP_SELF'];

		// [JAS]: Load information about the tables the view can use columns from.
		if(empty($vid)) {
			$this->_loadTables();
		}

		$this->setPrefs();
		$this->_loadViewOptions();
		$this->_loadViewDetails($vid);
		$this->_computeViewColSpan();
		$this->_populateView();
		$this->_determinePageURLs();
	}

	function setPrefs() {
		// [JAS]: Expect Override
	}

	function setViewDefaults() {
		// [JAS]: Expect Override
	}

	function _loadTables() {
		global $session;
		$db = cer_Database::getInstance();
		$acl = CerACL::getInstance();
		
		$pref_vars = &$session->vars["login_handler"]->user_prefs->view_prefs->vars;
		$user_id = $session->vars["login_handler"]->user_id;
		$view_slot = $this->view_slot;
		$sort_by = $pref_vars[$view_slot."_sort_by"];

		// [JAS]: Do we need to LEFT JOIN some tables? (ugh)
		$use_company = false; // phew
		if($sort_by == "company_name" || isset($this->params['criteria']['company'])) {
			$use_company = true; // ack
		}
		$use_ticket_status = false; // phew
		if($sort_by == "ticket_new_status" || isset($this->params['criteria']['ticket_status'])) {
			$use_ticket_status = true; // ack
		}
		
		// [JAS]: [TODO] Move this tags/teams/agents stuff into the search system (second query)
		
		// ==========================================
		// TEAMS
		// ==========================================
//		$team_sql = false; // phew
//		if(isset($this->params['criteria']['workflow']) && count($this->params['criteria']['workflow']['teams'])) {
//			$team_ary = array();
//			
//			// [JAS]: [TODO] Clean the teams array using ACL, exclude superuser?
////			if(is_array($this->params['criteria']['workflow']['teams']))
////			foreach($this->params['criteria']['workflow']['teams'] as $teamId => $team) {
////				if(!isset($acl->teams[$teamId]))
////					unset($this->params['criteria']['workflow']['teams'][$teamId]);
////			}
//			
//			if(!empty($this->params['criteria']['workflow']['teams_match']) && $this->params['criteria']['workflow']['teams_match'] == 1) { // MATCH ALL
//				if(is_array($this->params['criteria']['workflow']['teams']))
//					foreach($this->params['criteria']['workflow']['teams'] as $teamId => $team) {
//						$team_ary[] = sprintf("INNER JOIN workstation_routing_to_tickets rt%d ON (rt%d.ticket_id = t.ticket_id AND rt%d.team_id=%d) ",
//							$teamId,
//							$teamId,
//							$teamId,
//							$teamId
//						);
//					}
//					$team_sql = implode('',$team_ary);
//			} elseif(!empty($this->params['criteria']['workflow']['teams_match']) && $this->params['criteria']['workflow']['teams_match'] == 2) { // MATCH NONE
//				$db->query(sprintf("DROP TABLE IF EXISTS `not_teams%d`",$user_id));
//				
//				$db->query(sprintf("CREATE TABLE `not_teams%d` (`ticket_id` BIGINT(20) unsigned NOT NULL, PRIMARY KEY (ticket_id)) TYPE=HEAP ",$user_id));
//				
//				$team_list = implode(',', array_keys($this->params['criteria']['workflow']['teams']));
//				if(empty($team_list)) $team_list = "-1";
//
//				$union_sql = sprintf("INSERT INTO `not_teams%d` SELECT rt.ticket_id FROM workstation_routing_to_tickets rt WHERE rt.team_id IN (%s) GROUP BY rt.ticket_id",
//					$user_id,
//					$team_list
//				);
//				$db->query($union_sql);
//				
//				$team_sql = sprintf("LEFT JOIN `not_teams%d` ngu ON (t.ticket_id = ngu.ticket_id) ",
//					$user_id
//				);
//				$team_where_sql = sprintf("AND ngu.ticket_id IS NULL ",$user_id);
//				
//			} else { // MATCH ANY
//				$team_list = implode(',', array_keys($this->params['criteria']['workflow']['teams']));
//				if(empty($team_list)) $team_list = "-1";
//				$team_sql = sprintf("INNER JOIN workstation_routing_to_tickets rt ON (rt.ticket_id = t.ticket_id AND rt.team_id IN (%s)) ",
//					$team_list
//				);
//			}
//		} else {
//			$team_sql = false;
//		}
		
		// ==========================================
		// TAGS
		// ==========================================
		$tag_sql = false; // phew
		if(isset($this->params['criteria']['tags']) && count($this->params['criteria']['tags']['tags'])) {
//			$use_tags = true; // ack
			$tag_ary = array();
			
			if(!empty($this->params['criteria']['tags']['tags_match']) && $this->params['criteria']['tags']['tags_match']==1) { // MATCH ALL
				if(is_array($this->params['criteria']['tags']['tags']))
				foreach($this->params['criteria']['tags']['tags'] as $tagId => $tag)	{
					$tag_ary[] = sprintf("INNER JOIN workstation_tags_to_tickets tt%d ON (tt%d.ticket_id = t.ticket_id AND tt%d.tag_id=%d) ",
						$tagId,
						$tagId,
						$tagId,
						$tagId
					);
				}
				$tag_sql = implode('',$tag_ary);
			} elseif(!empty($this->params['criteria']['tags']['tags_match']) && $this->params['criteria']['tags']['tags_match']==2) { // MATCH NONE
				$db->query(sprintf("DROP TABLE IF EXISTS `not_tags%d`",$user_id));
				
				$db->query(sprintf("CREATE TABLE `not_tags%d` (`ticket_id` BIGINT(20) unsigned NOT NULL, PRIMARY KEY (ticket_id)) TYPE=HEAP ",$user_id));
				
				$union_sql = sprintf("INSERT INTO `not_tags%d` SELECT tt.ticket_id FROM workstation_tags_to_tickets tt WHERE tt.tag_id IN (%s) GROUP BY tt.ticket_id",
					$user_id,
					implode(',', array_keys($this->params['criteria']['tags']['tags']))
				);
				$db->query($union_sql);
				
				$tag_sql = sprintf("LEFT JOIN `not_tags%d` ntu ON (t.ticket_id = ntu.ticket_id) ",
					$user_id
				);
				$tag_where_sql = sprintf("AND ntu.ticket_id IS NULL ",$user_id);
			} else { // MATCH ANY
				$tag_sql = sprintf("INNER JOIN workstation_tags_to_tickets tt ON (tt.ticket_id = t.ticket_id AND tt.tag_id IN (%s)) ",
					implode(',', array_keys($this->params['criteria']['tags']['tags']))
				);
			}
		}
		
		
		// ==========================================
		// AGENTS
		// ==========================================
		$agent_sql = false; // phew
		if(isset($this->params['criteria']['workflow']) && count($this->params['criteria']['workflow']['agents'])) {
			$agent_ary = array();
			
			if(!empty($this->params['criteria']['workflow']['agents_match']) && $this->params['criteria']['workflow']['agents_match'] == 1) { // MATCH ALL
				if(is_array($this->params['criteria']['workflow']['agents']))
				foreach($this->params['criteria']['workflow']['agents'] as $agentId => $agent) {
					$agent_ary[] = sprintf("INNER JOIN ticket_spotlights_to_agents ta%d ON (ta%d.ticket_id = t.ticket_id AND ta%d.agent_id=%d) ",
						$agentId,
						$agentId,
						$agentId,
						$agentId
					);
				}
				$agent_sql = implode('',$agent_ary);
			} elseif(!empty($this->params['criteria']['workflow']['agents_match']) && $this->params['criteria']['workflow']['agents_match'] == 2) { // MATCH NONE
				$db->query(sprintf("DROP TABLE IF EXISTS `not_agents%d`",$user_id));
				
				$db->query(sprintf("CREATE TABLE `not_agents%d` (`ticket_id` BIGINT(20) unsigned NOT NULL, PRIMARY KEY (ticket_id)) TYPE=HEAP ",$user_id));

				$union_sql = sprintf("INSERT INTO `not_agents%d` SELECT ta.ticket_id FROM ticket_spotlights_to_agents ta WHERE ta.agent_id IN (%s) GROUP BY ta.ticket_id",
					$user_id,
					implode(',', array_keys($this->params['criteria']['workflow']['agents']))
				);
				$db->query($union_sql);
				
				$agent_sql = sprintf("LEFT JOIN `not_agents%d` nau ON (t.ticket_id = nau.ticket_id) ",
					$user_id
				);
				$agent_where_sql = sprintf("AND nau.ticket_id IS NULL ",$user_id);
			} else { // MATCH ANY
				$agent_sql = sprintf("INNER JOIN ticket_spotlights_to_agents ta ON (ta.ticket_id = t.ticket_id AND ta.agent_id IN (%s)) ",
					implode(',', array_keys($this->params['criteria']['workflow']['agents']))
				);
			}
		}
		
		// ==========================================
		// FLAGS
		// ==========================================
		$use_flags = false; // phew
		if(isset($this->params['criteria']['flag'])) {
			$use_flags = true; // ack
		}

		$use_queue = false;
		if($sort_by == "queue_name") {
			$use_queue = true;
		}
		
		$use_lastwrote = false;
		if($sort_by == "address_address") {
			$use_lastwrote = true;
		}

		$use_firstwrote = false;
		if($sort_by == "requestor_address") {
			$use_firstwrote = true;
		}
		
		$base_sql = "SELECT t.ticket_id ". // , a.address_id
		"FROM (ticket t %s %s) ".
		(($use_queue) ? "INNER JOIN queue q ON (t.ticket_queue_id = q.queue_id) " : " ").
		//(($use_firstwrote) ? "INNER JOIN thread thr ON (t.min_thread_id = thr.thread_id) " : "").
		//(($use_lastwrote) ? "INNER JOIN thread th ON (t.max_thread_id = th.thread_id) " : "").
		(($use_firstwrote || $use_company) ? "INNER JOIN address a ON (t.opened_by_address_id = a.address_id) " : "").
		(($use_lastwrote) ? "INNER JOIN address ad ON (t.last_wrote_address_id = ad.address_id) " : "").
		(($tag_sql) ? $tag_sql : "") .
//		(($team_sql) ? $team_sql : "") .
		(($use_flags) ? (($this->params['criteria']['flag']['flag_mode']) ? "INNER JOIN ticket_flags_to_agents fta ON (fta.ticket_id = t.ticket_id) ":" ") : " ") .
		(($agent_sql) ? $agent_sql : "") .
		"%s ".
		(($use_company) ? "LEFT JOIN public_gui_users pu ON (a.public_user_id = pu.public_user_id) " : "") .
		(($use_company) ? "LEFT JOIN company c ON (pu.company_id = c.id) " : "") .
		(($use_ticket_status) ? "LEFT JOIN ticket_status ts ON (t.ticket_status_id = ts.ticket_status_id) " : "") .
		"WHERE 1 ".
//		((!empty($team_where_sql)) ? $team_where_sql : "").
		((!empty($tag_where_sql)) ? $tag_where_sql : "").
		((!empty($agent_where_sql)) ? $agent_where_sql : "").
		((!empty($this->params['criteria']['requester'])) ? "AND r.ticket_id = t.ticket_id " : "").
		//((!empty($this->params["search_flagged"])) ? (($this->params['search_flagged']==2) ? "AND t.num_flags = 0" : " AND t.num_flags > 0 ") : "").
		"";

		$this->tables = array (
		"address_first" => new cer_TicketViewColumnTable("address","a",$base_sql),
		"address_last" => new cer_TicketViewColumnTable("address","ad",$base_sql),
		"company" => new cer_TicketViewColumnTable("company","c",$base_sql),
		"queue" => new cer_TicketViewColumnTable("queue","q",$base_sql),
//		"thread_first" => new cer_TicketViewColumnTable("thread","thr",$base_sql),
//		"thread_last" => new cer_TicketViewColumnTable("thread","th",$base_sql),
		"ticket" => new cer_TicketViewColumnTable("ticket","t",$base_sql),
		"tags" => new cer_TicketViewColumnTable("workstation_tags_to_tickets","tt",$base_sql),
//		"teams" => new cer_TicketViewColumnTable("workstation_routing_to_tickets","rt",$base_sql),
		"agents" => new cer_TicketViewColumnTable("ticket_spotlights_to_agents","ta",$base_sql),
		"ticket_status" => new cer_TicketViewColumnTable("ticket_status","ts",$base_sql)
		);
	}

	function _loadViewOptions() {
		global $cer_hash; //* \todo clean

		// [JAS]: Store our view options for displaying the view select box
		$this->view_options = array('' => 'Default');
		$views = $cer_hash->get_view_hash();
		foreach($views as $view)
		{
			$this->view_options[$view->view_id] = $view->view_name;
		}
	}

	function _loadViewDetails($vid) {
		$acl = CerACL::getInstance();

		// [JAS]: Determine what links we'll be showing under the view
		if($acl->has_priv(PRIV_VIEW_CHANGE)) {
			// [TODO] ... block
		}

		// [JAS]: If a view ID was selected, load it
		if(!empty($vid))
		{
			$sql = sprintf("SELECT v.view_id,v.view_name,v.view_columns,".
				"v.view_params,v.view_adv_2line,v.view_adv_controls,v.view_order ".
				"FROM ticket_views v ".
				"WHERE v.view_id = %d",
				$vid
			);
			$v_res = $this->db->query($sql);

			if($this->db->num_rows($v_res))
			{
				$v_row = $this->db->fetch_row($v_res);
				$this->view_id = $v_row['view_id'];
				$this->view_name = trim(stripslashes($v_row['view_name']));
				$this->view_order = intval($v_row['view_order']);
				$this->column_string = (string)$v_row['view_columns'];
				$this->view_adv_2line = $v_row['view_adv_2line'];
				$this->view_adv_controls = $v_row['view_adv_controls'];
				$params = stripslashes($v_row['view_params']);
				
				// [JAS]: If we have overriding model info.
				if(!empty($params)) {
					$this->params = unserialize($params);
					$this->_loadTables();
				}
				
				$cols = explode(",",$v_row['view_columns']);

				array_push($this->columns,new cer_TicketViewColumn("checkbox",$this,"bids[]")); // [JAS]: Force checkbox
				array_push($this->columns,new cer_TicketViewColumn("ticket_subject",$this)); // [JAS]: Force subject

				// [JAS]: Are we showing two line subjects
				if($this->view_adv_2line)
				$this->view_adv_exclusions++;

				// [JAS]: Are we showing view advanced controls
				if($this->view_adv_controls)
				$this->view_adv_exclusions++;

				if(!empty($cols))
				foreach($cols as $c) {
					if($this->view_adv_2line && $c == "ticket_subject")
					continue;

					array_push($this->columns,new cer_TicketViewColumn($c,$this));
				}

			}
			else // no row returned (bad cookie or deleted)
			{
				$this->setViewDefaults();
			}
		}
		else // [JAS]: default view values
		{
			$this->setViewDefaults();
		}
	}

	function _computeViewColSpan()
	{
		$col_span = count($this->columns);

		if(!$this->view_adv_controls)
		$col_span--;

		$this->view_colspan = $col_span - 1;

		$this->view_colspan_subject = $col_span - 1;

		if($this->view_adv_2line && $this->view_adv_controls)
		$this->view_colspan_subject--;
	}

	/* Allow Override */
	function _buildMaskList() {
		return null;
	}
	
	/* Allow Override */
	function _buildStatusList() {
		$show_status_sql = null;

		$show_status_sql = "t.is_closed = 0 AND t.is_waiting_on_customer = 0 AND t.is_deleted = 0";
		
		return $show_status_sql;
	}

	/* Expect Override */
	function _buildWaitingList() {
		return null;
	}
	
	/* Expect Override */
	function _buildSenderList() {
		return null;
	}

	/* Expect Override */
	function _buildCompanyList() {
		return null;
	}

	/* Expect Override */
	function _buildNewStatusList() {
		return null;
	}

	/* Expect Override */
	function _buildQueueList() {
		return null;
	}
	
	/* Expect Override */
	function _buildTagList() {
		return null;
	}

	/* Expect Override */
	function _buildPriorityList() {
		return null;
	}
	
	/* Expect Override */
	function _buildTeamList() {
		return null;
	}

	/* Expect Override */
	function _buildAgentList() {
		return null;
	}

	/* Expect Override */
	function _buildFlagList() {
		return null;
	}

	/* Expect Override */
	function _buildSearchWords() {
		return null;
	}

	/* Expect Override */
	function _buildContentSearchWords() {
		return null;
	}

	/* Expect Override */
	function _buildSubjectSearchWords() {
		return null;
	}

	/* Expect Override */
	function _buildCreatedDateSearch() {
		return null;
	}

	/* Expect Override */
	function _buildUpdatedDateSearch() {
		return null;
	}

	/* Expect Override */
	function _buildDueDateSearch() {
		return null;
	}

	/* Expect Override */
	function _buildCustomFieldSearch() {
		return null;
	}

	/* Expect Override */
	function _buildBatchList() {
		return null;
	}

//	function _buildCustomerRespondedSQL() {
//		if(!empty($this->filter_responded)) {
//			return " t.last_reply_by_agent = 0 ";
//		}
//		else {
//			return null;
//		}
//	}

	function _populateView() {
		global $session;

		///////////////// =============================================================
		//  INITIALIZE SCOPE  [JAS]
		///////////////// =============================================================

		$pref_vars = &$session->vars["login_handler"]->user_prefs->view_prefs->vars;
		$view_slot = $this->view_slot;
		$sort_by = $pref_vars[$view_slot."_sort_by"];

		// [JAS]: If the session is sorting on an invalid column, look for ticket due, age or ticket ID, otherwise
		//	sort on the second column by default (first could be checkbox)
		if(!$this->columnExists($sort_by)) {
			if(!$col_id = $this->columnExists("ticket_due"))
			if(!$col_id = $this->columnExists("ticket_last_date"))
			if(!$col_id = $this->columnExists("ticket_id"))
			if($col_id = $this->columns[1]->column_id) {}
			$sort_by = $this->columns[$col_id]->column_name;
		}
		$sort_asc = (($pref_vars[$view_slot."_asc"]==1)?"ASC":"DESC");

		$show_rows = $this->filter_rows;

		$p = @$pref_vars[$view_slot . "_p"];
		if(empty($p) || $p < 0) $p=0;

		// [JAS]: (LIMIT) Where to start in the resultset + how many rows to grab
		$row_from = ($p * $show_rows);
		$row_for = $show_rows;

		$qid_sql = $this->_buildQueueList();
		$show_mask_sql = $this->_buildMaskList();
		$show_status_sql = $this->_buildStatusList();
		$show_new_status_sql = $this->_buildNewStatusList();
		$waiting_sql = $this->_buildWaitingList();
		$sender_sql = $this->_buildSenderList();
		$tag_sql = $this->_buildTagList();
//		$team_sql = $this->_buildTeamList();
		$agent_sql = $this->_buildAgentList();
		$priority_sql = $this->_buildPriorityList();
		$flag_sql = $this->_buildFlagList();
		$created_date_range_sql = $this->_buildCreatedDateSearch();
		$updated_date_range_sql = $this->_buildUpdatedDateSearch();
		$due_date_range_sql = $this->_buildDueDateSearch();
		$company_sql = $this->_buildCompanyList();
		$subject_words_rows = $this->_buildSubjectSearchWords(); // run second for subj bit
		$content_words_rows = $this->_buildContentSearchWords();
		$batch_id_sql = $this->_buildBatchList();
//		$responded_sql = $this->_buildCustomerRespondedSQL();

		list($field_from_sql, $field_where_sql) = $this->_buildCustomFieldSearch();

		//          if(!count($this->in_tids) && $this->view_exclusive) array_push($this->in_tids,-1);

		///////////////// =============================================================
		//  INITIALIZE SORT VARIABLES  [JAS]
		///////////////// =============================================================

		$sort_id = $this->columnExists($sort_by);
		$sort["table_name"] = $this->columns[$sort_id]->table->table_name;
		$sort["table_prefix"] = $this->columns[$sort_id]->table->table_prefix;
		$sort["field_name"] = $this->columns[$sort_id]->table_field_name;
		$sort["sort_sql"] = $this->columns[$sort_id]->table->sort_sql;
		$sort["group_by"] = $this->columns[$sort_id]->table->group_by;

		///////////////// =============================================================
		//  PRE-SORT USING TICKET ID  [JAS]
		///////////////// =============================================================

		$t_ids = array();
//		$r_ids = array();
		$search_join = null;

		if(!empty($subject_words_rows)) {
			$search_join .= sprintf(", %s_s si_s ",
				$this->heap_name
			);
		}
		if(!empty($content_words_rows)) {
			$search_join .= sprintf(", %s_c si_c ",
				$this->heap_name	
			);
		}

		$sql = $sort["sort_sql"] .
		" %s ". // show mask
		" %s ". // show status
		" %s ". // show new status
		" %s ". // waiting
		" %s ". // in sender list
		" %s ". // company
		" %s ". // queue
		" %s ". // priority
		" %s ". // batch
//		" %s ". // tags
//		" %s ". // teams
//		" %s ". // assigned
//		" %s ". // agents
		" %s ". // flags
		" %s ". // created date range
		" %s ". // updated date range
		" %s ". // due date range
		" %s ". // subject search words
		" %s ". // content search words
		" %s ". // custom field where
		"GROUP BY t.ticket_id %s ". // group by
		"ORDER BY %s %s";

//		echo "<HR>" . $sql . "<BR>";
		
		// [JAS]: Fix needed for [#CERB-68] where MySQL 4.1 will not sort on `table_name`.`aliased_field`
		if(substr($sort["table_prefix"],0,2) == "v_") {
			$sort_sql = '`' . $sort["field_name"] . '`';
		} else {
			$sort_sql = '`' . $sort["table_prefix"] . '`.`' . $sort["field_name"] . '`';
		}
		
		$acl = CerACL::getInstance();
		
		// [JAS]: Team privs
//		$numteam_sql = "";
//		if(@isset($this->params['criteria']['has_teams']['show'])) { // yes or no
//			if($this->params['criteria']['has_teams']['show']==1) { // yes
//				$numteam_sql = " AND t.num_teams > 0 ";
//			} else { // no
//					$numteam_sql = " AND t.num_teams = 0 ";
//			}
//		}

		$sql = sprintf($sql,
			(($field_from_sql) ? ", " . $field_from_sql : ""),
			(($sender_sql) ? ", requestor r " : ""),
			((!empty($search_join)) ? $search_join : " "), // [JAS]: Are we adding the search_index table?

			((!empty($qid_sql)) ? " AND t.ticket_queue_id IN ($qid_sql)" : " "),
			((!empty($show_mask_sql)) ? "AND $show_mask_sql " : " "),
			((!empty($sender_sql)) ? " AND r.address_id IN (".$sender_sql.")" : " "),
			((!empty($company_sql)) ? " AND c.id IN ($company_sql)" : " "),
			((!empty($show_new_status_sql) && $show_new_status_sql != "-1") ? " AND ts.ticket_status_id IN ($show_new_status_sql)" : " "),
			((strlen($priority_sql)) ? " AND t.ticket_priority IN ($priority_sql)" : " "),
			((!empty($batch_id_sql)) ? "AND t.ticket_id IN ($batch_id_sql)" : ""),
//			((!empty($numteam_sql)) ? sprintf("%s ",$numteam_sql) : ""),
			((!empty($created_date_range_sql)) ? $created_date_range_sql : ""),
			((!empty($updated_date_range_sql)) ? $updated_date_range_sql : ""),
			((!empty($due_date_range_sql)) ? $due_date_range_sql : ""),
			((!empty($field_where_sql)) ? " AND " . $field_where_sql : ""),
			((!empty($subject_words_rows)) ? " AND  t.ticket_id = si_s.ticket_id " : ""),
			((!empty($content_words_rows)) ? " AND  t.ticket_id = si_c.ticket_id " : ""),
			((!empty($waiting_sql)) ? "AND $waiting_sql " : " "),
			((!empty($show_status_sql)) ? "AND $show_status_sql " : " "),
			((!empty($flag_sql)) ? $flag_sql : ""),
			((!empty($sort["group_by"])) ? sprintf(",`%s`", $sort["group_by"]) : " "),
			$sort_sql,
			$sort_asc
		);

		//echo "<HR>" . $sql . "<HR>";
	
		$count_result = $this->db->query($sql);

		$this->show_of = $this->db->num_rows($count_result);

		// [JAS]: If we're trying to go past the max number of pages, reset the view.
		if($row_from > $this->show_of) {
			$row_from = 0;
			$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;
			$view_prefs->vars[$this->view_slot."_p"] = 0;
		}

		$sql .= sprintf(" LIMIT %d,%d",
			$row_from,
			$row_for
		);

		$res = $this->db->query($sql);

		$this->show_to = $row_from+$row_for;
		$this->show_from = $row_from+1;
		if($this->show_of == 0) $this->show_from = 0;
		if($this->show_to > $this->show_of) $this->show_to = $this->show_of;

		//		  echo $sql . "<HR>";
		//		  echo "$sort_by, $sort_asc<br>";

		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$t_ids[$row["ticket_id"]] = 1;
//				$r_ids[$row["thread_address_id"]] = $row["thread_address_id"];
			}
		}
	

		///////////////// =============================================================
		//  HANDLE CUSTOM FIELDS [JAS]
		///////////////// =============================================================

		$c_fld_ids = array();
		$cust_select = array();
		$cust_join = array();

		if(!empty($this->columns))
		foreach($this->columns as $col) {
			if($col->column_type == "custom_field") {
				$cust_select[] = sprintf("v_%d.`field_value` as g_%d_custom_%d",
					$col->field_id,
					$col->group_id,
					$col->field_id
				);

				$cust_join[] = sprintf("LEFT JOIN field_group_values v_%d ON ".
				"(v_%d.entity_index = IF ( v_%d.`entity_code` =  'R', t.`opened_by_address_id`, t.`ticket_id` ) ".
				"AND v_%d.field_id = %d)",
					$col->field_id,
					$col->field_id,
					$col->field_id,
					$col->field_id,
					$col->field_id
				);
			}
		}

		$use_custom_fields = false;
		if(!empty($cust_select) && !empty($cust_join))
		$use_custom_fields = true;


		///////////////// =============================================================
		//  FILL OUT THE RESULTS USING THE COLUMNS OF THE VIEW  [JAS]
		///////////////// =============================================================

		$use_company = false;
		if($this->columnExists("company_name") || $use_custom_fields)
		$use_company = true;

		$use_new_status = false;
		if($this->columnExists("ticket_new_status"))
		$use_new_status = true;

		$ticket_ids = array_keys($t_ids);
		CerSecurityUtils::integerArray($ticket_ids);
		
		$sql = sprintf("SELECT t.ticket_id, t.ticket_subject, t.ticket_priority, t.ticket_spam_trained, t.last_reply_by_agent, t.ticket_status_id, ".
			"t.ticket_spam_probability, t.ticket_last_date, t.ticket_date, t.is_closed, t.is_deleted, t.is_waiting_on_customer, t.ticket_due, ".
			"t.last_wrote_address_id, t.min_thread_id, a.address_address, t.ticket_mask, t.ticket_time_worked as total_time_worked, ".
			"ad.address_address as requestor_address, ad.address_banned, t.num_flags, q.queue_id, q.queue_name ".
			"%s ". // use company?
			"%s ". // use new status?
			"%s ". // use owner?
			"%s ". // custom field column?
			"FROM ticket t ".
			"INNER JOIN queue q ON (q.queue_id = t.ticket_queue_id) ".
//			"INNER JOIN thread thr ON (t.min_thread_id = thr.thread_id) ".
//			"INNER JOIN thread th ON (t.max_thread_id = th.thread_id) ".
			"INNER JOIN address a ON (a.address_id = t.last_wrote_address_id) ".
			"INNER JOIN address ad ON (ad.address_id = t.opened_by_address_id) ".
			"%s ". // use company?
			"%s ". // use new status?
			"%s ". // custom field join?
			"WHERE 1 ".
			"AND t.ticket_id IN (%s) ".
			"GROUP BY t.ticket_id %s ".
			"ORDER BY %s %s",
				(($use_company) ? ", c.name as name" : ""),
				(($use_new_status) ? ", ts.ticket_status_text as ticket_status_text" : ""),
				(($use_owner) ? ", u.user_login as ticket_owner" : ""),
				(($use_custom_fields) ? ("," . implode(",",$cust_select)) : ""),
				(($use_company) ? "LEFT JOIN public_gui_users pu ON (ad.public_user_id = pu.public_user_id) LEFT JOIN company c ON (pu.company_id = c.id)" : ""),
				(($use_new_status) ? "LEFT JOIN ticket_status ts ON (ts.ticket_status_id = t.ticket_status_id)" : ""),
				(($use_custom_fields) ? (implode(" ",$cust_join)) : ""),
				implode(',', $ticket_ids),
				(($use_custom_fields) ? "" : ""),
				$sort_sql,
				$sort_asc
		);
		$result = $this->db->query($sql);
		if(!empty($search_join)) {
			$sql = sprintf("DROP TABLE IF EXISTS `%s_s`",
					$this->heap_name
				);
			$this->db->query($sql);
			$sql = sprintf("DROP TABLE IF EXISTS `%s_c`",
					$this->heap_name
				);
			$this->db->query($sql);
		}
		
//		echo $sql . "<HR>";

		if($this->db->num_rows($result))
		while($ticket_row = $this->db->fetch_row($result))
		{
			$proc_args = new cer_TicketViewsProc($this);
			$proc_args->ticket_id = @$ticket_row["ticket_id"];
			$proc_args->ticket_status_id = @$ticket_row["ticket_status_id"];
			$proc_args->ticket_status_text = @$ticket_row["ticket_status_text"];
			$proc_args->ticket_created = @$ticket_row["ticket_date"];
			$proc_args->ticket_updated = @$ticket_row["ticket_last_date"];
			$proc_args->ticket_due = @$ticket_row["ticket_due"];
			$proc_args->queue_id = @$ticket_row["queue_id"];
			$proc_args->queue_name = @$ticket_row["queue_name"];
			$proc_args->num_flags = @$ticket_row["num_flags"];
			$proc_args->ticket_priority = @$ticket_row["ticket_priority"];
			$proc_args->ticket_mask = @$ticket_row["ticket_mask"];
			$proc_args->is_closed = @$ticket_row["is_closed"];
			$proc_args->is_deleted = @$ticket_row["is_deleted"];
			$proc_args->is_waiting_on_customer = @$ticket_row["is_waiting_on_customer"];
			$proc_args->last_reply_by_agent = @$ticket_row["last_reply_by_agent"];
			$proc_args->address_address = @$ticket_row["address_address"];
			$proc_args->requestor_address = @$ticket_row["requestor_address"];
			$proc_args->requester_banned = @$ticket_row["address_banned"];
			$proc_args->ticket_spam_trained = @$ticket_row["ticket_spam_trained"];
			$proc_args->ticket_spam_probability = @$ticket_row["ticket_spam_probability"];
			$proc_args->min_thread_id = @$ticket_row["min_thread_id"];
			$proc_args->view_ptr = &$this;

			$row_data = array();

			if(!empty($this->columns))
			foreach($this->columns as $idx => $col)
			{
				$proc_args->col_ptr = &$this->columns[$idx];
				$col_name = $proc_args->col_ptr->table_field_name;
				$row_data[$idx] = $col->execute_proc(@$ticket_row[$col_name],$proc_args);
			}
			array_push($this->rows,$row_data);
		}

	}

	function columnExists($col_name)
	{
		if(!empty($this->columns))
		foreach($this->columns as $idx => $col)
		{
			if($col->column_name == $col_name)
			return $idx;
		}

		return false;
	}

	function _determinePageURLs() {
		global $session; // clean up

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->view_page = @$view_prefs->vars[$this->view_slot."_p"];
		if(empty($this->view_page)) $this->view_page = 0;

		$p = $this->view_page;

		if($p > 0) { // [JAS]: Can we show a previous page?
			$this->show_prev = true;
			$this->view_prev_url = cer_href(sprintf("%s?%s&%s_p=%d",
			$this->page_name,
			$this->page_args,
			$this->view_slot,
			($p-1)
			));
		}

		if($this->show_of > $this->show_to) {
			$this->show_next = true;
			$this->view_next_url = cer_href(sprintf("%s?%s&%s_p=%d",
			$this->page_name,
			$this->page_args,
			$this->view_slot,
			($p+1)
			));
		}
	}

	function enableSearchActions()
	{
		$acl = CerACL::getInstance();

		if($acl->has_priv(PRIV_TICKET_CHANGE,BITGROUP_1)) {
			$this->show_chstatus = true;
		}

		if($acl->has_priv(PRIV_TICKET_CHANGE,BITGROUP_1)) {
			$this->show_chaction = true;
		}
	}
	
	function getColumnOptions() {
		include_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
		
		static $columns = null;

		if(null == $columns) {
			$handler = new cer_CustomFieldGroupHandler();
			$handler->loadGroupTemplates();
	
			$columns = array();
			$columns[''] = '-none-';
			$columns['ticket_id'] = 'Ticket ID';
			$columns['ticket_subject'] = 'Subject';
			$columns['ticket_status'] = 'Ticket State';
			$columns['ticket_new_status'] = 'Ticket Status';
			$columns['queue_name'] = 'Mailbox';
			$columns['ticket_due'] = 'Due Date';
			$columns['thread_date'] = 'Last Activity Date';
			$columns['thread_received'] = 'Created Date';
			$columns['ticket_priority'] = 'Priority';
			$columns['address_address'] = 'Last Wrote Address';
			$columns['requestor_address'] = 'Requester Address';
			$columns['company_name'] = 'Company Name';
			$columns['total_time_worked'] = 'Total Time Worked';
			$columns['spam_probability'] = 'Spam Probabilty';
			$columns['ticket_spam_trained'] = 'Spam Trained (Spam/Not)';
			
			// [JAS]: List Requester Custom Fields
			if(!empty($handler->group_templates))
			foreach($handler->group_templates as $group) {
				if(!empty($group->fields))
				foreach($group->fields as $field) {
					$field_id = $field->field_id;
					$field_name = $field->field_name;
					$key = sprintf("g_%d_custom_%d",
							$group->group_id,
							$field_id
					);
					$val = sprintf("%s: %s",
							@htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE),
							@htmlspecialchars($field_name, ENT_QUOTES, LANG_CHARSET_CODE)
					);
					$columns[$key] = $val;
				}
			}
		}
		
		return $columns;
	}
	
	function getActiveColumns() {
		return explode(',', $this->column_string);
	}
	
};

class cer_TicketDashboardView extends cer_TicketViewSearch {
	var $slot_tag = "";
	var $view_bind_page = "index.php";
	
	function cer_TicketDashboardView($vid,$slot) {
		global $session;
		
//		@$v = $_REQUEST[$slot]; // assigned view
		@$v_sort_by = $_REQUEST[$slot."_sort_by"];
		@$v_asc = $_REQUEST[$slot."_asc"];
		@$v_p = $_REQUEST[$slot."_p"];
//		if(isset($v)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot] = $v; $v_p = 0; }
		if(isset($v_sort_by)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_sort_by"] = $v_sort_by; }
		if(isset($v_asc)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_asc"] = $v_asc; }
		if(isset($v_p)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_p"] = $v_p; }
		
		$this->cer_TicketView($vid,$slot);
		$this->slot_tag = $slot;
	}
	
	function setPrefs() {
		global $session;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->filter_rows = @$view_prefs->vars[$this->view_slot."_filter_rows"];
//		$this->filter_responded = @$view_prefs->vars[$this->view_slot."_filter_responded"];

		$this->show_options = !empty($session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_" . $this->slot_tag]) ? 1 : 0;

		$this->view_title_style = "boxtitle_blue_glass";

		if(empty($this->filter_rows)) $this->filter_rows = 10;
//		if(empty($this->filter_responded)) $this->filter_responded = 0;

		$this->view_adv_controls = 1;
		$this->show_mass = 1;
		$this->enableSearchActions();
		$this->view_exclusive = true;
	}
	
	function setViewDefaults()
	{
		global $session;

		$this->view_name = "Tickets";
		$this->show_mass = 1;
		$this->view_adv_2line = 1;
		$this->view_adv_controls = 1;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		if(empty($view_prefs->vars[$this->slot_tag."_sort_by"])) $view_prefs->vars[$this->slot_tag."_sort_by"] = "ticket_due";
		if(empty($view_prefs->vars[$this->slot_tag."_asc"])) $view_prefs->vars[$this->slot_tag."_asc"] = 0;

		$this->column_string = "ticket_id,ticket_subject,ticket_status,address_address,ticket_priority,queue_name,ticket_due";
		$this->columns[0] = new cer_TicketViewColumn("checkbox",$this,"bids[]");
		$this->columns[1] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[2] = new cer_TicketViewColumn("ticket_id",$this);
		$this->columns[3] = new cer_TicketViewColumn("ticket_status",$this);
		$this->columns[4] = new cer_TicketViewColumn("address_address",$this);
		$this->columns[5] = new cer_TicketViewColumn("ticket_priority",$this);
		$this->columns[6] = new cer_TicketViewColumn("queue_name",$this);
		$this->columns[7] = new cer_TicketViewColumn("ticket_due",$this);
	}
	
};

class cer_TicketViewSearch extends cer_TicketView {
	var $slot_tag = "sv";
	var $view_bind_page = "ticket_list.php";

	function cer_TicketViewSearch($vid="",$params) {
		$this->cer_TicketView($vid,$this->slot_tag,$params);
		//		$this->setPrefs();
	}

	function setPrefs() {
		global $session;
		$acl = CerACL::getInstance();

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->filter_rows = @$view_prefs->vars[$this->view_slot."_filter_rows"];
//		$this->filter_responded = @$view_prefs->vars[$this->view_slot."_filter_responded"];

		$this->show_options = !empty($session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_" . $this->slot_tag]) ? 1 : 0;

		$this->view_title_style = "boxtitle_blue_glass";

		if(empty($this->filter_rows)) $this->filter_rows = 25;
//		if(empty($this->filter_responded)) $this->filter_responded = 0;

		if($acl->has_priv(PRIV_TICKET_CHANGE,BITGROUP_1)) {
			$this->show_modify = true;
			$this->enableSearchActions();
		}
		$this->view_exclusive = true;
	}

	function setViewDefaults()
	{
		global $session;

		$this->view_name = "Tickets";
		$this->view_adv_2line = 1;
		$this->view_adv_controls = 1;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		if(empty($view_prefs->vars["sv_sort_by"])) $view_prefs->vars["sv_sort_by"] = "ticket_due";
		if(empty($view_prefs->vars["sv_asc"])) $view_prefs->vars["sv_asc"] = 0;

		$this->column_string = "ticket_id,ticket_subject,ticket_status,address_address,ticket_priority,queue_name,ticket_due";
		$this->columns[0] = new cer_TicketViewColumn("checkbox",$this,"bids[]");
		$this->columns[1] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[2] = new cer_TicketViewColumn("ticket_id",$this);
		$this->columns[3] = new cer_TicketViewColumn("ticket_status",$this);
		$this->columns[4] = new cer_TicketViewColumn("address_address",$this);
		$this->columns[5] = new cer_TicketViewColumn("ticket_priority",$this);
		$this->columns[6] = new cer_TicketViewColumn("queue_name",$this);
		$this->columns[7] = new cer_TicketViewColumn("ticket_due",$this);
	}

	function _buildMaskList() {
		$show_mask_sql = null;
		
		if (isset($this->params['criteria']['mask'])) {
			$mask = @$this->params['criteria']['mask']['mask'];
			if(is_numeric($mask)) { // id match
				$show_mask_sql = sprintf("t.ticket_id = %d",
					$mask
				);
			} else { // mask match
				$show_mask_sql = sprintf("t.ticket_mask LIKE '%s%%'",
					$mask
				);
			}
		}
		
		return $show_mask_sql;
	}
	
	// [JAS]: Merge in any search filters on status with the view preferences
	// Merge any view exclusions with the desired search status value
	function _buildStatusList() {
		$show_status_sql = null;

		if (isset($this->params['criteria']['status'])) {

			switch(@$this->params['criteria']['status']['status']) {
				// Any
				case "":
				case "0":
					// defaults work
					break;

				// Any Active
				default:
				case "1":
					$show_status_sql = "t.is_closed = 0 AND t.is_waiting_on_customer = 0 AND t.is_deleted = 0";
					break;
				
				case "2": // open
					$show_status_sql = "t.is_closed = 0 AND t.is_deleted = 0";
					break;
					
				case "3": // resolved
					$show_status_sql = "t.is_closed = 1 AND t.is_deleted = 0";
					break;
				
				case "4": // dead
					$show_status_sql = "t.is_deleted = 1";
					break;
			}

		}

		return $show_status_sql;
	}

	function _buildWaitingList() {
		$wait_sql = null;

		if (isset($this->params['criteria']['waiting'])) {

			switch(@$this->params['criteria']['waiting']['waiting']) {
				case "":
					break;
				case 0:
					$wait_sql = "t.is_waiting_on_customer = 0";
					break;
				case 1:
					$wait_sql = "t.is_waiting_on_customer = 1";
					break;
			}
		}
		
		return $wait_sql;
	}
	
	function _buildQueueList() {
		$qid_sql = null;
		$acl = CerACL::getInstance();

		$qids = array();
		if(isset($this->params['criteria']['queue'])) {
			$searchids = array_keys(@$this->params['criteria']['queue']['queues']);
			if(is_array($searchids))
			foreach($searchids as $id) {
				if(isset($acl->queues[$id]))
					$qids[$id] = $id;
			}
		} else {
			$qids = array_keys($acl->queues);
		}

		if(!empty($qids))
			$qid_sql = implode(',', $qids);		
		else
			$qid_sql = "-1";
		
		return $qid_sql;
	}
	
	// [JAS]: Now we don't only find the original sender, but we allow searches for any of the
	//	ticket requesters as well.  We have to do a pre-search query for requesters that match
	//  the sender substring (if given).
	function _buildSenderList() {
		$requester_sql = null;

		if($this->params['criteria']['requester']) { // override
			$sql = sprintf("SELECT DISTINCT r.address_id ".
				"FROM (requestor r, address a) ".
				"WHERE r.address_id = a.address_id ".
				"AND a.address_address LIKE %s ".
				"GROUP BY a.address_id",
				$this->db->escape('%' . $this->params['criteria']['requester']['requester'] . '%')
			);
			$req_res = $this->db->query($sql);
	
			if($this->db->num_rows($req_res)) {
				$req_ary = array();
	
				while($row = $this->db->fetch_row($req_res)) {
					$req_ary[] = $row["address_id"];
				}
	
				CerSecurityUtils::integerArray($req_ary);
				$requester_sql = implode(",",$req_ary);
			}

			if(empty($requester_sql))
				$requester_sql = "-1";
		}

		return $requester_sql;
	}

	function _buildTagList() {
		$tag_sql = null;
		$tag_list = array();

		if(isset($this->params['criteria']['tags']) && is_array($this->params['criteria']['tags']['tags'])) {
			$keys = array_keys(@$this->params['criteria']['tags']['tags']);
			$tag_sql = implode(',', $keys);
		}
		
		return $tag_sql;
	}
	
//	function _buildTeamList() {
//		$team_sql = null;
//		$acl = CerACL::getInstance();
//		$team_list = array();
//
//		if(isset($this->params['criteria']['workflow']) && is_array($this->params['criteria']['workflow']['teams'])) {
//			$keys = array_keys(@$this->params['criteria']['workflow']['teams']);
//			$team_sql = implode(',', $keys);
//		}
//
//		return $team_sql;
//	}
	
	function _buildPriorityList() {
		$priority_sql = null;
		
		if(isset($this->params['criteria']['priority']) && is_array($this->params['criteria']['priority']['priorities'])) {
			$priority_sql = implode(',', array_keys($this->params['criteria']['priority']['priorities']));
		}
		
		return $priority_sql;
	}

	function _buildAgentList() {
		$agent_sql = null;
		$agent_list = array();

		if(isset($this->params['criteria']['workflow']) && is_array($this->params['criteria']['workflow']['agents'])) {
			$keys = array_keys(@$this->params['criteria']['workflow']['agents']);
			$agent_sql = implode(',', $keys);
		}

		return $agent_sql;
	}
	
	function _buildFlagList() {
		$flag_sql = null;
		
		if(isset($this->params['criteria']['flag']) && !empty($this->params['criteria']['flag']['flag_mode'])) {
			$keys = array_keys(@$this->params['criteria']['flag']['flags']);
			$flag_sql = sprintf(" AND fta.agent_id IN (%s)", implode(',', $keys));
		} elseif(isset($this->params['criteria']['flag']) && empty($this->params['criteria']['flag']['flag_mode'])) { // not flagged
			//$flag_sql = " AND fta.agent_id IS NULL";
			$flag_sql = " AND t.num_flags = 0";
		}
		
		return $flag_sql;
	}
	
	function _buildCompanyList() {
		$company_sql = null;

		if($this->params['criteria']['company']) {
			$sql = sprintf("SELECT c.`id` FROM `company` c WHERE c.`name` LIKE %s",
				$this->db->escape('%' . $this->params['criteria']['company']['company'] . '%')
			);
			$res = $this->db->query($sql);
			
			if($this->db->num_rows($res)) {
				$com_ary = array();
				
				while($row = $this->db->fetch_row($res)) {
					$com_ary[] = $row['id'];
				}
				
				$company_sql = implode(',', $com_ary);
			} else {
				$company_sql = "-1";
			}
		}

		return $company_sql;
	}

	function _buildNewStatusList() {
		$sid_sql = null;

		$sids = array();
		if(isset($this->params['criteria']['ticket_status'])) {
			$searchids = array_keys(@$this->params['criteria']['ticket_status']['statuses']);
			if(is_array($searchids))
			foreach($searchids as $id) {
				$sids[$id] = $id;
			}
		}

		if(!empty($sids))
			$sid_sql = implode(',', $sids);		
		else
			$sid_sql = "-1";
		
		return $sid_sql;
	}

	function _buildSubjectSearchWords() {
		$rows = 0;
		
//		if($this->params["search_subject"]) {
//			$rows = $this->_parseSearchString($this->params["search_subject"],1);
//		}

		if(isset($this->params['criteria']['subject'])) {
			$subject = $this->params['criteria']['subject']['subject'];
			$rows = $this->_parseSearchString($subject,1);
		}

		return $rows;
	}

	function _buildContentSearchWords() {
		$rows = 0;
		
//		if($this->params["search_content"]) {
//			$rows = $this->_parseSearchString($this->params["search_content"],0);
//		}

		if(isset($this->params['criteria']['content'])) {
			$content = $this->params['criteria']['content']['content'];
			$rows = $this->_parseSearchString($content,0);
		}

		return $rows;
	}

	function _parseSearchString($str, $is_subject=0) {
		global $cerberus_db;
		global $session;
		$cfg = CerConfiguration::getInstance();
		
		$cer_search = new cer_SearchIndex();
		$sql = "";
		
		$content_string = strtolower($str);
		$content_string = cer_Whitespace::mergeWhitespace($content_string);
		
		$search_terms = explode(" ",$content_string);
		$terms_required = array();
		$terms_optional = array();
		$terms_excluded = array();
		$terms_required_str = "";
		$terms_optional_str = "";
		$terms_excluded_str = "";
		
		if(!empty($search_terms))
		foreach($search_terms as $w) {
			$oper = substr($w,0,1);
			$word = substr($w,1);
			switch($oper) {
				case "+":
					$terms_required[] = $word;
					break;
				case "-":
					$terms_excluded[] = $word;
					break;
				default:
					$terms_optional[] = $oper . $word;
					break;
			}
		}
		
		if(!empty($terms_required)) {
			$terms_pre = count($terms_required);
			$terms_required_str = implode(" ",$terms_required);
			$cer_search->indexWords($terms_required_str, $cfg->settings["search_index_numbers"], 1);
			$terms_required = $cer_search->loadWordIDs(1);
			if(count($terms_required) < $terms_pre) $terms_required = array(-99);
//			echo "REQ: "; print_r($terms_required);echo "<BR>";
		}
		
		if(!empty($terms_optional)) {
			$terms_optional_str = implode(" ",$terms_optional);
			$cer_search->indexWords($terms_optional_str, $cfg->settings["search_index_numbers"], 1);
			$terms_optional = $cer_search->loadWordIDs(1);
//			echo "OPT: ";print_r($terms_optional);echo "<BR>";
		}
		
		if(!empty($terms_excluded)) {
			$terms_excluded_str = implode(" ",$terms_excluded);
			$cer_search->indexWords($terms_excluded_str, $cfg->settings["search_index_numbers"], 1);
			$terms_excluded = $cer_search->loadWordIDs(1);
//			echo "NEQ: ";print_r($terms_excluded);echo "<BR>";
		}

		$req_count = count($terms_required);
		$req_add = 0;
		$nuke_count = 0;
		
		if(!empty($terms_required)) {
			$req_list = implode(',', $terms_required);
			if($terms_required[0] != -99) $nuke_count += $req_count;
			if($terms_required[0] != -99) $req_add = $req_count;
		}
		
		$heap_table = $this->heap_name . (($is_subject) ? "_s" : "_c");
		
		$sql = "DROP TABLE IF EXISTS `". $heap_table . "`";
		$cerberus_db->query($sql);

       $where = sprintf("WHERE 1 %s%s",
       	(!empty($req_list) ? " AND word_id IN ($req_list) " : ""),
         ((!empty($req_list) && !empty($is_subject)) ? " AND in_subject = 1 " : "")
       );
         
       $where = sprintf("%s",
			(($where == "WHERE 1 " && !count($terms_excluded)) ? ' WHERE 0 ' : $where)
       );
		
		$sql = sprintf("CREATE TABLE `%s` TYPE=HEAP ".
			"SELECT ticket_id, count( word_id ) AS hit_count, 0 AS optional_count ".
			"FROM `search_index` ".
			"%s ".
			"GROUP BY ticket_id ".
			"%s ",
				$heap_table,
          	$where,
          	(!empty($req_list) ? sprintf(" HAVING hit_count = %d ",$req_count) : "")
       );
      
//		echo sprintf($sql, $where) . "<BR>";		
		$cerberus_db->query($sql);
		
		$sql = sprintf("ALTER TABLE `%s` ADD PRIMARY KEY (ticket_id);",
			$heap_table
		);
		$cerberus_db->query($sql);
		
		if(!empty($terms_optional)) {
			$opt_list = implode(",",$terms_optional);
			$sql = sprintf("REPLACE INTO `%s` SELECT w.ticket_id, ".
				"count(w.word_id) + %d AS hit_count, ".
				"count(w.word_id) AS optional_count ".
				"FROM `search_index` w ".
				"WHERE word_id IN ($opt_list) " .
				(!empty($is_subject) ? " AND in_subject = 1 " : "") .
				" GROUP BY ticket_id;",
				$heap_table,
				$req_add
			);
			$cerberus_db->query($sql);
			// [JSJ]: Clean out optionals if we didn't hit on our requireds
			if($req_add > 0) {
				$sql = sprintf("DELETE FROM `%s` WHERE hit_count = optional_count",
					$heap_table
				);
				$cerberus_db->query($sql);
			}
		}
		
		if(!empty($terms_excluded)) {
			$exc_list = implode(",",$terms_excluded);			
			$sql = sprintf("REPLACE INTO `%s` SELECT w.ticket_id, 0, 0 ".
				"FROM search_index w ".
				"WHERE w.word_id in ($exc_list) " .
				(!empty($is_subject) ? " AND in_subject = 1 " : ""),
					$heap_table
			);
			$cerberus_db->query($sql);
		}

		// [JAS]: This needs to be a bit more complex later so it can tell that we're deleting optional 
		// 		matches.  Right now n optionals that pass req_count will stick.
//		if($req_add > 0) {
			$sql = sprintf("DELETE FROM `%s` WHERE hit_count < %d",
				$heap_table,
				(($nuke_count) ? $nuke_count : 1)
			);
			$cerberus_db->query($sql);
//			echo $sql . "<BR>";		
//		}
		
		$sql = sprintf("SELECT ticket_id FROM `%s`",
			$heap_table
		);
		$res = $cerberus_db->query($sql);
		$rows = $cerberus_db->num_rows($res);
		
		$rows = empty($rows) ? -1 : $rows;
		
		return $rows;
	}
	
	function _buildCreatedDateSearch() {
		$date_sql = null;

		if(isset($this->params['criteria']['created'])) {
			$from_date = new cer_DateTime($this->params['criteria']['created']['from']);
			$to_date = new cer_DateTime($this->params['criteria']['created']['to']);
			$f_string = $from_date->getDate("%Y-%m-%d %H:%M:%S");
			$t_string = $to_date->getDate("%Y-%m-%d %H:%M:%S");
			
			$date_sql = sprintf(" AND t.ticket_date BETWEEN %s AND %s",
				$this->db->escape($f_string),
				$this->db->escape($t_string)
			);
		}

		return $date_sql;
	}

	function _buildUpdatedDateSearch() {
		$date_sql = null;

		if(isset($this->params['criteria']['last_updated'])) {
			$from_date = new cer_DateTime($this->params['criteria']['last_updated']['from']);
			$to_date = new cer_DateTime($this->params['criteria']['last_updated']['to']);
			$f_string = $from_date->getDate("%Y-%m-%d %H:%M:%S");
			$t_string = $to_date->getDate("%Y-%m-%d %H:%M:%S");
			
			$date_sql = sprintf(" AND t.ticket_last_date BETWEEN %s AND %s",
				$this->db->escape($f_string),
				$this->db->escape($t_string)
			);
		}

		return $date_sql;
	}

	function _buildDueDateSearch() {
		$date_sql = null;

		if(isset($this->params['criteria']['due'])) {
			$from_date = new cer_DateTime($this->params['criteria']['due']['from']);
			$to_date = new cer_DateTime($this->params['criteria']['due']['to']);
			$f_string = $from_date->getDate("%Y-%m-%d %H:%M:%S");
			$t_string = $to_date->getDate("%Y-%m-%d %H:%M:%S");
			
			$date_sql = sprintf(" AND t.ticket_due BETWEEN %s AND %s",
				$this->db->escape($f_string),
				$this->db->escape($t_string)
			);
		}

		return $date_sql;
	}

	function _buildCustomFieldSearch() {
		$field_from_sql = null;
		$field_where_sql = null;

		// [JAS]: If we're not in advanced mode, bail out.
//		if(!$this->params["search_advanced"])
//		return null;

		$ids = array();
		if(is_array($this->params['criteria']))
		foreach($this->params['criteria'] as $criteria => $void) {
			if(substr($criteria,0,12) == "custom_field") {
				$field_id = substr($criteria,12);
				$ids[$field_id] = $field_id;
			}
		}

		if(is_array($ids) && !empty($ids)) {
			$field_from = array();
			$field_where = array();

			foreach($ids as $id) {
				$type = @$this->params['criteria']['custom_field' . $id]['type'];

				$field_from[] = sprintf("`field_group_values` v%d",
					$id
				);

				switch($type) {
					case "S":
					case "T":
						$field_where[] = sprintf("(v%d.field_id = %d AND v%d.entity_code IN ('T','R') AND v%d.entity_index = IF (v%d.entity_code = 'R', t.opened_by_address_id,t.ticket_id) AND v%d.field_value LIKE '%s')",
							$id,
							$id,
							$id,
							$id,
							$id,
							$id,
							sprintf("%s", '%'.$this->params['criteria']['custom_field'.$id]['value'].'%')
						);
						break;
					case "D":
						$field_where[] = sprintf("(v%d.field_id = %d AND v%d.entity_code IN ('T','R') AND v%d.entity_index = IF (v%d.entity_code = 'R', t.opened_by_address_id,t.ticket_id) AND v%d.field_value IN (%s))",
							$id,
							$id,
							$id,
							$id,
							$id,
							$id,
							implode(',', @array_keys($this->params['criteria']['custom_field' . $id]['options']))
						);
						break;
					case "E":
						$from = $this->params['criteria']['custom_field'.$id]['from'];
						$to = $this->params['criteria']['custom_field'.$id]['to'];
						$fromDate = new cer_DateTime($from);
						$toDate = new cer_DateTime($to);
						
						$field_where[] = sprintf("(v%d.field_id = %d AND v%d.entity_code IN ('T','R') AND v%d.entity_index = IF (v%d.entity_code = 'R', t.opened_by_address_id,t.ticket_id) AND v%d.field_value > %d AND v%d.field_value < %d)",
							$id,
							$id,
							$id,
							$id,
							$id,
							$id,
							$fromDate->mktime_datetime,
							$id,
							$toDate->mktime_datetime
						);
						break;
				}
			} // foreach $id
		}

		if(!empty($field_from))
		$field_from_sql = implode(',', $field_from);

		if(!empty($field_where))
		$field_where_sql = implode(" AND ", $field_where);

		return array($field_from_sql, $field_where_sql);
	}

};

class cer_TicketViewColumnTable
{
	var $table_name = null;
	var $table_prefix = null;
	var $sort_sql = null;
	var $group_by = null;
	var $selects = 0;

	function cer_TicketViewColumnTable($name,$prefix,$sql,$group_by="") {
		$this->table_name = $name;
		$this->table_prefix = $prefix;
		$this->sort_sql = $sql;
		$this->group_by = $group_by;
	}
};

class cer_TicketViewColumn
{
	var $column_name = null; // db column name
	var $column_heading = null; // column display name
	var $column_align = "left"; // column alignment (center,right,left)
	var $column_type = "normal";
	var $column_url = null; // column url for sorting
	var $column_extras = null; // extras such as nowrap, etc.
	var $column_sortable = true;
	var $column_proc = null; // column function (date to age, etc)
	var $table = null;
	var $table_field_name = null;

	var $field_id = 0;
	var $group_id = 0;

	var $parent_view = null; // parent view pointer
	var $element_name = null; // if we're drawing a <FORM> element, name it this

	function cer_TicketViewColumn($c_name,&$view_obj,$name="")
	{
		$this->column_name = $c_name;
		$this->parent_view = &$view_obj;
		$this->element_name = $name;

		$this->assign_default_proc($c_name);
		$this->set_column_url();
	}

	function setTable($table) {
		$ptr = &$this->parent_view->tables[$table];
		$ptr->selects++;
		return $ptr;
	}

	// [JAS]: Handle Column Specific Procedures
	function assign_default_proc($c_name)
	{
		switch($c_name)
		{
			case "checkbox":
			$this->column_proc = "view_proc_checkbox";
			$this->column_heading = strtolower(LANG_WORD_ALL);
			$this->column_align = "center";
			break;

			case "ticket_id":
			$this->column_proc = "view_proc_print_id";
			$this->column_heading = "#";
			$this->column_extras = "nowrap";
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_id";
			break;

			case "ticket_subject":
			$this->column_proc = "view_proc_print_subject_link";
			$this->column_heading = LANG_WORD_SUBJECT;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_subject";
			break;

			case "ticket_status":
			$this->column_proc = "view_proc_print_ticket_status";
			$this->column_heading = LANG_WORD_STATUS;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "is_closed";
			break;

			case "ticket_new_status":
			$this->column_proc = "view_proc_print_ticket_new_status";
			$this->column_heading = LANG_WORD_NEW_STATUS;
			$this->table = $this->setTable("ticket_status");
			$this->table_field_name = "ticket_status_text";
			break;

			case "queue_name":
			$this->column_proc = "view_proc_print_queue_link";
			$this->column_heading = "Mailbox";
			$this->table = $this->setTable("queue");
			$this->table_field_name = "queue_name";
			break;

			case "thread_received":
			$this->column_proc = "view_proc_date";
			$this->column_heading = "Created";
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_date";
			break;

			case "thread_date":
			$this->column_proc = "view_proc_date_to_age";
			$this->column_heading = LANG_WORD_AGE;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_last_date";
			break;

			case "ticket_due":
			$this->column_proc = "view_proc_due_to_age";
			$this->column_heading = LANG_WORD_DUE;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_due";
			break;

			case "ticket_priority":
			$this->column_proc = "view_proc_print_priority";
			$this->column_heading = LANG_WORD_PRIORITY;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_priority";
			$this->column_align = "center";
			break;

			case "address_address":
			$this->column_proc = "view_proc_print_email_address";
			$this->column_heading = LANG_WORD_WROTE_LAST;
			$this->table = $this->setTable("address_last");
			$this->table_field_name = "address_address";
			break;

			case "requestor_address":
			$this->column_proc = "view_proc_print_email_address";
			$this->column_heading = LANG_WORD_REQUESTER;
			$this->table = $this->setTable("address_first");
			$this->table_field_name = "requestor_address";
			break;

			case "company_name":
			$this->column_proc = "view_proc_print_small";
			$this->column_heading = LANG_WORD_COMPANY;
			$this->table = $this->setTable("company");
			$this->table_field_name = "name";
			break;

			case "total_time_worked":
			$this->column_proc = "view_proc_print_worked";
			$this->column_heading = LANG_WORD_WORKED;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_time_worked";
			break;

			case "spam_probability":
			$this->column_proc = "view_proc_print_spam_probability";
			$this->column_heading = LANG_WORD_SPAM;
			$this->column_sortable = false;
			break;

			case "ticket_spam_trained":
			$this->column_proc = "view_proc_print_spam_trained";
			$this->column_heading = LANG_WORD_TRAINING;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_spam_trained";
			break;

			default:
			{
				// [JAS]: If we're looking at a group custom field column.
				if(substr($c_name,0,2) == "g_") {
					list($group_id,$fld_id) = sscanf($c_name,"g_%d_custom_%d");
					$this->column_type = "custom_field";
					$this->column_heading = $this->parent_view->field_handler->group_templates[$group_id]->fields[$fld_id]->field_name;
					$this->field_id = $fld_id;
					$this->group_id = $group_id;
					$this->column_proc = "view_proc_print_custom_field";

					$sort_sql = sprintf("SELECT t.ticket_id, a.address_id, v_%d.field_value AS g_%d_custom_%d ".
						"FROM (ticket t, thread thr, thread th, address a, queue q, address ad %%s %%s %%s) ". // [JAS]: leave the double %%, we're injecting vals later
						"LEFT JOIN public_gui_users pu ON (a.public_user_id = pu.public_user_id) ".
						"LEFT JOIN company c ON (pu.company_id = c.id) ".
						"LEFT JOIN field_group_values v_%d ".
						"ON ( v_%d.entity_index =  IF ( v_%d.entity_code =  'R', t.opened_by_address_id, t.ticket_id ) ".
						"AND v_%d.field_id = %d ) ".
						"WHERE t.min_thread_id = thr.thread_id ".
						"AND t.max_thread_id = th.thread_id ".
						"AND a.address_id = t.opened_by_address_id ".
						"AND ad.address_id = th.thread_address_id ".
						"AND t.ticket_queue_id = q.queue_id ",
							$fld_id,
							$group_id,
							$fld_id,
							$fld_id,
							$fld_id,
							$fld_id,
							$fld_id,
							$fld_id
					);

					$table_name = "v_" . $fld_id;

					$this->parent_view->tables[$table_name] = new cer_TicketViewColumnTable("field_group_values",$table_name,$sort_sql);
					$this->table = $this->setTable($table_name);
					$this->table_field_name = $c_name;
				}
				break;
			}
		}
	}

	function set_column_url()
	{
		global $session; // clean up

		$slot = $this->parent_view->view_slot;

		switch($slot)
		{
//			case "uv": // unassigned or assigned homepage view
//			if($this->column_name != "checkbox")
//			{
//				$this->column_url = cer_href(sprintf("%s?%s_p=%d&%s_asc=%d&%s_sort_by=%s",
//				@$this->parent_view->page_name,
//				$slot,
//				0,
//				$slot,
//				((($session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_sort_by"]==$this->column_name) && $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_asc"]==1)?"0":"1"),
//				$slot,
//				$this->column_name
//				));
//			}
//			else if($this->column_name == "checkbox")
//			{
//				$this->column_url = sprintf("javascript:checkAllToggle_%s();",
//				$this->parent_view->view_slot
//				);
//			}
//			break;

			default:
			case "sv": // search results 
			if($this->column_name != "checkbox")
			{
				$this->column_url = cer_href(sprintf("%s?%s_p=%d&%s_asc=%d&%s_sort_by=%s",
				$this->parent_view->page_name,
				$slot,
				0,
				$slot,
				((($session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_sort_by"]==$this->column_name) && $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_asc"]==1)?"0":"1"),
				$slot,
				$this->column_name
				));
			}
			else if($this->column_name == "checkbox")
			{
				$this->column_url = sprintf("javascript:checkAllToggle_%s();",
				$this->parent_view->view_slot
				);
			}
			break;

//			case "bv": // batch view
//			//			global $mode; // fix
//			//			global $ticket; // fix
//
//			if($this->column_name != "checkbox")
//			{
//				$this->column_url = cer_href(sprintf("%s?%s&%s_p=%d&%s_asc=%d&%s_sort_by=%s",
//				$this->parent_view->page_name,
//				$this->parent_view->page_args,
//				$slot,
//				0,
//				$slot,
//				((($session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_sort_by"]==$this->column_name) && $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_asc"]==1)?"0":"1"),
//				$slot,
//				$this->column_name
//				));
//			}
//			else if($this->column_name == "checkbox")
//			{
//				$this->column_url = sprintf("javascript:checkAllToggle_%s();",
//				$this->parent_view->view_slot
//				);
//
//			}
//			break;

//			default:
//			$this->column_url = "#";
//			break;
		}
	}

	// [JAS]: $arg = value of row's field, $proc_args = object of row's variables
	//		that are usable in the procedure
	function execute_proc($arg="",$proc_args="")
	{
		if(!empty($this->column_proc))
		{ return call_user_func($this->column_proc,$arg,$proc_args); }
		else
		{ return ""; }
	}
};

class CerTicketViewHelper {
	function saveSchema($vid,$name,$columns,$order=0) {
		$db = cer_Database::getInstance();
		if(!is_array($columns) || empty($vid))
			return;

		// [JAS]: Ignore empty dropdowns
		foreach($columns as $idx => $c) {
			if(empty($c)) unset($columns[$idx]);
		}
			
		$sql = sprintf("UPDATE `ticket_views` SET `view_name` = %s, `view_columns` = %s, `view_order` = %d WHERE view_id = %d",
			$db->escape($name),
			$db->escape(implode(',', $columns)),
			$order,
			$vid
		);
		$db->query($sql);
		
		// Import params from a saved search
//		if(!empty($search_id)) {
//			include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
//			$search = new CerSearch();
//			
//			$view_params = array();
//			if(($saved = $search->loadSearch($search_id))) {
//				$view_params = array("criteria"=>$saved->params);
//			}
//			
//			$sql = sprintf("UPDATE `ticket_views` SET `view_params` = %s WHERE view_id = %d",
//				$db->escape(serialize($view_params)),
//				$vid
//			);
//			$db->query($sql);
//		}
		
		return TRUE;
	}
	
	function getSchema($vid) {
		$db = cer_Database::getInstance();
		$params = array();
		
		if(empty($vid)) {
			return;	
		}
		
		$sql = sprintf("SELECT `view_params` FROM `ticket_views` WHERE view_id = %d",
			$vid
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			$row = $db->fetch_row($res);
			$params = unserialize(stripslashes($row['view_params']));
		}

		return $params;
	}
	
	function saveParams($vid,$params) {
		$db = cer_Database::getInstance();
		
		if(!is_array($params) || empty($vid))
			return;
		
		$params = array("criteria"=>$params);
			
		$sql = sprintf("UPDATE `ticket_views` SET `view_params` = %s WHERE view_id = %d",
			$db->escape(serialize($params)),
			$vid
		);
		$db->query($sql);
	}
}
