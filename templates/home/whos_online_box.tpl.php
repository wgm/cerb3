<span class="link_ticket">{$smarty.const.LANG_FOOTER_WHOS_ONLINE}: ({$cer_who->who_user_count_string})</span><br>
<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" alt="" width="1" height="1" /></td></tr></table>

{section name=who_id loop=$cer_who->who_users}
 	<span class="cer_whos_online_text">
 	{if !empty($cer_who->who_users[who_id]->user_name)}
 		<b>{$cer_who->who_users[who_id]->user_name}</b>
 	{/if}
 	{if !empty($cer_who->who_users[who_id]->user_login)}
 		({$cer_who->who_users[who_id]->user_login})
 	{/if}
 	{$cer_who->who_users[who_id]->user_action_string} 
 	(ip: {$cer_who->who_users[who_id]->user_ip} 
 	idle: {$cer_who->who_users[who_id]->user_idle_secs}) 
 	{if $cer_who->who_users[who_id]->user_pm_url != ""}
 		(<a href="{$cer_who->who_users[who_id]->user_pm_url}" class="cer_whos_online_text">{$smarty.const.LANG_FOOTER_SEND_PM}</a>)
 	{/if}
 	</span>
 	<br>
{/section}
