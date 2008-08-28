<?php
if(!function_exists('file_get_contents')) {
   function file_get_contents($filename) {
      return implode('', file($filename));
   }
}

function get_var($varname = '', $fail = FALSE, $default = '') {
   if($fail && !isset($_REQUEST[$varname])) {
      print("Error: Variable wasn't passed and was required (\$" . $varname . ")");
      exit();
   }
   if(get_magic_quotes_gpc()) {
      return stripslashes(isset($_REQUEST[$varname]) ? $_REQUEST[$varname] : $default);
   }
   else {
      return isset($_REQUEST[$varname]) ? $_REQUEST[$varname] : $default;
   }
}