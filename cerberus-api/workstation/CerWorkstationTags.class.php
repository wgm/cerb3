<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

class CerWorkstationTags {
	
//	var $sets = array();
	var $tags = array();
//	var $tree = array();
	var $root = null;
	
	/**
	* @return CerWorkstationTags
	* @desc 
	*/
	function CerWorkstationTags() {
		$this->_loadTags();
	}

	function _getFnrTags($tag_string) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		//=====================
		// GENERIC PARSE TAS CODE [TODO]: Move to API
		//=====================
		
		$parse_tags = explode(',', strtolower($tag_string));
		$tags = array();
		
		// [JAS]: [TODO] Remove unset tags?
		
		// [JAS]: Index & trim spaces
		if(is_array($parse_tags))
		foreach($parse_tags as $t) {
			$tag_name = trim($t);
			if(!empty($tag_name)) {
				$tags[$tag_name] = 0;
			}
		}
		
		// [JAS]: Look up IDs
		$tag_csv = $db->escape(implode(",", array_keys($tags)));
		$tag_csv = str_replace(",","','",$tag_csv);
		$sql = sprintf("SELECT tag_id, LOWER(tag_name) as tag_name FROM workstation_tags ".
			"WHERE tag_name IN (%s)",
			$tag_csv
		);
		$res = $db->query($sql);
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$tag_name = stripslashes($row['tag_name']);
			if(isset($tags[$tag_name])) {
				$tags[$tag_name] = intval($row['tag_id']);
			}
		}
		
		// [JAS]: Insert new tags
		require_once(FILESYSTEM_PATH . "cerberus-api/acl/CerACL.class.php");
		$acl = CerACL::getInstance();
		if ( $acl->has_priv( PRIV_CFG_TAGS_CHANGE, BITFLAG_2 ) ) {
			if(is_array($tags))
			foreach($tags as $tname => $tid) {
				if(empty($tags[$tname])) {
					$sql = sprintf("INSERT INTO workstation_tags (tag_name) VALUES(%s)",
						$db->escape($tname)
					);
					$db->query($sql);
					$tags[$tname] = $db->insert_id();
				}
			}
		}
		
		return $tags;
	}
	
	function applyFnrTicketTags($tag_string,$ticket_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$tags = $this->_getFnrTags($tag_string);
		
		// [JAS]: Apply tag IDs to resource
		if(is_array($tags))
		foreach($tags as $t=>$id) {
			$sql = sprintf("INSERT IGNORE INTO workstation_tags_to_tickets (tag_id,ticket_id) VALUES (%d,%d)",
				$id,
				$ticket_id
			);
			$db->query($sql);
		}
	}
	
	function applyMailboxTags($tag_string,$mailbox_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$tags = $this->_getFnrTags($tag_string);
		
		// [JAS]: Apply tag IDs to resource
		if(is_array($tags))
		foreach($tags as $t=>$id) {
			$sql = sprintf("INSERT IGNORE INTO workstation_routing_tags (tag_id,queue_id) VALUES (%d,%d)",
				$id,
				$mailbox_id
			);
			$db->query($sql);
		}
	}
	
	function applyFnrResourceTags($tag_string,$resource_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$tags = $this->_getFnrTags($tag_string);
		
		// [JAS]: Apply tag IDs to resource
		if(is_array($tags))
		foreach($tags as $t=>$id) {
			$sql = sprintf("INSERT IGNORE INTO workstation_tags_to_kb (tag_id,kb_id) VALUES (%d,%d)",
				$id,
				$resource_id
			);
			$db->query($sql);
		}
	}
	
	function getRelatedArticlesByTicket($ticket_id,$limit=10) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("SELECT count(k1.tag_id) as hits, k1.tag_id, k1.kb_id ".
			"FROM `workstation_tags_to_tickets` t1 ".
			"INNER JOIN `workstation_tags_to_kb` k1 ON (t1.tag_id=k1.tag_id) ".
			"WHERE t1.ticket_id = %d ".
			"GROUP BY k1.kb_id ".
			"ORDER BY hits DESC ".
			"LIMIT 0,%d ",
				$ticket_id,
				$limit
		);
		$res = $db->query($sql);
		
		$ids = array();
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$ids[$row['kb_id']] = $row['hits'];
		}
		
		return $ids;
	}
	
	function getDescendentsList($tag_id) {
		if(isset($this->tags[$tag_id])) {
			foreach($this->tags[$tag_id]->children as $ch) {
				$ids[] = $ch->id;
				$add_ids = $this->getDescendentsList($ch->id);
				$ids = array_merge($add_ids,$ids);				
			}
		}
		
		return $ids;
	}
	
	function _loadTerms() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "SELECT `tag_id`, `term` FROM `workstation_tags_to_terms` ORDER BY `term`";
		$res = $db->query($sql);
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$tag_id = $row['tag_id'];
				$term = stripslashes($row['term']);
				if(isset($this->tags[$tag_id])) {
					$this->tags[$tag_id]->terms[] = $term;
				}
			}
		}
	}
	
	function getRelatedArticles($article_id,$limit=5,$only_in_cats=array()) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$cat_list = '';
		if(!empty($only_in_cats))
			$cat_list = implode(',', $only_in_cats);
		
		$sql = sprintf("SELECT count(t2.tag_id) as hits, t2.tag_id, t2.kb_id ".
			"FROM `workstation_tags_to_kb` t1 ".
			"INNER JOIN `workstation_tags` tag ON (t1.tag_id=tag.tag_id) ".
			"INNER JOIN `workstation_tags_to_kb` t2 ON (t1.tag_id=t2.tag_id) ".
			((!empty($only_in_cats)) ? "INNER JOIN `kb_to_category` c1 ON (c1.kb_id=t1.kb_id) " : " ").
			((!empty($only_in_cats)) ? "INNER JOIN `kb_to_category` c2 ON (c2.kb_id=t2.kb_id) " : " ").
			"WHERE t1.kb_id = %d ".
			"AND t2.kb_id != %d ".
			((!empty($only_in_cats)) ? sprintf("AND c1.kb_category_id IN (%s) ",$cat_list) : " ").
			((!empty($only_in_cats)) ? sprintf("AND c2.kb_category_id IN (%s) ",$cat_list) : " ").
			"GROUP BY t2.kb_id ".
			"ORDER BY hits DESC ".
			"LIMIT 0,%d ",
				$article_id,
				$article_id,
				$limit
		);
		$res = $db->query($sql);
		$ids = array();
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$ids[$row['kb_id']] = $row['hits'];
		}
		
		return $ids;
	}
	
	function _loadTags() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
