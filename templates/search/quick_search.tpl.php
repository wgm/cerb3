<div id="search" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_home_show_search}block{else}none{/if};">
<a name="search_box"></a>
<form action="ticket_list.php" method="post" name="search" id="searchForm" onsubmit="javascript:checkWorkflowTrees();" style="margin:0px;">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="search_id" value="{$session->vars.psearch->params.search_id}">
<input type="hidden" name="search_submit" value="1">
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#DDDDDD">
<tr><td class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr> 
  <td class="boxtitle_gray_dk">
  	{$smarty.const.LANG_SEARCH_TITLE}
  </td>
</tr>
<tr><td class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>

<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">

<tr> 
  <td bgcolor="#DDDDDD" width="0%" nowrap><span class="cer_maintable_headingSM">&nbsp;{$smarty.const.LANG_SEARCH_STATUS}:<img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></span></td>
  <td bgcolor="#EEEEEE" width="50%" nowrap>
  	<select name="search_status">
  		<option value="">- any status -
  		<option value="1" {if $session->vars.psearch->params.search_status==1}selected{/if}>- any active -
  		<option value="2" {if $session->vars.psearch->params.search_status==2}selected{/if}>- waiting on customer -
  		<option value="3" {if $session->vars.psearch->params.search_status==3}selected{/if}>- closed -
  		<option value="4" {if $session->vars.psearch->params.search_status==4}selected{/if}>- deleted -
  	</select>
   </td>
  <td bgcolor="#DDDDDD" width="0%" nowrap><span class="cer_maintable_headingSM">&nbsp;{$smarty.const.LANG_SEARCH_SENDER}:<img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></span></td>
  <td bgcolor="#EEEEEE" width="50%" nowrap>
    <input type="text" size="15" name="search_sender" class="cer_maintable_text" value="{$session->vars.psearch->params.search_sender|short_escape}" style="width: 99%;">
  </td>
</tr>

<tr bgcolor="#DDDDDD">
  <td class="cer_maintable_headingSM" width="0%" nowrap valign="top">&nbsp;{$smarty.const.LANG_SEARCH_SUBJECT}:</td>
  <td bgcolor="#EEEEEE" class="cer_maintable_headingSM" width="50%" valign="top"> 
    <input type="text" size="15" name="search_subject" class="cer_maintable_text" value="{$session->vars.psearch->params.search_subject|short_escape}" style="width: 99%;">
  </td>
  <td class="cer_maintable_headingSM" width="0%" nowrap valign="top">&nbsp;{$smarty.const.LANG_SEARCH_CONTENT}:</td>
  <td class="cer_maintable_headingSM" bgcolor="#EEEEEE" width="50%" valign="top"> 
    <input type="text" size="15" name="search_content" class="cer_maintable_text" value="{$session->vars.psearch->params.search_content|short_escape}" style="width: 99%;">
  </td>
</tr>
	
<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="0%" nowrap>&nbsp;{$smarty.const.LANG_SEARCH_COMPANY}:<img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_text" width="50%" nowrap> 
    <input type="text" name="search_company" size="30" style="width:99%;" value="{$session->vars.psearch->params.search_company}" />
  </td>
  <td class="cer_maintable_headingSM" width="0%" nowrap valign="top">&nbsp;Entered Queue:<img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td class="cer_maintable_headingSM" bgcolor="#EEEEEE" width="50%" valign="top"> 
	<select name="search_queue">
		<option value="">- any -
		{foreach from=$queues key=queueId item=queue name=queues}
			{if isset($acl->queues[$queueId])}
			<option value="{$queue->queue_id}" {if $session->vars.psearch->params.search_queue == $queue->queue_id}selected{/if}>{$queue->queue_name}
			{/if}
		{/foreach}
	</select>
  </td>
</tr>

<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="0%" nowrap>&nbsp;Flagged: <img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_text" width="50%" nowrap> 
  	 <select name="search_flagged">
  	 	<option value="0" {if $session->vars.psearch->params.search_flagged==0}selected{/if}>- any -
  	 	<option value="1" {if $session->vars.psearch->params.search_flagged==1}selected{/if}>Yes
  	 	<option value="2" {if $session->vars.psearch->params.search_flagged==2}selected{/if}>No
  	 </select>
  </td>
  <td class="cer_maintable_headingSM" width="0%" nowrap valign="top">&nbsp;<img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td class="cer_maintable_headingSM" bgcolor="#EEEEEE" width="50%" valign="top"> 
  </td>
</tr>

<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="0%" nowrap valign="top"><label>&nbsp;Workflow: <input type="checkbox" name="search_workflow" value="1" onclick="javascript:toggleDiv('workflowSearch');" {if $session->vars.psearch->params.search_workflow}checked{/if}></label><img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_text" colspan="3" width="100%">
  
<script type="text/javascript">
{literal}
var tagTree = new YAHOO.widget.TreeView("applyTagTree");
var agentTree = new YAHOO.widget.TreeView("applyAgentTree");
var teamTree = new YAHOO.widget.TreeView("applyTeamTree");

