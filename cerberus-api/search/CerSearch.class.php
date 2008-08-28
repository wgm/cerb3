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

class CerSearch {
	
	function CerSearch() {
	}
	
	function getList($uid) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		$searches = array();

		$sql = sprintf("SELECT search_id,title FROM saved_search WHERE created_by_uid = %d ORDER BY title",
			$uid
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$item = new CerSavedSearch();
				$item->id = intval($row['search_id']);
				$item->title = stripslashes($row['title']);
				$searches[$item->id] = $item;
			}
		}
		
		return $searches;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 * @return CerSavedSearch
	 */
	function loadSearch($id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */

		$sql = sprintf("SELECT search_id,title,params FROM saved_search WHERE search_id = %d",
			$id
		);
		$res = $db->query($sql);
		
		if($row = $db->grab_first_row($res)) {
			$item = new CerSavedSearch();
			$item->id = intval($row['search_id']);
			$item->title = stripslashes($row['title']);
			$item->params = unserialize(stripslashes($row['params']));
			return $item;
		}
		
		return FALSE;
	}
	
	function saveSearch($params,$id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
		if(!is_array($params))
			return FALSE;
		
		$params['search_id'] = $id;
			
		$sql = sprintf("UPDATE saved_search SET params=%s WHERE search_id = %d",
			$db->escape(serialize($params)),
			$id
		);
		$db->query($sql);
		
		return TRUE;
	}
	
	function createSearch($params,$title="New Search",$uid) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
		if(!is_array($params))
			return FALSE;

		$sql = sprintf("INSERT INTO saved_search (title,created_by_uid) ".
			"VALUES (%s,%d)",
			$db->escape($title),
			$uid
		);
		$db->query($sql);
		
		$id = $db->insert_id();
		
		if($id) {
			$params['search_id'] = $id;
			CerSearch::saveSearch($params,$id);
		}
		
		return $id;
	}
	
	function deleteSearch($id) {
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
		$sql = sprintf("DELETE FROM saved_search WHERE search_id = %d",
			$id
		);
		$db->query($sql);
		
		return TRUE;
	}
	
};

class CerSavedSearch {
	var $id = 0;
	var $title = "";
	var $params = array();
};