//		$this->sets = array(); // clear
//		
//		$sql = "SELECT s.`id`, s.`name` FROM `workstation_tag_sets` s ORDER BY s.`name`";
//		$res = $db->query($sql);
//		
//		if(!$db->num_rows($res))
//			return;
//			
//		// [JAS]: Load sets
//		while($row = $db->fetch_row($res)) {
//			$set_id = intval($row['id']);
//			$set = new stdClass();
//			$set->id = $set_id;
//			$set->name = stripslashes($row['name']);
//			$set->tags = array();
//			$set->teams = array();
//			$this->sets[$set_id] = $set;
//		}
//		
//		$sql = "SELECT `set_id`, `team_id` FROM `workstation_tag_sets_to_teams`";
//		$res = $db->query($sql);
//		
//		if($db->num_rows($res)) {
//			while($row = $db->fetch_row($res)) {
//				$set_id = intval($row['set_id']);
//				$team_id = intval($row['team_id']);
//				if(isset($this->sets[$set_id])) {
//					$this->sets[$set_id]->teams[$team_id] = $team_id;
//				}
//			}
//		}
		
		$sql = "SELECT t.`tag_id`, t.`tag_name` FROM `workstation_tags` t ORDER BY t.`tag_name`";
		$res = $db->query($sql);

//		$this->root = new stdClass();
//		$this->root->children = array();
//		$this->root->sorted_children = array();
		
		if(!$db->num_rows($res))
			return;
		
		while($row = $db->fetch_row($res)) {
			$tag = new CerWorkstationTag();
			$tag->id = $row['tag_id'];
			$tag->name = stripslashes($row['tag_name']);
//			$tag->tag_set_id = $row['tag_set_id'];
//			$tag->parent =& $this->sets[$tag->tag_set_id];
			$this->tags[$row['tag_id']] = $tag;
//			$this->sets[$tag->tag_set_id]->tags[$tag->id] = &$this->tags[$row['tag_id']];
		}
		
		// [JAS]: Build our tree.
//		if(is_array($this->tags))
//		foreach($this->tags as $idxTag => $tag) {
//			$parent = $tag->tag_set_id;
//			if(!empty($parent) && isset($this->tags[$parent])) {
//				$this->tags[$parent]->children[] =& $this->tags[$idxTag];
//			} else {
//				$this->root->children[] =& $this->tags[$idxTag];
//			}
//		}
		
//		$this->_sortTags();
//		$this->_recurseTags(0);
		$this->_loadTerms();

		return true;
	}
	
	function deleteTag($tag_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("DELETE FROM `workstation_tags` WHERE `tag_id` = %d",
			$tag_id
		);
		$db->query($sql);
		
		$sql = sprintf("DELETE FROM `workstation_routing_tags` WHERE `tag_id` = %d",
			$tag_id
		);
		$db->query($sql);

		$sql = sprintf("DELETE FROM `workstation_tags_to_tickets` WHERE `tag_id` = %d",
			$tag_id
		);
		$db->query($sql);

		$sql = sprintf("DELETE FROM `workstation_tags_to_kb` WHERE `tag_id` = %d",
			$tag_id
		);
		$db->query($sql);
		
		$sql = sprintf("DELETE FROM `workstation_tags_to_terms` WHERE `tag_id` = %d",
			$tag_id
		);
		$db->query($sql);
		
		// [JAS]: Reload the tag cache
		$this->_loadTags();
	}
	
