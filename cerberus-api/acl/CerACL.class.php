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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/bits/bitflags.php");

// BITGROUP 1
define("PRIV_",						BITFLAG_1);
define("PRIV_TICKET_CHANGE",		BITFLAG_2);
define("PRIV_CONTACT_CHANGE",		BITFLAG_3);
define("PRIV_COMPANY_CHANGE",		BITFLAG_4);
define("PRIV_CONFIG",				BITFLAG_5);
//define("",			BITFLAG_6); // nuke
//define("",				BITFLAG_7); // nuke
define("PRIV_VIEW_CHANGE",			BITFLAG_8);
//define("",	BITFLAG_9); // nuke, was PRIV_VIEW_UNASSIGNED
define("PRIV_KB",						BITFLAG_10);
define("PRIV_REPORTS",				BITFLAG_11);
define("PRIV_TICKET_DELETE",		BITFLAG_12);
define("PRIV_PLACEHOLDER",			BITFLAG_13); // nuke?
define("PRIV_BLOCK_SENDER",		BITFLAG_14);
define("PRIV_KB_EDIT",				BITFLAG_15);
define("PRIV_KB_DELETE",			BITFLAG_16);

// BITGROUP 2
define("PRIV_DATA_IO",				BITFLAG_1); // import/export
define("REST_EMAIL_ADDY",			BITFLAG_2); // can't see requester emails
//define("",	BITFLAG_3); // nuke, was team remove flags
define("PRIV_REMOVE_ANY_FLAGS",	BITFLAG_4);
//...
define("PRIV_CFG_POP3_CHANGE",	BITFLAG_8);
define("PRIV_CFG_POP3_DELETE",	BITFLAG_9);
define("PRIV_CFG_PARSER_FAILED",	BITFLAG_10);
define("PRIV_CFG_PARSER_IMPORT",	BITFLAG_11);
define("PRIV_CFG_QUEUES_CHANGE",	BITFLAG_12);
define("PRIV_CFG_QUEUES_DELETE",	BITFLAG_13);
define("PRIV_CFG_QUEUES_CATCHALL",	BITFLAG_14);
define("PRIV_CFG_RULES_CHANGE",	BITFLAG_15);
define("PRIV_CFG_RULES_DELETE",	BITFLAG_16);
define("PRIV_CFG_PARSER_LOG",		BITFLAG_17);
define("PRIV_CFG_SC_PROFILES",	BITFLAG_18);
define("PRIV_CFG_HD_SETTINGS",	BITFLAG_19);
define("PRIV_CFG_TEAMS_CHANGE",	BITFLAG_20);
define("PRIV_CFG_TEAMS_DELETE",	BITFLAG_21);
define("PRIV_CFG_TAGS_CHANGE",	BITFLAG_22);
define("PRIV_CFG_TAGS_DELETE",	BITFLAG_23);
define("PRIV_CFG_AGENTS_CHANGE",	BITFLAG_24);
define("PRIV_CFG_AGENTS_DELETE",	BITFLAG_25);
define("PRIV_CFG_SCHED_TASKS",	BITFLAG_26);
define("PRIV_CFG_MAINT_REPAIR",	BITFLAG_27);
define("PRIV_CFG_MAINT_PURGE",	BITFLAG_28);
define("PRIV_CFG_MAINT_ATTACH",	BITFLAG_29);
define("PRIV_CFG_CUSTOM_FIELDS",	BITFLAG_30);
define("PRIV_CFG_SLA_CHANGE",		BITFLAG_31);

// BITGROUP 3
define("PRIV_CFG_SCHEDULES",		BITFLAG_1);
define("PRIV_CFG_INDEXES",			BITFLAG_2);
define("PRIV_CFG_WORKSTATION",	BITFLAG_3);
define("PRIV_REPORTS_INSTALL",	BITFLAG_4);

class CerACL {
	var $acl1 = 0;
	var $acl2 = 0;
	var $acl3 = 0;
	var $user_id = 0;
	var $is_superuser = 0;
	var $teams = array();
	var $queues = array();
	var $tagsets = array();

	function CerACL($p)
	{
		if($p != "private")
			die("Don't instantiate CerTeamACL directly.  Use CerTeamACL::getInstance()");
			
		global $session; // clean up
		$this->user_id = @$session->vars["login_handler"]->user_id;
		$this->is_superuser = @$session->vars["login_handler"]->user_superuser;
		
		// [JAS]: Pull ACL out of group table
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
		$wsteams = CerWorkstationTeams::getInstance();
		
		$acl1 = array();
		$acl2 = array();
		$acl3 = array();
		
		$teams = $wsteams->getTeams();
		foreach($teams as $teamId => $team) {
			if(isset($team->agents[$this->user_id])) { // agent is in this team
				$this->teams[$teamId] = $teamId;
				$acl1[] = $team->acl1;
				$acl2[] = $team->acl2;
				$acl3[] = $team->acl3;
				
				if(is_array($team->queues))
				foreach($team->queues as $qid=>$q) {
					$this->queues[$qid] = $qid;
				}
				
				if(is_array($team->tagsets))
				foreach($team->tagsets as $tsid=>$ts) {
					$this->tagsets[$tsid] = $tsid;
				}
			}
		}
		
		// [JAS]: Loop through it and see if each of 31 bits is set per bit group, 
		// if so build an aggregate permission bit set
		foreach($acl1 as $idx => $a) {
			for($x=0;$x<31;$x++) {
				// Set bits in class scope
				$bit = pow(2,$x);
				if(cer_bitflag_is_set($bit,$acl1[$idx]) && !cer_bitflag_is_set($bit,$this->acl1)) $this->acl1 += $bit;
				if(cer_bitflag_is_set($bit,$acl2[$idx]) && !cer_bitflag_is_set($bit,$this->acl2)) $this->acl2 += $bit;
				if(cer_bitflag_is_set($bit,$acl3[$idx]) && !cer_bitflag_is_set($bit,$this->acl3)) $this->acl3 += $bit;
			}
		}
	}

	/**
	 * Enter description here...
	 * @return CerACL
	 *
	 */
	function getInstance() {
		static $instance = null;
		if(null == $instance) {
			$instance = new CerACL("private");
		}
		
		return $instance;
	}
	
	function has_restriction($bitfield,$bitgroup=BITGROUP_1)
	{
		if($this->is_superuser==1) return false;
		return $this->check_bit($bitfield, $bitgroup);
	}

	function has_priv($bitfield,$bitgroup=BITGROUP_1)
	{
		if($this->is_superuser==1) return true;
		return $this->check_bit($bitfield, $bitgroup);
	}

	function check_bit($bitfield,$bitgroup=BITGROUP_1) {
		switch($bitgroup)
		{
			case BITGROUP_1:
				return cer_bitflag_is_set($bitfield,$this->acl1);
				break;
			case BITGROUP_2:
				return cer_bitflag_is_set($bitfield,$this->acl2);
				break;
			case BITGROUP_3:
				return cer_bitflag_is_set($bitfield,$this->acl3);
				break;
		}
	}

};
