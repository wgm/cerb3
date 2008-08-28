<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="assign">
<input type="hidden" name="form_submit" value="assign">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_green_glass"> 
    <td>&nbsp;Watcher Settings</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="2" width="100%" border="0">
		<tr>
			<td><span class="cer_maintable_text">
			As a <b>watcher</b> you will receive an e-mail copy of all new tickets and correspondence for the queues you select below.  A watcher can read and reply to tickets through any e-mail client, or simply use the e-mail messages as a notification to log into the GUI.<br>
			<br>
			Your watcher e-mails will be sent to:  &lt;<b>{$user_email}</b>&gt;<br>
			<br>
			<b>Watch which teams?</b><br>
			</td>
		</tr>

		<tr>
			<td>
			<table border="0" cellspacing="1" cellpadding="1">
				{foreach name=teams from=$teams item=team key=teamId}
				<tr>
				  <td><label><input type="checkbox" name="watcher_team[]" value="{$teamId}" {if $team->agents.$uid->is_watcher == 1}CHECKED{/if}> {$team->name}</label></td>
				</tr>
				{/foreach}
			</table>
			</td>
		</tr>
		
	</table>
	<table border=0 cellspacing=0 cellpadding=4 width="100%">
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="right">
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
        	</td>
      	</tr>
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>

</form>
