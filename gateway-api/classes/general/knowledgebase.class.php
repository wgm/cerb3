<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");

class general_knowledgebase
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_knowledgebase() {
      $this->db =& database_loader::get_instance();
   }

   function search($string) {
   	$kb = new CerWorkstationKb();
   	$results = $kb->getArticlesByKeywords($string);
   	
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
		$articles =& $data->add_child("articles", xml_object::create("articles", NULL));
   	
      foreach($results as $article) { /* @var $article CerWorkstationKbArticle */
			$article_item =& $articles->add_child("article", xml_object::create("article", NULL, array("id"=>$article->article_id)));
	      $article_item->add_child("title", xml_object::create("title", $article->article_title));
         $article_item->add_child("rating", xml_object::create("rating", sprintf("%01.1f",$article->article_rating)));
         $article_item->add_child("votes", xml_object::create("votes", $article->article_votes));
         $article_item->add_child("views", xml_object::create("views", $article->article_views));
         $article_item->add_child("relevance", xml_object::create("relevance", $article->article_relevance));
			$article_tags =& $article_item->add_child("tags", xml_object::create("tags", NULL));
			$this->_buildTagXml($article_tags, $article);
      }
      
      return TRUE;
   }
   
   function search_by_tags($tag_ids) {
   	if(!is_array($tag_ids))
   		return TRUE;
   	
   	$kb = new CerWorkstationKb();
   	$results = $kb->getArticlesByTags($tag_ids);
   	 
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
		$articles =& $data->add_child("articles", xml_object::create("articles", NULL));
   	
      foreach($results as $article) { /* @var $article CerWorkstationKbArticle */
			$article_item =& $articles->add_child("article", xml_object::create("article", NULL, array("id"=>$article->article_id)));
	      $article_item->add_child("title", xml_object::create("title", $article->article_title));
         $article_item->add_child("rating", xml_object::create("rating", sprintf("%01.1f",$article->article_rating)));
         $article_item->add_child("votes", xml_object::create("votes", $article->article_votes));
         $article_item->add_child("views", xml_object::create("views", $article->article_views));
         $article_item->add_child("relevance", xml_object::create("relevance", $article->article_relevance));
			$article_tags =& $article_item->add_child("tags", xml_object::create("tags", NULL));
			$this->_buildTagXml($article_tags, $article);
      }
   	
   	return TRUE;
   }
   
   function get_article_by_id($article_id) {
   	$kb = new CerWorkstationKb();
	
		$article_info = $kb->getArticleById($article_id); /* @var $article_info CerWorkstationKbArticle */

      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

      if(!empty($article_info)) {
      	$article =& $data->add_child("article",xml_object::create("article", NULL, array("id"=>$article_info->article_id)));
         $article->add_child("title", xml_object::create("title", $article_info->article_title));
         $article->add_child("rating", xml_object::create("rating", sprintf("%01.1f",$article_info->article_rating)));
         $article->add_child("votes", xml_object::create("votes", $article_info->article_votes));
         $article->add_child("views", xml_object::create("views", $article_info->article_views));
         $article->add_child("relevance", xml_object::create("relevance", $article_info->article_relevance));
         $article->add_child("content", xml_object::create("content", $article_info->article_content));
			$article_tags =& $article->add_child("tags", xml_object::create("tags", NULL));
			$this->_buildTagXml($article_tags, $article_info);
      }
   	
   	return TRUE;
   }
   
   function _buildTagXml(&$tags, &$article) {
   	/* @var $article CerWorkstationKbArticle */

   	if(is_array($article->tags))
   	foreach($article->tags as $tag_id => $tag) {
   		$tag_elm =& $tags->add_child("tag", xml_object::create("tag", NULL, array("id"=>$tag_id)));
		$tag_elm->add_child("name", xml_object::create("name", $tag->name));
		$tag_elm->add_child("set", xml_object::create("set", $tag->setName, array("id"=>$tag->setId)));
   	}
   }
   
   function add_tags($article_id, $tags) {
   	$kb = new CerWorkstationKb();
   	return $kb->addTagsToArticleId($tags, $article_id);
   }
   
   function remove_tag($article_id, $tag_id) {
   	$kb = new CerWorkstationKb();
   	return $kb->removeTagsFromArticleId(array($tag_id), $article_id);
   }
   
}