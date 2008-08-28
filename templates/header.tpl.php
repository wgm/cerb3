<script language="Javascript" type="text/javascript">
sid = "sid={$session_id}";
show_sid = {$track_sid};
error_nan = "{$smarty.const.LANG_CERB_ERROR_TICKET_NAN}";

sid = "sid={$session_id}";
show_sid = {$track_sid};

{if isset($new_pm) && $new_pm !== false}
new_pm = {$new_pm};
{else}
new_pm = false;
{/if}

{literal}
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

function toggleDiv(div,state) {
	var eDiv = document.getElementById(div);
	
	if(null != eDiv) {
		if(eDiv.style.display == "block" || 0==state) {
			eDiv.style.display = "none";
		} else {
			eDiv.style.display = "block";
		}
	}
}
function toggleDivInline(div,state) {
	var eDiv = document.getElementById(div);
	
	if(null != eDiv) {
		if(eDiv.style.display == "inline" || 0==state) {
			eDiv.style.display = "none";
		} else {
			eDiv.style.display = "inline";
		}
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

function findX(o)
{
	var left = 0;
	if (o.offsetParent)
	{
		while (o.offsetParent)
		{
			left += o.offsetLeft
			o = o.offsetParent;
		}
	}
	else if (o.x)
	{
		left += obj.x;
	}
	return left;
}

function findY(o)
{
	var top = 0;
	if (o.offsetParent)
	{
		while (o.offsetParent)
		{
			top += o.offsetTop
			o = o.offsetParent;
		}
	}
	else if (o.y) {
		top += o.y;
	}
	return top;
}	

{/literal}


</script>
{if (!empty($errorcode)) }
<font color="red"><center>{$errorcode}</center></font>
{/if}

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="2" valign="bottom" bgcolor="#FFFFFF"> 
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="99%"><img alt="Cerberus Logo" src="logo.gif"></td>
          <td width="1%" align="right" valign="bottom" nowrap><span class="cer_footer_text">
          		{$smarty.const.LANG_HEADER_LOGGED}
              <b>{$user_login}</b> 
     				<img src="includes/images/icone/16x16/flag_red.gif" width="16" height="16" border="0" title="My Flagged Tickets"><a href="{'ticket_list.php?override=h0'|cer_href}" class="cer_maintable_text" title="My Flagged Tickets">{$header_flagged}</a> <img src="includes/images/icone/16x16/hand_paper.gif" width="16" height="16" border="0" title="Tickets Suggested to Me"><a href="{'ticket_list.php?override=h1'|cer_href}" class="cer_maintable_text" title="Tickets Suggested to Me">{$header_suggested}</a>
              [ <a href="{$urls.logout}" class="cer_maintable_text">{$smarty.const.LANG_WORD_LOGOUT|lower}</a> ]</span>
              {if $unread_pm}
	            <br>
              	<a href="{$urls.mycerb_pm}" class="cer_configuration_updated">{$unread_pm} {$smarty.const.LANG_HEADER_UNREAD_MESSAGES}!</a>
              {/if}
              
			<span class="cer_footer_text">
			{if !empty($session->vars.login_handler->ticket_id)}
				<br>
				<B>[</B> {$smarty.const.LANG_HEADER_LAST_VIEWED}: <a href="{$session->vars.login_handler->ticket_url}" class="cer_maintable_text">{$session->vars.login_handler->ticket_subject|truncate:45:"..."|short_escape}</a> <B>]</B>
			{/if}
				</span>
            <br>
            <form name="headerForm" action="ticket_list.php" method="post" OnSubmit="this.override.value=this.category.value+this.search.value;" style="margin:0px;padding-top:3px;"><span class="cer_footer_text"><b>Quick Find:</b> </span><input type="hidden" name="override" value=""><select name="category" class="cer_footer_text">
            	<option value="i" {if $session->vars.override_type=='i'}selected{/if}>Ticket ID/Mask
            	<option value="r" {if $session->vars.override_type=='r'}selected{/if}>Requester
            	<option value="s" {if $session->vars.override_type=='s'}selected{/if}>Subject
            	<option value="c" {if $session->vars.override_type=='c'}selected{/if}>Content</select><input type="text" name="search" size="15" value="" class="cer_footer_text"><input type="submit" class="cer_button_face" value="{$smarty.const.LANG_WORD_SEARCH}"></form>
            <img alt="" src="includes/images/spacer.gif" height="3" width="1"></td>
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
          <td><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle"></td>
          
          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
          <td nowrap {if $page == "index.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle"><a href="{$urls.home}" class="{if $page == "index.php"}headerMenuActive{else}headerMenu{/if}">{"dashboard"|lower}</a><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle"></td>
          
          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
          <td nowrap {if $page == "getwork.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle"><a href="{"getwork.php"|cer_href}" class="{if $page == "getwork.php"}headerMenuActive{else}headerMenu{/if}">{"teamwork"|lower}</a><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle"></td>
          
       	<td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
       	<td nowrap {if $page == "ticket_list.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.search_results}" class="{if $page == "ticket_list.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_RESULTS}</a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
          
          {if $acl->has_priv($smarty.const.PRIV_KB) }
	          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
    	      <td nowrap {if $page == "knowledgebase.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.knowledgebase}" class="{if $page == "knowledgebase.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_KB}</a><img alt="" src="includes/images/spacer.gif" width="15" height="1"></td>
          {/if}
          
          {if $acl->has_priv($smarty.const.PRIV_CONFIG) }
	          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
    	      <td nowrap {if $page == "configuration.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.configuration}" class="{if $page == "configuration.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_CONFIG}</a><img alt="" src="includes/images/spacer.gif" width="15" height="1"></td>
          {/if}
          
          {if $acl->has_priv($smarty.const.PRIV_CONTACT_CHANGE) || $acl->has_priv($smarty.const.PRIV_COMPANY_CHANGE) }
	          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
    	      <td nowrap {if $page == "clients.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.clients}" class="{if $page == "clients.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_CONTACTS}</a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
    	  {/if}
          
          {if $acl->has_priv($smarty.const.PRIV_REPORTS) }
	    	  <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
    	      <td nowrap {if $page == "reports.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="8"><a href="{$urls.reports}" class="{if $page == "reports.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_REPORTS}</a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
    	  {/if}
          
         <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
 	      <td nowrap {if $page == "my_cerberus.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img alt="" src="includes/images/spacer.gif" width="15" height="8"><a href="{$urls.preferences}" class="{if $page == "my_cerberus.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_MYCERBERUS|lower}</a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
          
          <td valign="bottom"><img alt="" src="includes/images/menuSep.gif" width="1" height="10" align="middle"></td>
          <td><img alt="" src="includes/images/spacer.gif" width="1" height="20" align="middle"></td>
        </tr>
      </table>
    </td>
    <td width="1%" nowrap bgcolor="#666666" valign="bottom" align="right">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr> 
          <td nowrap><img alt="" src="includes/images/spacer.gif" width="1" height="20" align="middle"></td>

          {if $urls.save_layout}  
          	<td nowrap><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle"><a href="{$urls.save_layout}" class="headerMenu">{$smarty.const.LANG_HEADER_SAVE_PAGE_LAYOUT|lower}</a><img alt="" src="includes/images/spacer.gif" width="15" height="8"></td>
          {/if}
          
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
