<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKbTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting members detailed info
 *
 */
class search_articles_by_ticket_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
   var $xml;
   
   /**
    * Class constructor
    *
    * @param object $xml
    * @return search_articles_handler
    */
   function search_articles_by_ticket_handler(&$xml) {
      $this->xml =& $xml;
   }
   
   /**
    * main() function for this class. 
    *
    */
	function process() {
		$users_obj =& new general_users();
		if($users_obj->check_login() === FALSE) {
			xml_output::error(0, 'Not logged in. Please login before proceeding!');
		}
      
//		$kb = new general_knowledgebase();   

		$ticket_elm =& $this->xml->get_child('ticket',0);
		$ticket_id = $ticket_elm->get_attribute("id", false);

		$wsTags = new CerWorkstationTags();
		$ids = $wsTags->getRelatedArticlesByTicket($ticket_id);
		
		$article_ids = array_keys($ids);
		
		$kb = new CerWorkstationKb();
		$articles = $kb->getArticlesByIds($article_ids,false,true);

		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$articles_elm =& $data->add_child("articles", xml_object::create("articles", NULL));		
		
		if($articles === FALSE) {
			xml_output::error(0, 'Failed to search knowledgebase articles');
		}
		else {
			if(is_array($articles))
			foreach($articles as $article) { /* @var $article CerWorkstationKbArticle */
				$article_item =& $articles_elm->add_child("article", xml_object::create("article", NULL, array("id"=>$article->article_id)));
				$article_item->add_child("title", xml_object::create("title", $article->article_title));
				$article_item->add_child("rating", xml_object::create("rating", sprintf("%01.1f",$article->article_rating)));
				$article_item->add_child("votes", xml_object::create("votes", $article->article_votes));
				$article_item->add_child("views", xml_object::create("views", $article->article_views));
				$article_item->add_child("relevance", xml_object::create("relevance", $article->article_relevance));
				$article_tags =& $article_item->add_child("tags", xml_object::create("tags", NULL));
				
				if(is_array($article->tags))
					foreach($article->tags as $tag_id => $tag) {
						$tag_elm =& $article_tags->add_child("tag", xml_object::create("tag", NULL, array("id"=>$tag_id)));
						$tag_elm->add_child("name", xml_object::create("name", $tag->name));
						$tag_elm->add_child("set", xml_object::create("set", $tag->setName, array("id"=>$tag->setId)));
					}				
			}			
			
			xml_output::success();
		}
	}        
}