//	function deleteTagSet($set_id) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//		
//		$sql = sprintf("SELECT count(*) as hits FROM `workstation_tags` WHERE `tag_set_id` = %d",
//			$set_id
//		);
//		$res = $db->query($sql);
//		if(($row = $db->grab_first_row($res)) && intval($row['hits']) > 0)
//			return false;
//		
//		$sql = sprintf("DELETE FROM `workstation_tag_sets` WHERE `id` = %d",
//			$set_id
//		);
//		$db->query($sql);
//
//		// [JAS]: Reload the tag cache
//		$this->_loadTags();
//		
//		return true;
//	}
	
	function saveTag($tag_id, $tag_name, $terms=array()) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("UPDATE `workstation_tags` SET `tag_name` = %s WHERE `tag_id` = %d",
			$db->escape($tag_name),
//			$ws_tag_set_id,
			$tag_id
		);
		$db->query($sql);
		
		// [JAS]: Clear terms
		$sql = sprintf("DELETE FROM `workstation_tags_to_terms` WHERE `tag_id` = %d",
			$tag_id
		);
		$db->query($sql);
		
		if(!empty($terms)) {
			foreach($terms as $term) {
				if(empty($term)) continue;
				$sql = sprintf("INSERT INTO `workstation_tags_to_terms` (`tag_id`,`term`) ".
					"VALUES (%d,%s)",
						$tag_id,
						$db->escape($term)
				);
				$db->query($sql);
			}
		}
		
	}
	
//	function saveSet($id,$name,$teams) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//		
//		$sql = sprintf("UPDATE `workstation_tag_sets` SET `name` = %s WHERE `id` = %d",
//			$db->escape($name),
//			$id
//		);
//		$db->query($sql);
//
//		$sql = sprintf("DELETE FROM `workstation_tag_sets_to_teams` WHERE `set_id` = %d",
//			$id
//		);
//		$db->query($sql);
//		
//		if(!empty($teams)) {
//			if(is_array($teams))
//			foreach($teams as $team) {
//				$sql = sprintf("INSERT INTO `workstation_tag_sets_to_teams` (`set_id`,`team_id`) ".
//					"VALUES (%d,%d)",
//					$id,
//					$team
//				);
//				$db->query($sql);
//			}
//		}
//	}
	
//	function deleteSet($id) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//		
//		$sql = sprintf("DELETE FROM `workstation_tag_sets` WHERE `id` = %d",
//			$id
//		);
//		$db->query($sql);
//	}
	
//	function addTagSet($name) {
//		/* @var $db cer_Database */
//		$db = cer_Database::getInstance();
//
//		$sql = sprintf("INSERT INTO `workstation_tag_sets` (`name`) VALUES (%s)",
//			$db->escape($name)
//		);
//		$db->query($sql);
//		
//		$id = $db->insert_id();
//
//		return $id;
//	}
	
	function addTag($name,$set_id=0,$terms=array()) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("INSERT INTO `workstation_tags` (`tag_name`) VALUES (%s)",
			$db->escape($name)
//			$set_id
		);
		$db->query($sql);
		
		$id = $db->insert_id();
		
		if(!empty($terms)) {
			foreach($terms as $term) {
				$sql = sprintf("INSERT INTO `workstation_tags_to_terms` (`tag_id`,`term`) ".
					"VALUES (%d,%s)",
						$id,
						$db->escape($term)
				);
			}
		}
		
		return $id;
	}
	
	function _filterTagResults($matches,$sets) {
		if(!is_array($sets) || !is_array($matches))
			return array();
			
//		foreach($matches as $idx => $t) {
//			if(!in_array($sets[$t->tag_set_id],$sets))
//				unset($matches[$idx]);
//		}
		
		return $matches;
	}
	
	function getTags() {
		if(is_array($this->tags))
			return $this->tags;
		else 
			return array();
	}
	
//	function _recurseTags($tag_id,$level=0)
//	{
//		if(empty($tag_id)) {
//			$children = $this->root->sorted_children;
//		} else {
//			$children = $this->tags[$tag_id]->sorted_children;
//		}
//
//		if(is_array($children)) {
//			foreach($children as $idxChild => $child) {
//				
//				// [JAS]: Take advantage of the recursion to build a structured single-dimension array of all tags
//				$this->tree["" . $child->id] = 
//						array($level,&$children[$idxChild]);
//				
//				$new_level = $level + 1;
//				$this->_recurseTags($child->id,$new_level);
//			}
//		}
//	}
	
	function _sortTags() {
		$this->root->sorted_children = 
			cer_PointerSort::pointerSortCollection($this->root->children,"name");
		
		foreach($this->tags as $idx => $t) {
			$this->tags[$idx]->sorted_children = 
				cer_PointerSort::pointerSortCollection($this->tags[$idx]->children,"name");
		}
	}
	
};

class CerWorkstationTag {
	var $id = 0;
	var $name = "";
	var $num_articles = 0; // hits with child roll-up
	var $hits = 0; // own hits
//	var $sorted_children = array();
//	var $children = array();
	var $terms = array();
};