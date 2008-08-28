<script type="text/javascript">

function doReload_{$view->view_slot}(value)
{literal} { {/literal}
	url = formatURL('{$view->page_name}?{$view->view_slot}=' + value);
	{if !empty($ticket)} url = url + '&ticket={$o_ticket->ticket_id}';{/if}
	{if !empty($mode)} url = url + '&mode={$mode}';{/if}
	document.location =  url;
{literal} } {/literal}

var {$view->view_slot}_toggleCheck = 0;
	
function checkAllToggle_{$view->view_slot}()
{literal}
{
	{/literal}
	{$view->view_slot}_toggleCheck = ({$view->view_slot}_toggleCheck) ? 0 : 1;
	{literal}

	for(e = 0;e < document.viewform_{/literal}{$view->view_slot}{literal}.elements.length; e++) {
		if(document.viewform_{/literal}{$view->view_slot}{literal}.elements[e].type == 'checkbox' && document.viewform_{/literal}{$view->view_slot}{literal}.elements[e].name == 'bids[]') {
			document.viewform_{/literal}{$view->view_slot}{literal}.elements[e].checked = {/literal}{$view->view_slot}{literal}_toggleCheck;
		}
	}
}
{/literal}

function doViewOptions_{$view->view_slot}() {literal}
{
	if(document.getElementById)
	{
			{/literal}if(document.getElementById("view_{$view->view_slot}_options").style.display=="block"){literal}
			{
				{/literal}
				document.getElementById("view_{$view->view_slot}_options").style.display="none";
				{if $urls.save_layout && $page == $view->view_bind_page}
//						document.formSaveLayout.layout_view_options_{$view->view_slot}.value = 0;
				{/if}
				{literal}
			}
			else
			{
				{/literal}
				document.getElementById("view_{$view->view_slot}_options").style.display="block";
				{if $urls.save_layout && $page == $view->view_bind_page}
//						document.formSaveLayout.layout_view_options_{$view->view_slot}.value = 1;
				{/if}
				{literal}
			}
	}
}
{/literal}

</script>

<table border="0" cellpadding="2" cellspacing="0" class="table_blue" bgcolor="#F0F0FF" width="100%">
	<tr>
	  <td class="bg_blue"><table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
	      <td width="100%"><span class="text_title_white"> {$view->view_name|escape:"htmlall"}</span></td>
	      <td width="0%" nowrap="nowrap">{if $view->view_id}<img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle"><a href="{"ticket_list.php?override=v"|cat:$view->view_id|cer_href}" class="headerMenu">search</a> | <a href="javascript:;" onclick="doSearchCriteriaList('{$view->view_slot}_customize');doViewOptions_{$view->view_slot}();" class="headerMenu">customize</a><img alt="" src="includes/images/spacer.gif" width="15" height="8" align="middle">{/if}</td>
	  </tr>
	  </table></td>
	</tr>
</table>

{* [JAS]: Draw View select Box *}
{if $view->view_id}
<div id="view_{$view->view_slot}_options" style="display:{if $view->show_options}block{else}none{/if};">
<table width="100%" border="0" cellspacing="0" cellpadding="1">
		<td align="left" bgcolor="#DDDDDD">
			<div id="view_slot_{$view->view_slot}_options" style="display:block;">
			<form name="view_slot_{$view->view_slot}" action="{$view->page_name}" method="post" style="margin:0px;">
			<input type="hidden" name="sid" value="{$session_id}">
			<input type="hidden" name="vid" value="{$view->view_id}">
			<input type="hidden" name="view_submit_mode" value="0">
			<input type="hidden" name="view_submit" value="{$view->view_slot}">
			
				<b>Name: </b><br>
				<input type="text" name="{$view->view_slot}_name" value="{$view->view_name}" size="45" maxlength="64"><br>
				<br>
				
				{assign var=cols value=$view->getActiveColumns()}
				<b>Columns:</b><br>
				{section loop=15 start=0 step=1 name=x}
					{assign var=i value=$smarty.section.x.index}
					{$smarty.section.x.index|string_format:"%02d"}: <select name="{$view->view_slot}_columns[]">
					{html_options options=$view->getColumnOptions() selected=$cols[$i]}
					</select><br>
				{/section}
				<br>
				<b>Paging: </b><br>
				{$smarty.const.LANG_WORD_SHOW}
				<input type="text" name="{$view->view_slot}_filter_rows" value="{$view->filter_rows}" size="2" maxlength="3" class="cer_footer_text"> rows<br>
				View position on page <input type="text" name="{$view->view_slot}_order" value="{$view->view_order}" size="2" maxlength="3" class="cer_footer_text"><br>
				<br>

				<input type="button" onclick="this.form.view_submit_mode.value=1;this.form.submit();" value="{$smarty.const.LANG_WORD_DELETE|lower}" class="cer_button_face">
				<input type="button" onclick="this.form.view_submit_mode.value=0;this.form.submit();" value="{$smarty.const.LANG_BUTTON_SAVE|lower}" class="cer_button_face">
				</form>
				
				<br>
				
				<table>
					<tr>
						<td valign="top">
							<span id="{$view->view_slot}_customize_searchCriteriaList"></span>
						</td>
						<td valign="top">{include file="search/search_builder.tpl.php" label=$view->view_slot|cat:"_customize"}</td>
					</tr>
				</table>
				<br>
			</div>
		</td>
    </tr>
