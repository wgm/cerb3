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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_OB_CALLBACK",true); // [JAS]: Leave this true

require_once("site.config.php");

$acl = CerACL::getInstance();
if(DEMO_MODE || !$acl->has_priv(PRIV_CFG_PARSER_FAILED,BITGROUP_2)) {
	die("Permission denied.");
}

@$id = $_REQUEST["id"];

$sql = sprintf("SELECT `id`,`message_source_filename` ".
	"FROM `parser_fail_headers` ".
	"WHERE `id` = %d",
		$id
);
$fail_res = $cerberus_db->query($sql);

if(!$cerberus_db->num_rows($fail_res)) {
	die("Invalid Message ID.");
}

$fail_row = $cerberus_db->fetch_row($fail_res);
$failFilename = stripslashes($fail_row["message_source_filename"]);
?>

Message source from ./tempdir/<?php echo $failFilename ?>:<br>
<TEXTAREA rows="25" cols="100" style="width:98%;height:98%;">
<?php
@$fp = fopen(FILESYSTEM_PATH . "tempdir/" . $failFilename, "rb");
if($fp) {
	while(!feof($fp)) {
		echo fgets($fp, 50000);
	}
}
@fclose($fp);
?> 
</TEXTAREA>
