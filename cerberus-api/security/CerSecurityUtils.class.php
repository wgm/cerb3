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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class CerSecurityUtils {

	/**
	* @return array
	* @param array $ary
	* @desc Forces all array elements to be integers
	*/
	function integerArray(&$ary) {
		if(!is_array($ary))
			return array();
			
		foreach($ary as $idx => $id) {
			if(is_nan($id))
				unset($ary[$idx]);
		}
	}

	
	/**
	* @return string
	* @param string $str
	* @desc Adds MySQL backticks to a string
	*/
	function sqlBacktick($str) {
		return '`' . $str . '`';
	}
	
}