</table>
</div>
{/if}

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	{* [JAS]: Draw Column Headings *}
	<tr bgcolor="#C0C0C0"> 
	{if $view->view_adv_controls}
		<td align="{$view->columns[0]->column_align}" style="padding-left: 2px;">
			<a href="{$view->columns[0]->column_url}" class="cer_maintable_heading">{$view->columns[0]->column_heading}</a>
		</td>
	{/if}
	
	{section name=col loop=$view->columns start=2}
	{* foreach from=$view->columns item=col name=col start=2 *}
		<td align="{$view->columns[col]->column_align}" style="padding-left: 2px;">
			{if $view->columns[col]->column_sortable}
				<a href="{$view->columns[col]->column_url}" class="cer_maintable_heading">{$view->columns[col]->column_heading|short_escape}</a>
			{else}
			 	<span class="cer_maintable_heading">{$view->columns[col]->column_heading|short_escape}</span>
			{/if}
		</td>
	{/section}
    </tr>
    
    {* [JAS]: Draw View Rows *}
    {if $view->show_modify}
		<form action="ticket_list.php" method="post" name="viewform_{$view->view_slot}" id="viewform_{$view->view_slot}" onsubmit="return false;">
		<input type="hidden" name="sid" value="{$session_id}">
		<input type="hidden" name="mass_slot" value="{$view->view_slot}">
		<input type="hidden" name="form_submit" value="tickets_modify">
	{/if}
	
    {if $view->show_mass && $view->view_adv_controls}
		<form action="index.php" method="post" name="viewform_{$view->view_slot}" id="viewform_{$view->view_slot}" onsubmit="return false;">
		<input type="hidden" name="sid" value="{$session_id}">
		<input type="hidden" name="mass_slot" value="{$view->view_slot}">
		<input type="hidden" name="form_submit" value="tickets_modify">
	{/if}

	<tr><td colspan="{$view->view_colspan}" class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	{section name=row loop=$view->rows}
		
		{if $view->view_adv_2line}
		<tr class="{if %row.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
		
			{if $view->view_adv_controls}
			<td rowspan="{if $view->view_adv_2line}2{else}1{/if}" align="center">
				{$view->rows[row][0]}
			</td>
			{/if}
			
			{if $view->view_adv_2line}
				<td colspan="{$view->view_colspan_subject}">{$view->rows[row][1]}</td>
			{/if}
			
      </tr>
      {/if}
        
		<tr class="{if %row.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}" title="">
		
		{* [JAS]: If we are not showing subjects on two lines but are showing checkboxes, draw now *}
		{if !$view->view_adv_2line && $view->view_adv_controls}
          <td style="padding-left: 2px; padding-right: 2px;" align="{$view->columns[0]->column_align}">
          	{$view->rows[row][0]}
          </td>
		{/if}
		
		{section name=col loop=$view->rows[row] start=2}
          <td style="padding-left: 2px; padding-right: 2px;" align="{$view->columns[col]->column_align}" {$view->columns[col]->column_extras}>
          	{$view->rows[row][col]}
          </td>
        {/section}
        </tr>
	    <tr><td colspan="{$view->view_colspan}" bgcolor="#FFFFFF"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	{/section}
	
	{if $view->show_modify}
	  {include file="views/mass_actions.tpl.php" col_span=$view->view_colspan}
	  </form>
	{/if}
	
	{if $view->show_mass && $view->view_adv_controls}
	  {include file="views/mass_actions.tpl.php" col_span=$view->view_colspan}
	  </form>
	{/if}
	
</table>

<table width="100%" border="0" cellspacing="1" cellpadding="2">
	<tr>
		<td width="100%" align="right" class="cer_footer_text">
			{if $view->show_prev}<a href="{""|cat:$smarty.server.PHP_SELF|cat:"?"|cat:$view->view_slot|cat:"_p=0"|cer_href}" class="cer_header_loginLink">&lt;&lt;</a>{/if}
			{if $view->show_prev}<a href="{$view->view_prev_url}" class="cer_header_loginLink">&lt;{$smarty.const.LANG_WORD_PREV}</a>{/if}
			({$smarty.const.LANG_WORD_SHOWING} {$view->show_from}-{$view->show_to} {$smarty.const.LANG_WORD_OF} {$view->show_of}) 
			{if $view->show_next}<a href="{$view->view_next_url}" class="cer_header_loginLink">{$smarty.const.LANG_WORD_NEXT}&gt;</a>{/if}
			{if !$view->filter_rows}
				{assign var=last_page value=0}
			{else}
				{math assign=last_page equation="ceil(x/y)" x=$view->show_of y=$view->filter_rows}
				{math assign=last_page equation="x-1" x=$last_page format="%0d"}
			{/if}
			{if $view->show_next}<a href="{""|cat:$smarty.server.PHP_SELF|cat:"?"|cat:$view->view_slot|cat:"_p="|cat:$last_page|cer_href}" class="cer_header_loginLink">&gt;&gt;</a>{/if}	
		</td>
	</tr>
</table>
