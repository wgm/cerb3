<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC 
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

require_once(FILESYSTEM_PATH . "cerberus-api/pop3/CerPop3Accounts.class.php");

/* [JAS]: Variable typing */
/* @var $acct CerPop3Account */

if(is_nan($pgid)) $pgid=0;

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_POP3_CHANGE,BITGROUP_2)) {
	die("Permission denied.");
}

$pop3accts = new CerPop3Accounts();
$acct = $pop3accts->getById($pgid);
if(null == $acct) $acct = new CerPop3Account();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pgid" value="<?php echo $pgid; ?>">
<input type="hidden" name="module" value="pop3">
<input type="hidden" name="form_submit" value="pop3_edit">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF">
  <tr> 
   <td class="boxtitle_orange_glass">Configure POP3 Account: <?php echo @htmlspecialchars($acct->getName()) ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap><?php echo LANG_WORD_ENABLED; ?>:</td>
            <td width="90%" bgcolor="#EEEEEE" class="cer_maintable_text">
            	<label><input type="radio" name="pop3_disabled" value="0" <?php echo (!$acct->getDisabled() ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_TRUE; ?></label> 
            	<label><input type="radio" name="pop3_disabled" value="1" <?php echo ($acct->getDisabled() ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_FALSE; ?></label>
            </td>
          </tr>
          
          <tr>
          	<td colspan="2" bgcolor="#FFFFFF"><img alt="" src="includes/images/spacer.gif" height="1" width="1"></td>
          </tr>
          
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Nickname:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	<input type="text" name="pop3_name" maxlength="64" size="45" value="<?php echo @htmlspecialchars($acct->getName()); ?>">
            </td>
          </tr>
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Host:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	<input type="text" name="pop3_host" maxlength="64" size="45" value="<?php echo @htmlspecialchars($acct->getHost()); ?>">
            </td>
          </tr>
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Port:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	<input type="text" name="pop3_port" maxlength="6" size="4" value="<?php echo @htmlspecialchars(($acct->getPort()) ? $acct->getPort() : 110); ?>">
            </td>
          </tr>
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Login:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	<input type="text" name="pop3_login" maxlength="32" size="45" value="<?php echo @htmlspecialchars($acct->getLogin()); ?>">
            </td>
          </tr>
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Password:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	<input type="password" name="pop3_pass" maxlength="32" size="45" value="<?php echo (DEMO_MODE) ? '' : @htmlspecialchars($acct->getPass()); ?>">
            </td>
          </tr>

          <tr>
          	<td colspan="2" bgcolor="#FFFFFF"><img alt="" src="includes/images/spacer.gif" height="1" width="1"></td>
          </tr>

          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Max. Messages:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	<input type="text" name="pop3_max_messages" maxlength="3" size="3" value="<?php echo @htmlspecialchars(($acct->getMaxMessages()) ? $acct->getMaxMessages() : 10); ?>">
            	<span class="cer_footer_text">(# of msgs to download per run, too many can timeout or burden server. Timeout: <?php echo (ini_get('max_execution_time') ? ini_get('max_execution_time') : "unlimited") ; ?> secs)</span>
            </td>
          </tr>

          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Max. Msg. Size:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	<input type="text" name="pop3_max_size" maxlength="9" size="10" value="<?php echo @htmlspecialchars(($acct->getMaxSize()) ? $acct->getMaxSize() : 2000000); ?>">
            	<span class="cer_footer_text">(max. size per message in bytes -- raising this too high will overrun your PHP memory limit)</span>
            </td>
          </tr>

          <tr>
          	<td colspan="2" bgcolor="#FFFFFF"><img alt="" src="includes/images/spacer.gif" height="1" width="1"></td>
          </tr>
          
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Test Mode:</td>
            <td width="90%" bgcolor="#EEEEEE" class="cer_maintable_text">
            	<label><input type="radio" name="pop3_delete" value="0" <?php echo ((!$acct->getDelete()) ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_TRUE; ?></label> 
            	<label><input type="radio" name="pop3_delete" value="1" <?php echo (($acct->getDelete()) ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_FALSE; ?></label><br>
            	<span class="cer_footer_text">(Test mode won't delete messages from your mailbox.  Don't set this 'true' in live environments.)</span>
            </td>
          </tr>
          
        </table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
			<input type="button" class="cer_button_face" value="&lt;&lt; Back (Discard)" onclick="javascript:document.location='<?php echo cer_href("configuration.php?module=pop3"); ?>';">
			<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>

<?php
if(!empty($pgid) && !empty($acct)) {

if(isset($form_submit) && $form_submit == "pop3_test") {
	include_once(FILESYSTEM_PATH . "cerberus-api/pop3/cer_Pop3.class.php");
	$client = new cer_Pop3Client($acct->getHost(), $acct->getPort(), $acct->getLogin(), $acct->getPass(), 30);
	if($client->connect()
		&& $client->pop3_user()
		&& $client->pop3_pass()
		&& $client->pop3_stat()
		) {
			$client->pop3_stat();
			$valid = true;
		} else {
			$valid = false;
		}
	@$client->disconnect();
	
	if($valid) {
		echo sprintf("<span class='cer_configuration_success'>SUCCESS! %d message(s) in mailbox.</span>",
			count($client->messageListArray)
		);
	} else {
		echo sprintf("<span class='cer_configuration_updated'>FAILED! Check your connection settings.</span>");
	}
}

if($valid !== FALSE) {
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pgid" value="<?php echo $pgid; ?>">
<input type="hidden" name="module" value="pop3">
<input type="hidden" name="form_submit" value="pop3_test">

<input type="submit" value="Test POP3 Connection">
</form>

<?php
	}
}
?>