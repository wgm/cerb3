<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: header.php
|
| Purpose: The global page header.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");

$cerberus_disp = new cer_display_obj;
?>

<script>
sid = "<?php echo "sid=" . $session->session_id; ?>";
show_sid = <?php echo ((@$cfg->settings["track_sid_url"]) ? "true" : "false"); ?>;

<?php if(isset($new_pm) && $new_pm !== false) { ?>
var new_pm = <?php echo $new_pm; ?>;
<?php } else { ?>
var new_pm = false;
<?php } ?>

function toggleDiv(div) {
	var eDiv = document.getElementById(div);
	
	if(null != eDiv) {
		if(eDiv.style.display == "block") {
			eDiv.style.display = "none";
		} else {
			eDiv.style.display = "block";
		}
	}
}

function cer_upload_win()
{
	url = "upload.php";
	if(show_sid) {
		window.open( url + "?" + sid,"uploadWin","width=600,height=300,status=yes");
	}
	else {
		window.open( url,"uploadWin","width=600,height=300,status=yes");
	}
}

function getCacheKiller() {
	var date = new Date();
	return date.getTime();
}

function formatURL(url)
{
	if(show_sid) { url = url + "&" + sid; }
	return(url);
}

function printTicket(url)
{
	window.open(url,'print_ticket','width=700,height=500,scrollbars=yes');
}

function pmCheck()
{
	if(new_pm != false)
	{
		url = "message_popup.php?mid=" + new_pm;
		window.open(formatURL(url),"pm_notify_wdw","width=200,height=175");
	}
}

function load_init()
{
	pmCheck();
}

function jumpNav(link)
{
	if(link != null) {
		link_id = link;
	}
	else
		link_id = parseInt(document.headerForm.jump_nav.value);
		
	switch(link_id)
	{
		case 0:
		url = "my_cerberus.php?mode=dashboard";
		break;
		case 1:
		url = "my_cerberus.php?mode=tasks";
		break;
		case 2:
		url = "my_cerberus.php?mode=messages";
		break;
		case 3:
		url = "my_cerberus.php?mode=preferences";
		break;
		case 4:
		url = "my_cerberus.php?mode=assign";
		break;
		case 5:
		url = "my_cerberus.php?mode=notification";
		break;
	}

	
//	if(show_sid) { url = url + "&" + sid; } document.location = url;
	document.location = formatURL(url);
}
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="2" valign="bottom" bgcolor="#FFFFFF"> 
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="99%"><img alt="Cerberus Logo" src="logo.gif"></td>
          <td width="1%" align="right" valign="bottom" nowrap><span class="cer_footer_text"><?php echo LANG_HEADER_LOGGED; ?>
              <b><?php echo $session->vars["login_handler"]->user_login; ?></b> [ <a href="<?php echo cer_href("logout.php"); ?>" class="cer_footer_text"><?php echo strtolower(LANG_WORD_LOGOUT); ?></a> ]</span>
              <?php if($unread_pm) { ?>
	            <br>
              	<a href="<?php echo cer_href("my_cerberus.php?mode=messages&pm_folder=ib"); ?>" class="cer_configuration_updated"><?php echo $unread_pm; ?> <?php echo LANG_HEADER_UNREAD_MESSAGES; ?>!</a>
              <?php } ?>
            
			<?php if(!empty($session->vars["login_handler"]->ticket_id)) { ?>
    	        <br>
				<span class="cer_footer_text"><B>[</B> <?php echo LANG_HEADER_LAST_VIEWED; ?>: <a href="<?php echo $session->vars["login_handler"]->ticket_url; ?>" class="cer_maintable_text"><?php echo substr($session->vars["login_handler"]->ticket_subject,0,45); ?></a> <B>]</B></span>
			<?php } ?>
            <br>
            <form name="headerForm" action="ticket_list.php" method="post" OnSubmit="this.override.value=this.category.value+this.search.value;" style="margin:0px;padding-top:3px;"><span class="cer_footer_text"><b>Quick Find:</b> </span><input type="hidden" name="override" value=""><select name="category" class="cer_footer_text">
            	<option value="i" <?php echo (@$session->vars['override_type']=='i') ? 'selected' : ''; ?>>Ticket ID/Mask
            	<option value="r" <?php echo (@$session->vars['override_type']=='r') ? 'selected' : ''; ?>>Requester
            	<option value="s" <?php echo (@$session->vars['override_type']=='s') ? 'selected' : ''; ?>>Subject
            	<option value="c" <?php echo (@$session->vars['override_type']=='c') ? 'selected' : ''; ?>>Content</select><input type="text" name="search" size="15" value="" class="cer_footer_text"><input type="submit" class="cer_button_face" value="<?php echo LANG_WORD_SEARCH; ?>"></form>
            <img alt="" src="includes/images/spacer.gif" height="3" width="1"></strong></span></td>
        </tr>
      </table>
