<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     str_repeat
 * Purpose:  repeat a string
 * -------------------------------------------------------------
 */
function smarty_function_str_repeat($params, &$this)
{
    extract($params);

//    if (!isset($var)) {
//        $this->trigger_error("eval: missing 'var' parameter");
//        return;
//    }
//	if($var == '') {
//		return;
//	}
//
//	$this->_compile_template("evaluated template", $var, $source);
//	

//    ob_start();
    
    $contents = str_repeat($string,$mult);
    
//	$contents = ob_get_contents();
//    ob_end_clean();
//
//    if (!empty($assign)) {
//    	$this->assign($assign, $contents);
//    } else {
		return $contents;
//    }
}

/* vim: set expandtab: */

?>
