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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class CerMenuItem {
	var $image;
	var $text;
	var $subtext;
	var $link;
	
	function CerMenuItem($i,$t,$s,$l) {
		$this->image = $i;
		$this->text = $t;
		$this->subtext = $s;
		$this->link = $l;
	}
};
