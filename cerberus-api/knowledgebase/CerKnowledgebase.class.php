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
|
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once (FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

define("FNR_TYPE_ARTICLE","1");

/**
 * Knowledgebase Handler API
 *
 */
class CerKnowledgebase {
	var $flat_categories = array();
	var $root = null;
	var $_only_public = 0;
	
	function CerKnowledgebase($only_public=0) {
		$this->root = new CerKnowledgebaseCategory(0,"Top",-1);
		$this->_only_public = $only_public;
		$this->_loadTree();
	}
	
	function _loadTree() {
		$db = cer_Database::getInstance();
		
		$sql = sprintf("SELECT kc.id, kc.name, kc.parent_id ".
			"FROM kb_category kc ".
			"ORDER BY kc.name ASC"
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$category = new CerKnowledgebaseCategory();
			$category->id = intval($row['id']);
			$category->name = stripslashes($row['name']);
			$category->parent_id = intval($row['parent_id']);
			$this->flat_categories[$category->id] = $category;
		}
		
		// [JAS]: Attach parent/child pointers to the flat tree
			if(is_array($this->flat_categories))
			foreach($this->flat_categories as $idx => $cat) { /* @var $cat CerKnowledgebaseCategory */
				if(0 != $cat->parent_id) {
					@$this->flat_categories[$idx]->parent_ptr =& $this->flat_categories[$cat->parent_id]; // parent
					@$this->flat_categories[$cat->parent_id]->children[$idx] =& $this->flat_categories[$idx]; // child
			} else {
				@$this->flat_categories[$idx]->parent_ptr =& $this->root; // parent
				@$this->root->children[$idx] =& $this->flat_categories[$idx]; // child
			}
		}
		
		// [JAS]: [TODO] Need to porter sort children
		
		// [JAS]: Need to total resource counts (flexibly, so we can reuse for Support Center)
		// [TODO] "$only_public"
		$this->_buildCategoryIndex();
	}
	
	function deleteCategories($ids) {
		$db = cer_Database::getInstance();
		if(!is_array($ids)) $ids = array($ids);
		
		// [JAS]: [TODO] Move to API
		$sql = sprintf("DELETE FROM kb_category WHERE id IN (%s)",
			implode(',', $ids)
		);
		$db->query($sql);
		
		$sql = sprintf("DELETE FROM kb_to_category WHERE kb_category_id IN (%s)",
			implode(',', $ids)
		);
		$db->query($sql);
		
		return TRUE;
	}
	
	function _getCategoryCounts() {
		$db = cer_Database::getInstance();
		$cat_counts = array();
		
		// [JAS]: [TODO] Need to limit to only public articles here (if $_only_public)
		$sql = sprintf("SELECT count(kbtc.kb_id) as hits, kbc.id ".
			"FROM kb_category kbc ".
			"INNER JOIN kb_to_category kbtc ON (kbc.id=kbtc.kb_category_id) ".
			"INNER JOIN kb ON (kb.id=kbtc.kb_id) ".
			"WHERE 1 ".
			(($this->_only_public) ? "AND kb.public = 1 " : ""). 
			"GROUP BY kbc.id"
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$cat_counts[intval($row['id'])] = intval($row['hits']);
		}
		
		return $cat_counts;
	}
	
	function _buildCategoryIndex() {
		$cat_counts = $this->_getCategoryCounts();
		
		if(null != $this->flat_categories && is_array($this->flat_categories))
		foreach($this->flat_categories as $idx => $cat) {
			$ancestors = $cat->getAncestors();
			
			// [JAS]: Indentation/depth
			$this->flat_categories[$idx]->level = max(count($ancestors) - 1,0);
			
			// [JAS]: Resource counts
			if(is_array($ancestors))
			foreach($ancestors as $ancestor) {
				$this->flat_categories[$ancestor]->hits += $cat_counts[$idx];
			}
		}
		
	}
	
	function _recurseCategories($cat=null) { /* @var $cat CerKnowledgebaseCategory */
		static $cids = array();
		if(null == $cat) {
			$cids = array();
			$cat = $this->root;
		}
		$cids[$cat->id] = $cat->id;
		
		$ancestors = $cat->getAncestors();
		$cat->level = max(count($ancestors) - 1,0);
	
		if(is_array($cat->children))
		foreach($cat->children as $c)
			$this->_recurseCategories($c);	
		
		return $cids;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return CerKnowledgebaseCategory[]
	 */
	function getCategories() {
		return $this->flat_categories;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return CerKnowledgebaseCategory
	 */
	function getRoot() {
		return $this->root;
	}
};

class CerKnowledgebaseCategory {
	var $id = null;
	var $name = null;
	var $parent_id = null;
	var $parent_ptr = null; // pointer
	var $children = array(); // pointer array
	var $level = 0; // depth in tree
	var $hits = 0; // number of resources
	
	function CerKnowledgebaseCategory($id=null,$name=null,$parent=null) {
		$this->id = $id;
		$this->name = $name;
		$this->parent_id = $parent;
	}
	
	function getChildCount() {
		return count($this->children);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param int $limit
	 * @return CerKnowledgebaseArticle[]
	 */
	function getMostViewedArticles($limit=5,$only_public=0) {
		$db = cer_Database::getInstance();
		
		$descendents = $this->getDescendents();
		
		$sql = sprintf("SELECT kb.id, kb.title, kb.public, kb.views ".
			"FROM kb ".
			"INNER JOIN kb_to_category kbc ON (kbc.kb_id=kb.id) ".
			"WHERE kbc.kb_category_id IN (%s) ".
			(($only_public) ? "AND kb.public = 1 " : "").
			"GROUP BY kb.id ".
			"ORDER BY kb.views DESC ".
			"LIMIT 0,%d",
				implode(",",$descendents), // [JAS]: [TODO] Cascade
				$limit
		);
		$res = $db->query($sql);
		
		$resources = array();
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$resource = new CerKnowledgebaseArticle(
				intval($row['id']),
				stripslashes($row['title']),
				intval($row['public']),
				intval($row['views'])
			);
			$resources[$resource->id] = $resource;
		}
		
		$this->_addRatingsToResources($resources);
		
		return $resources;
	}

	// [JAS]: [TODO] Needs paging
	function getResources($limit=25,$only_public=0) {
		$db = cer_Database::getInstance();
		$resources = array();

		// [JAS]: Articles
		$sql = sprintf("SELECT kb.id, kb.title, kb.public, kb.views ".
			"FROM kb ".
			"%s ".
			(($only_public) ? "AND kb.public = 1 " : "").
			"GROUP BY kb.id ".
			"ORDER BY kb.views DESC ".
			"LIMIT 0,%d",
				((0 == $this->id)
					? sprintf("LEFT JOIN kb_to_category kbc ON (kbc.kb_id=kb.id) WHERE kbc.kb_id IS NULL ") // [JAS]: [TODO] Cascade
					: sprintf("INNER JOIN kb_to_category kbc ON (kbc.kb_id=kb.id) WHERE kbc.kb_category_id = %d ",$this->id)
				),
				$limit
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$resource = new CerKnowledgebaseArticle(
				intval($row['id']),
				stripslashes($row['title']),
				intval($row['public']),
				intval($row['views'])
			);
			$resources[$resource->id] = $resource;
		}

		$this->_addRatingsToResources($resources);
		
		// [JAS]: [TODO] Need external resources here
		
		return $resources;
	}

	function _addRatingsToResources(&$resources) {
		$db = cer_Database::getInstance();
		
		// [JAS]: Votes
		$sql = sprintf("SELECT count(*) as votes, avg(rating) as avg_rating, kb_article_id as kb_id ".
			"FROM `kb_ratings` ".
			"WHERE kb_article_id IN (%s) ".
			"GROUP BY kb_article_id",
				implode(',', array_keys($resources))
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$kb_id = $row['kb_id'];
			if(isset($resources[$kb_id])) {
				$resources[$kb_id]->votes = intval($row['votes']);
				$resources[$kb_id]->rating = floatval($row['avg_rating']);
			}
		}
	}
	
	function getDescendents($cat=null) {
		static $cids = array();
		if(null == $cat) {
			$cids = array();
			$cat = $this;
		}
		$cids[$cat->id] = $cat->id;
	
		if(is_array($cat->children))
		foreach($cat->children as $c)
			$this->getDescendents($c);	
		
		return $cids;
	}
	
	function getAncestors($reverse=0) {
		$cids = array();
		$ptr = $this;
		
		while(null != $ptr) {
			$cids[$ptr->id] = $ptr->id;
			$ptr =& $ptr->parent_ptr;
		}
		
		if($reverse)
			return array_reverse($cids,true);
		else
			return $cids;
	}
	
}

class CerKnowledgebaseResource {
	var $id = null;
	var $name = null;
	var $type = null; // CONSTANTS ENUM: article, ...
	var $categories = array(); // member of categories
	var $permalinks = array();
}

// [JAS]: [TODO] Add categories
class CerKnowledgebaseArticle extends CerKnowledgebaseResource {
	var $views = 0;
	var $public = 0;
	var $votes = 0;
	var $rating = 0.0;
	
	function CerKnowledgebaseArticle($id=null,$title=null,$public=0,$views=0) {
		$this->id = intval($id);
		$this->name = $title;
		$this->public = intval($public);
		$this->views = intval($views);
	}
	
	function reload() {
		if(empty($this->id)) return;
		$db = cer_Database::getInstance();

		// [JAS]: Articles
		$sql = sprintf("SELECT kb.id, kb.title, kb.public, kb.views ".
			"FROM kb ".
			"WHERE kb.id = %d ",
			$this->id
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			$row = $db->fetch_row($res);
			$this->name = stripslashes($row['title']);
			$this->public = intval($row['public']);
			$this->views = intval($row['views']);
		}
	}
	
	function loadCategories() {
		if(empty($this->id)) return;
		$db = cer_Database::getInstance();
		// [JAS]: [TODO] Move to API
//		$resource = new CerKnowledgebaseResource();
//		$resource->id = $id;
		$sql = sprintf("SELECT kbtc.kb_category_id FROM `kb_to_category` kbtc WHERE kbtc.kb_id = %d",
			$this->id
		);
		$res = $db->query($sql);
		
		// [JAS]: [TODO] Move to API
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$cat_id = intval($row['kb_category_id']);
			$this->categories[$cat_id] = $cat_id;
		}
		
		return true;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return array
	 */
	function getTags() {
		$db = cer_Database::getInstance();
		$tags = array();
		
		$sql = sprintf("SELECT t.tag_id, LOWER(t.tag_name) as tag_name ".
			"FROM workstation_tags_to_kb wtkb ".
			"INNER JOIN workstation_tags t ON (wtkb.tag_id=t.tag_id) ".
			"WHERE wtkb.kb_id = %d ".
			"ORDER BY t.tag_name ASC ",
			$this->id
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$tags[intval($row['tag_id'])] = stripslashes($row['tag_name']);
		}
		
		return $tags;
	}
	
	
	function getPermalinks() {
		$db = cer_Database::getInstance();
		$kb = new CerKnowledgebase(1);
		$links = array();
		$ids = array();
		
		// categories and their ancestors
		if(is_array($this->categories)) {
			foreach($this->categories as $ci => $c) {
				$ids[$ci] = $ci;
				$ans = $kb->flat_categories[$ci]->getAncestors();
				if(is_array($ans))
				foreach($ans as $ai => $a)
					$ids[$ai] = $ai;
			}
		}
		
		// [JAS]: Links
		$sql = sprintf("SELECT pg.profile_id, pg.profile_name, pg.pub_url FROM public_gui_profiles pg ".
			"WHERE pg.pub_mod_kb_root IN (%s) ",
				implode(',', array_keys($ids))
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		while($row = $db->fetch_row($res)) {
			$links[intval($row['profile_id'])] = array(stripslashes($row['profile_name']),stripslashes($row['pub_url']));
		}
		
		return $links;
	}
	
}