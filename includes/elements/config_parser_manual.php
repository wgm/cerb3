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

// [JAS]: Verify that the connecting user has access to modify configuration
$acl = CerACL::getInstance();
if(DEMO_MODE || !$acl->has_priv(PRIV_CFG_PARSER_IMPORT,BITGROUP_2)) {
	die("Permission denied.");
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php?module=parser_manual" name="parserFailForm" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="parser_manual">

<?php if(isset($form_submit) && !empty($refailed)) { ?>
	<span class="cer_configuration_updated"><?php echo $refailed; ?></span><br>
<?php } ?>

<?php if(isset($form_submit) && empty($refailed)) { ?>
	<span class="cer_configuration_success">SUCCESS! E-mail parsed and imported into helpdesk.</span><br>
<?php } ?>

<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr> 
    <td class="boxtitle_orange_glass">Manual E-mail Parser</td>
  </tr>

  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td align="left" bgcolor="#EEEEEE" class="cer_maintable_text"> 
    Paste the full message source of an e-mail message to manually import it into the helpdesk.
    </td>
  </tr>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td align="left" bgcolor="#BBBBBB" class="cer_maintable_text"> 
    
		<TEXTAREA rows="10" cols="75" name="raw_email"><?php
		
		 if(!empty($raw_email) && !empty($refailed)) {
		 	echo $raw_email; 
		 }
		 
		 ?></TEXTAREA><BR>
		<div align="right"><input type="submit" value="Parse"></div>
		
    </td>
  </tr>

 </table>
</form>
 