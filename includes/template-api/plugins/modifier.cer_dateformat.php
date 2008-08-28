<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     cer_dateformat
 * Purpose:  parse + format a date
 * -------------------------------------------------------------
 */
function smarty_modifier_cer_dateformat($string, $format="%Y-%m-%d")
{
	$date = "";
	
	if(is_numeric($string)) {
		$mktime = $string;
	} else {
		$mktime = strtotime($string);
	}
	
	if($mktime > 0)
		$date = strftime($format, $mktime); 
	
	return $date;
}

/* vim: set expandtab: */

?>