function scanAndAppend(form,ptr) {
	for(x=0;x < ptr.length; x++) {
		if(ptr[x].type=="checkbox" && ptr[x].checked) {
			var input = document.createElement("input");
			input.setAttribute("type","hidden");
			input.setAttribute("name",ptr[x].name);
			input.setAttribute("value",ptr[x].value);
			form.appendChild(input);
		}
	}
}

function scanAndCheck(ptr,bool) {
	for(x=0;x < ptr.length; x++) {
		if(ptr[x].type=="checkbox") {
			ptr[x].checked = bool;
		}
	}
}

function checkWorkflowTrees() {
	var wForm = document.getElementById("searchForm");
	
	tagTree.expandAll();
	agentTree.expandAll();
	teamTree.expandAll();
	
	var ptr = document.getElementById("applyTagTree").getElementsByTagName("input");
	scanAndAppend(wForm,ptr);
	
	var ptr = document.getElementById("applyAgentTree").getElementsByTagName("input");
	scanAndAppend(wForm,ptr);
	
	var ptr = document.getElementById("applyTeamTree").getElementsByTagName("input");
	scanAndAppend(wForm,ptr);
	
	return true;
}

function drawWorkflowTrees() {
{/literal}
	
	{* Tags *}
	{if !empty($tags) && is_array($tags->sets) }
		{foreach from=$tags->sets name=sets item=set key=set_id}
			{foreach from=$set->tags name=tags item=tag key=tag_id}
			{assign var=rSel value=$session->vars.psearch->params.search_tags.$tag_id}
				var myobj = {literal}{{/literal} checkName: "searchTags[]", checkValue: "{$tag_id}", isChecked:{if $rSel}true{else}false{/if}, label: "<span class='workflow_item'>{if $tag->num_articles > 0}<b>{$tag->name}</b>{else}{$tag->name}{/if}</span>" {literal}}{/literal} ;
				var node{$tag_id} = new YAHOO.widget.CheckNode(myobj, {if $tag->parent_tag_id==0}tagTree.getRoot(){else}node{$tag->parent_tag_id}{/if}, false);
			{/foreach}
		{/foreach}
	{/if}

	{* Agents *}
	{if !empty($agents) && is_array($agents) }
		{foreach from=$agents name=agents item=agent key=agentId}
		{assign var=rSel value=$session->vars.psearch->params.search_agents.$agentId}
			var myobj = {literal}{{/literal} checkName: "searchAgents[]", checkValue: "{$agentId}", isChecked:{if $rSel}true{else}false{/if}, label: "<span class='workflow_item'>{$agent->getRealName()}</span>" {literal}}{/literal} ;
			var node = new YAHOO.widget.CheckNode(myobj, agentTree.getRoot(), false);
		{/foreach}
	{/if}

	{* Teams *}
	{if !empty($teams) && is_array($teams) }
		{assign var=user_id value=$session->vars.login_handler->user_id}
		{foreach from=$teams name=teams item=team key=teamId}
		{if isset($team->agents.$user_id)}
			{assign var=rSel value=$session->vars.psearch->params.search_teams.$teamId}
			var myobj = {literal}{{/literal} checkName: "searchTeams[]", checkValue: "{$teamId}", isChecked:{if $rSel}true{else}false{/if}, label: "<span class='workflow_item'>{$team->name}</span>" {literal}}{/literal} ;
			var node = new YAHOO.widget.CheckNode(myobj, teamTree.getRoot(), false);
		{/if}
		{/foreach}
	{/if}
	
	tagTree.draw();
	agentTree.draw();
	teamTree.draw();
{literal}
}
{/literal}
</script>
  <span id="workflowSearch" style="display:{if $session->vars.psearch->params.search_workflow}block{else}none{/if};">
	<table border="0" cellspacing="2" cellpadding="0">
        <tr>
          <td valign="top"><table border="0" cellpadding="0" cellspacing="0" class="table_orange">
            <tr>
              <td><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FF8000">
                  <tr>
                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Folder" src="includes/images/icone/16x16/folder_network.gif" alt="find" width="16" height="16" /> Tags </span></td>
                  </tr>
              </table></td>
            </tr>
            <tr>
					<td bgcolor="#FFF0D9" style="padding:2px;">
						<a href="javascript:tagTree.expandAll();" class="link_navmenu">expand</a>
						|
						<a href="javascript:tagTree.collapseAll();" class="link_navmenu">collapse</a>
						|
						<a href="javascript:;" onclick="tagTree.expandAll();scanAndCheck(document.getElementById('applyTagTree').getElementsByTagName('input'),true);" class="link_navmenu">all</a>
						|
						<a href="javascript:;" onclick="tagTree.expandAll();scanAndCheck(document.getElementById('applyTagTree').getElementsByTagName('input'),false);" class="link_navmenu">none</a>
						<span id="applyTagTree"></span>
					</td>
            </tr>
          </table></td>
          <td valign="top"><table border="0" cellpadding="0" cellspacing="0" class="table_blue">
            <tr>
              <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#AD5BFF">
                  <tr>
                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Headset" src="includes/images/icone/16x16/user_headset.gif" alt="find" width="16" height="16" /> Agents </span></td>
                  </tr>
              </table></td>
            </tr>
            <tr>
              <td bgcolor="#F3E8FF" style="padding:2px;">
              <span id="applyAgentTree"></span>
              </td>
            </tr>
          </table></td>
          <td valign="top"><table border="0" cellpadding="0" cellspacing="0" class="table_green">
            <tr>
              <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#00DD37">
                  <tr>
                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Team" src="includes/images/icone/16x16/businessmen.gif" alt="find" width="16" height="16" /> Teams </span></td>
                  </tr>
              </table></td>
            </tr>
			            <tr>
              <td bgcolor="#DDFFCE" style="padding:2px;">
              <span class="">
			  	 	<label><input type="radio" name="search_assigned" value="0" {if $session->vars.psearch->params.search_assigned==0}checked{/if}>any</label>
			  	 	<label><input type="radio" name="search_assigned" value="2" {if $session->vars.psearch->params.search_assigned==2}checked{/if}>none</label>
			  	 	<label><input type="radio" name="search_assigned" value="1" {if $session->vars.psearch->params.search_assigned==1}checked{/if}>selected:</label>
              </span>
              <span id="applyTeamTree"></span>
              </td>
            </tr>
          </table>
          </td>
        </tr>
      </table>
      </span>
  </td>
