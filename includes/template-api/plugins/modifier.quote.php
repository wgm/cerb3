<?php
function smarty_modifier_quote($string)
{
	$output = "";
	
	$string = str_replace("\r","\n",$string);
	$string = str_replace("\n\n","\n",$string);
	$lines = split("\n",$string);
	
	foreach($lines as $line) {
		$output .= "> " . $line . "\r\n";
	}
	
	return $output;
}