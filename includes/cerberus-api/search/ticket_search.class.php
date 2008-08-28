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
| File: ticket_search.class.php
|
| Purpose: Ticket search box functionality
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

// [JAS]: Set up the global scope for search vars
@$psearch_sender = $session->vars["psearch_sender"];
@$psearch_status = $session->vars["psearch_status"];
@$psearch_subject = $session->vars["psearch_subject"];
@$psearch_content = $session->vars["psearch_content"];
@$psearch_company = $session->vars["psearch_company"];
@$psearch_date = $session->vars["psearch_date"];
@$psearch_fdate = $session->vars["psearch_fdate"];
@$psearch_tdate = $session->vars["psearch_tdate"];
@$psearch_tags = $session->vars["psearch_tags"];
@$psearch_teams = $session->vars["psearch_teams"];
@$psearch_agents = $session->vars["psearch_agents"];
@$psearch_flagged = $session->vars["psearch_flagged"];

if(!isset($p_qid)) $p_qid = "";
if(!isset($psearch_sender)) $psearch_sender="";
if(!isset($psearch_subject)) $psearch_subject="";
if(!isset($psearch_content)) $psearch_content="";
if(!isset($psearch_company)) $psearch_company="";
if(!isset($psearch_date)) $psearch_date="";
if(!isset($psearch_fdate)) $psearch_fdate="";
if(!isset($psearch_tdate)) $psearch_tdate="";
if(!isset($psearch_tags)) $psearch_tags="";
if(!isset($psearch_teams)) $psearch_teams="";
if(!isset($psearch_agents)) $psearch_agents="";
if(!isset($psearch_flagged)) $psearch_flagged="";
if(!isset($p_qid)) $p_qid="";

class CER_TICKET_SEARCH_BOX {
	var $search_status_options = array();
	var $searchTags = array();
	var $searchTeams = array();
	var $searchAgents = array();
	
	var $search_flagged = 0;
	
	var $field_groups = array();
	var $field_groups_field_ids = null;
	var $field_values = array();
	
	function CER_TICKET_SEARCH_BOX() {
		global $session; // [JAS]: Clean this up
		global $cerberus_translate; // clean
		global $cer_hash; // clean
		
		$this->field_groups = new cer_CustomFieldGroupHandler();
		$this->field_groups->loadGroupTemplates();
		
		// [JAS]: A list of all the custom field IDs we're drawing, so the search
		//	page knows what values to check for on the $_REQUEST side.
		$ids = array();
		
		foreach($this->field_groups->group_templates as $g) {
			foreach($g->fields as $f) {
				$id = $f->field_id;
				$ids[] = $id;
				$this->field_values[$id] = @$session->vars["psearch"]->params["search_field_" . $id];
			}
		}
		
		if(count($ids)) {
			$this->field_groups_field_ids = implode(',',$ids);
		}
	}

};


class CER_TICKET_SEARCH_BOX_FIELD {
	var $field_id = 0;
	var $field_name = null;
	var $field_type = null;
	var $field_options = array();
	var $pvalue = null;
};

?>