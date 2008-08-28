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
| File: config_users_edit.php
|
| Purpose: The configuration include for creating and editing user properties.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_AGENTS_CHANGE,BITGROUP_2) && !$acl->has_priv(PRIV_CFG_AGENTS_DELETE,BITGROUP_2)) {
	die();
}

if(!isset($uid))
	{ echo LANG_CONFIG_USER_EDIT_NOID; exit(); }	

require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");
$cerberus_format = new cer_formatting_obj;

/* @var $agent CerAgent */
$agents = new CerAgents();

if(0 == $uid || empty($uid)) {
	$agent = new CerAgent();
} else {
	$agent = $agents->getById($uid);
}


/*
 * [JAS]: If our logged in user isn't a superuser, they can't edit superusers.
 */
if($agent->getSuperuser() && $session->vars["login_handler"]->user_superuser == 0) {
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

?>

<SCRIPT LANGUAGE="JavaScript">
<!-- Begin

function validateUser() {
	if(document.useredit.user_name.value.length == 0) {
		alert("A valid real name is required");
		return false;
	}
	
	if(document.useredit.user_login.value.length == 0) {
		alert("A valid user login is required");
		return false;
	}
	
	if(!validatePwd()) {
		return false;
	}
	
	return true;
}

function validatePwd() {
var invalid = " "; // Invalid character is a space
var minLength = 6; // Minimum length
var pw1 = document.useredit.user_password_1.value;
var pw2 = document.useredit.user_password_2.value;
<?php
if($uid!=0) {
?>
// check for a value in both fields.
if (pw1 == '' && pw2 == '') {
return true;
}
<?php
}
else {
?>
// check for a value in both fields.
if (pw1 == '' || pw2 == '') {
alert('<?php echo  LANG_CONFIG_USER_EDIT_PWTWICE ?>');
return false;
}
<?php
}
?>
// check for minimum length
if (document.useredit.user_password_1.length < minLength) {
alert('Your password must be at least ' + minLength + ' characters long. Try again.');
return false;
}
// check for spaces
if (document.useredit.user_password_1.value.indexOf(invalid) > -1) {
alert("<?php echo  LANG_CONFIG_USER_EDIT_NOSPACES ?>");
return false;
}
else {
if (pw1 != pw2) {
alert ("<?php echo  LANG_CONFIG_USER_EDIT_PWTWICE_ERROR ?>");
return false;
}
else {
return true;
      }
   }
}
//  End -->
</script>

<form action="configuration.php" method="post" name="useredit" onsubmit="return validateUser();">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="puid" value="<?php echo  $uid ?>">
<input type="hidden" name="module" value="users">
<input type="hidden" name="form_submit" value="users_edit">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(!empty($user_error_msg)) echo "<span class=\"cer_configuration_updated\">" . $user_error_msg . "</span><br>"; ?>
<?php if(isset($form_submit) && empty($user_error_msg)) echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_USER_EDIT_SUCCESS . "</span><br>"; ?>

<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
<?php
if(0==$uid) {
?>
  <tr> 
    <td class="boxtitle_orange_glass"><?php echo  LANG_CONFIG_USER_EDIT_NEW ?></td>
  </tr>
<?php
}
else {
?>
  <tr> 
    <td class="boxtitle_orange_glass"><?php echo  LANG_CONFIG_USER_EDIT_EDIT ?>: <?php echo cer_dbc($agent->getRealName()); ?></td>
  </tr>
<?php
}
?>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_NAME ?>:</td>
            <td width="81%">
              <input type="text" name="user_name" size="24" maxlength="32" value="<?php echo @cer_dbc($agent->getRealName()); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_NAME_IE ?></span></td>
          </tr>
			 <tr bgcolor="#DDDDDD"> 
				<td width="19%" class="cer_maintable_heading" nowrap="nowrap">Display Name:</td>
				<td width="81%">
				  <input type="text" name="user_display_name" size="24" maxlength="32" value="<?php echo @cer_dbc($agent->getDisplayName()); ?>">
				  <span class="cer_footer_text"> (this is the name displayed for this user, such as 'Pam@Sales')</span>
				</td>
			 </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_EMAIL ?>:</td>
            <td width="81%">
              <input type="text" name="user_email" size="30" maxlength="128" value="<?php echo @cer_dbc($agent->getEmail()); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_EMAIL_IE ?></span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%"></td>
            <td width="81%">
              <span class="cer_footer_text"><b>NOTE: This address must be a valid e-mail account.  If this user plans to respond to 
              tickets from their e-mail client (i.e. Outlook Express) and NOT the GUI, this address must <i>exactly</i> match the account and reply-to  
              set up in that e-mail client.  Do not use queue addresses here or you will get duplicate tickets or mail loops.</b></span>
            </td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_LOGIN ?>:</td>
            <td width="81%">
              <input type="text" name="user_login" size="20" maxlength="32" value="<?php echo @cer_dbc($agent->getLogin()); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_LOGIN_IE ?></span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_PASS ?>:</td>
            <td width="81%">
              <input type="password" name="user_password_1" size="20" maxlength="64" value="">
						</td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_PASS_VER ?>:</td>
            <td width="81%">
              <input type="password" name="user_password_2" size="20" maxlength="64" value="">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_PASS_VER_IE ?></span></td>
          </tr>
          <?php if($session->vars["login_handler"]->user_superuser > 0) { ?>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_SUPERUSER ?>:</td>
            <td width="81%">
              <input type="checkbox" name="user_superuser" size="20" maxlength="32" value="1"<?php if($agent->getSuperuser()) {echo " checked";} ?>>
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_SUPERUSER_IE ?></span></td>
          </tr>
					<?php }
					else
					{	if($agent->getSuperuser()) echo "<input type=\"hidden\" name=\"user_superuser\" value=\"" . (($agent->getSuperuser()) ? 1 : 0) . "\">";	}
				 ?>
          <?php
			if(0!=$uid) {
			?>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_LASTLOGIN ?>:</td>
            <td width="81%"><span class="cer_footer_text"><?php $date = new cer_DateTime($agent->getLastLogin()); echo $date->getUserDate(); ?></span></td>
          </tr>
			<?php
				}
			?>
          
        </table>
    </td>
  </tr>
  
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