</tr>

<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="0%" nowrap valign="top"><label>&nbsp;Flagged by: <input type="checkbox" name="search_flags" value="1" onclick="javascript:toggleDiv('flagSearch');" {if $session->vars.psearch->params.search_flags}checked{/if}></label><img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_text" colspan="3" width="100%">
  <span id="flagSearch" style="display:{if $session->vars.psearch->params.search_flags}block{else}none{/if};">
	<table border="0" cellspacing="2" cellpadding="0" width="100%">
	  <tr>
	  	<td>
	  		<table border="0" cellpadding="0" cellspacing="0" class="table_green">
            <tr>
              <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#00DD37">
                  <tr>
                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Flags" src="includes/images/icone/16x16/flag_red.gif" alt="find" width="16" height="16" /> Flags </span></td>
                  </tr>
              </table></td>
            </tr>
            <tr>
              <td bgcolor="#DDFFCE" style="padding:2px;" class="workflow_item">
              {foreach from=$agents name=agents item=agent key=agentId}
				  {assign var=rSel value=$session->vars.psearch->params.search_flags.$agentId}
              	<label><input type="checkbox" name="searchFlags[]" value="{$agentId}" {if $rSel}checked{/if}>{$agent->getRealName()}</label><br>
              {/foreach}
              </td>
            </tr>
         </table>
	  	</td>
	  </tr>
	</table>
  </span>
  </td>
</tr>

<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="0%" nowrap valign="top"><label>&nbsp;Advanced: <input type="checkbox" name="search_advanced" value="1" onclick="javascript:toggleDiv('advancedSearch');" {if $session->vars.psearch->params.search_advanced}checked{/if}></label><img alt="" src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_text" colspan="3" width="100%">
  <span id="advancedSearch" style="display:{if $session->vars.psearch->params.search_advanced}block{else}none{/if};">
	<table border="0" cellspacing="2" cellpadding="0" width="100%">
	  <tr>
	  	<td>
	  		{include file="search/advsearch.tpl.php" col_span=$col_span}
	  	</td>
	  </tr>
	</table>
  </span>
  </td>
</tr>

</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
<tr>
	<td bgcolor="#CCCCCC" align="right" class="cer_maintable_text">
		<input type="hidden" name="search_mode" value="0">

		<span class="cer_footer_text">Results per page:</span> <input type="text" size="5" maxlength="4" name="search_limit" value="{if $session->vars.login_handler->user_prefs->view_prefs->vars.sv_filter_rows}{$session->vars.login_handler->user_prefs->view_prefs->vars.sv_filter_rows}{else}100{/if}">
		
		{if $session->vars.psearch->params.search_id} {* if the search_id is non-zero *}
			<input type="button" class="cer_button_face" value="Delete" onclick="this.form.search_mode.value=2;form.submit();">
			<input type="button" class="cer_button_face" value="Search &amp; Save" onclick="this.form.search_mode.value=1;form.submit();">
			<input type="button" class="cer_button_face" value="Run as New Search" onclick="this.form.search_mode.value=0;form.submit();">
		{else}
			<span class="cer_footer_text">Save as:</span> <input type="input" name="search_title" size="32" maxlength="128" value="">
			<input type="button" class="cer_button_face" value="Search &amp; Save" onclick="this.form.search_mode.value=1;form.submit();">
			<input type="button" class="cer_button_face" value="Search (Don't Save)" onclick="this.form.search_mode.value=0;form.submit();">
		{/if}
	</td>
</tr>
</form>
</table>

</div>