</td>
  </tr>
  <tr> 
    <td colspan="2" valign="bottom" bgcolor="#FFFFFF" class="headerMenu"><img alt="" src="includes/images/spacer.gif" width="1" height="5"></td>
  </tr>
  <tr> 
    <td width="99%" valign="bottom" bgcolor="#888888"> 
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"></td>
          
          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          <td nowrap <?php if($page == "index.php") { ?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"><a href="<?php echo cer_href("index.php"); ?>" class="<?php if($page == "index.php") { ?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo "dashboard"; ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"></td>

        	 <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          <td nowrap <?php if($page == "getwork.php") { ?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"><a href="<?php echo cer_href("getwork.php"); ?>" class="<?php if($page == "getwork.php") { ?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo "teamwork"; ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"></td>
          
          	<td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          	<td nowrap <?php if($page == "ticket_list.php") { ?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("ticket_list.php"); ?>" class="<?php if($page == "ticket_list.php") { ?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_RESULTS; ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
          
          <?php if($acl->has_priv(PRIV_KB)) { ?>
	          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "knowledgebase.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("knowledgebase.php"); ?>" class="<?php if($page == "knowledgebase.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_KB; ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="1"></td>
          <?php } ?>
          
          <?php if($acl->has_priv(PRIV_CONFIG)) { ?>
	          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "configuration.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("configuration.php"); ?>" class="<?php if($page == "configuration.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_CONFIG; ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="1"></td>
          <?php } ?>
          
          <?php if($acl->has_priv(PRIV_COMPANY_CHANGE) || $acl->has_priv(PRIV_CONTACT_CHANGE)) { ?>
	          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "clients.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("clients.php"); ?>" class="<?php if($page == "clients.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_CONTACTS; ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
    	  <?php } ?>
          
          <?php if($acl->has_priv(PRIV_REPORTS)) { ?>
	    	  <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "reports.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="8"><a href="<?php echo cer_href("reports.php"); ?>" class="<?php if($page == "reports.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_REPORTS; ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
    	  <?php } ?>
          
         <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
 	      <td nowrap <?php if($page == "my_cerberus.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img alt="" src="includes/images/spacer.gif" width="15" height="8"><a href="<?php echo cer_href("my_cerberus.php"); ?>" class="<?php if($page == "my_cerberus.php") { ?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo strtolower(LANG_MYCERBERUS); ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
          
          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          <td><img alt="" src="includes/images/spacer.gif" width="1" height="20" align="absmiddle"></td>
        </tr>
      </table>
    </td>
    <td width="1%" nowrap bgcolor="#666666" valign="bottom" align="right">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr> 
          <td nowrap><img alt="" src="includes/images/spacer.gif" width="1" height="20" align="absmiddle"></td>

          <?php /*
          
          <?php if($urls.save_layout) { ?> 
          	<td nowrap><img alt="" src="includes/images/spacer.gif" width="15" height="8"><a href="{$urls.save_layout}" class="headerMenu"><?php echo strtolower(LANG_HEADER_SAVE_PAGE_LAYOUT); ?></a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
          <?php } ?>
          
          */ ?>
          
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td colspan="2" bgcolor="#003399" class="headerMenu"><table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td bgcolor="#FF6600"><img alt="" src="includes/images/spacer.gif" width="1" height="5"></td>
        </tr>
      </table></td>
  </tr>
</table>

