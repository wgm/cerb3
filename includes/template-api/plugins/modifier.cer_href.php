<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     cerhref
 * Purpose:  rewrite URL
 * -------------------------------------------------------------
 */
function smarty_modifier_cer_href($string, $anchor="")
{
	return cer_href($string, $anchor);
}

/* vim: set expandtab: */

?>
