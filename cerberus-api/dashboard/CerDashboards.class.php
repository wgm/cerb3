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

class CerDashboards {
	
	function getList($uid) {
		$db = cer_Database::getInstance();
		
		$dashboards = array();
		
		$sql = sprintf("SELECT `id`, `title`, `agent_id`, `hide_teams`, `hide_queues`, `reload_mins` FROM `dashboard` WHERE `agent_id` = %d",
			$uid
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$dashboard = new CerDashboard();
				$dashboard->id = intval($row['id']);
				$dashboard->title = stripslashes($row['title']);
				$dashboard->agent_id = intval($row['agent_id']);
				$dashboard->hide_teams = stripslashes($row['hide_teams']);
				$dashboard->hide_queues = stripslashes($row['hide_queues']);
				$dashboard->reload_mins = intval($row['reload_mins']);
				
				if(!empty($dashboard->hide_teams)) {
					$dashboard->hide_teams = unserialize($dashboard->hide_teams);
				}
				if(!empty($dashboard->hide_queues)) {
					$dashboard->hide_queues = unserialize($dashboard->hide_queues);
				}
				
				$dashboards[$dashboard->id] = $dashboard;
			}
		}
		
		$sql = sprintf("SELECT `view_id`, `view_name`, `view_params`, `dashboard_id` ".
			"FROM `ticket_views` ".
			"WHERE `view_created_by_id` = %d ".
			"ORDER BY `view_order` ASC",
				$uid
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$view_id = intval($row['view_id']);
				$view_title = stripslashes($row['view_name']);
				$view_params = unserialize(stripslashes($row['view_params']));
				$dashboard_id = intval($row['dashboard_id']);
				
				if(!isset($dashboards[$dashboard_id]))
					continue;
				
				$dashboards[$dashboard_id]->views[$view_id] = new CerDashboardView($view_id,$view_title,$view_params,$dashboard_id);
			}
		}
		
		return $dashboards;
	}
	
	function create($title,$uid) {
		$db = cer_Database::getInstance();
		
		$sql = sprintf("INSERT INTO `dashboard` (`title`,`agent_id`) ".
			"VALUES (%s,%d)",
				$db->escape($title),
				$uid
		);
		$db->query($sql);
		
		return $db->insert_id();
	}
	
	function save($did,$uid,$title,$hide_teams,$hide_queues,$reload) {
		$db = cer_Database::getInstance();

		$teams = array();
		$queues = array();
		
		// [JAS]: Make assoc array
		if(is_array($hide_teams)) {
			foreach($hide_teams as $id)
				$teams[$id] = $id;
		}
		// [JAS]: Make assoc array
		if(is_array($hide_queues)) {
			foreach($hide_queues as $id)
				$queues[$id] = $id;
		}
		
		$sql = sprintf("UPDATE `dashboard` SET `title` = %s, `hide_teams`=%s, `hide_queues`=%s, `reload_mins` = %d WHERE `agent_id` = %d AND `id` = %d",
				$db->escape($title),
				$db->escape(serialize($teams)),
				$db->escape(serialize($queues)),
				$reload,
				$uid,
				$did
		);
		$db->query($sql);
	}
	
	function createView($dashboard_id,$uid,$title="",$order=0) {
		$db = cer_Database::getInstance();
		
		$params = array();
		$params["search_status"] = 1;
		
		$sql = sprintf("INSERT INTO `ticket_views` (`view_name`,`view_columns`, `view_params`, `dashboard_id`, `view_created_by_id`, `view_order`) ".
			"VALUES (%s,%s,%s,%d,%d,%d)",
				$db->escape(empty($title) ? 'New View' : $title),
				$db->escape('ticket_id,ticket_subject,ticket_status,address_address,ticket_priority,queue_name,ticket_due'),
				$db->escape(serialize($params)),
				$dashboard_id,
				$uid,
				$order
		);
		$db->query($sql);
		
		return $db->insert_id();
	}
	
	function delete($id,$uid) {
		$db = cer_Database::getInstance();
		
		// dashboards
		$sql = sprintf("DELETE FROM `dashboard` WHERE `id` = %d AND `agent_id` = %d",
			$id,
			$uid
		);
		$db->query($sql);
		
		// views
		$sql = sprintf("DELETE FROM `ticket_views` WHERE `dashboard_id` = %d AND `view_created_by_id` = %d",
			$id,
			$uid
		);
		$db->query($sql);
	}
	
	function deleteView($vid,$uid) {
		$db = cer_Database::getInstance();
		
		$sql = sprintf("DELETE FROM `ticket_views` WHERE `view_id` = %d AND `view_created_by_id` = %d",
			$vid,
			$uid
		);
		$db->query($sql);
	}
	
};

class CerDashboard {
	var $id = 0;
	var $title = "";
	var $agent_id = 0;
	var $views = array();
	var $hide_teams = array();
	var $hide_queues = array();
	var $reload_mins = 0;
};

class CerDashboardView {
	var $id = 0;
	var $title = "";
	var $params = array();
	var $dashboard_id = 0;
	
	function CerDashboardView($id=0,$title="",$params=array(),$dashboard_id=0) {
		$this->id = $id;
		$this->title = $title;
		$this->params = $params;
		$this->dashboard_id = $dashboard_id;
	}
};