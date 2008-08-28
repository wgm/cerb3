{if is_array($dashboard_queues) && !empty($dashboard_queues)}
<table border="0" cellpadding="0" cellspacing="0" class="table_green" width="100%">
   <tr>
     <td class="bg_green"><table width="100%" border="0" cellspacing="0" cellpadding="0">
         <tr>
           <td nowrap="nowrap"><span class="text_title_white"><img src="includes/images/icone/16x16/folder_network.gif" alt="A tag" width="16" height="16" /> Mailbox Loads </span></td>
         </tr>
     </table></td>
   </tr>
   <tr>
     <td bgcolor="#F0F0FF"><span class="box_text">
			<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF" width="100%">
				{foreach from=$dashboard_queues key=queueId item=queue name=queues}
						{if $total_queue_hits}
							{math assign=percent equation="(x/y)*50" x=$queue->active_tickets y=$total_queue_hits format="%0.0f"}
							{math assign=percenti equation="50-x" x=$percent format="%0.0f"}
						{else}
							{assign var=percent value="1"}
							{assign var=percenti value="49"}
						{/if}
						<tr>
						  <td bgcolor="#F0F0FF" width="100%" align="left" nowrap="nowrap"><a href="{"ticket_list.php?override=q"|cat:$queueId|cer_href|cat:"#results"}" class="text_ticket_links"><b>{$queue->queue_name}</b></a> <span class="cer_footer_text" title="Available Active Tickets">({$queue->active_tickets})</span></td>
						  <td bgcolor="#DDDDDD" width="0%" align="left" nowrap="nowrap"><img src="includes/images/cerb_graph.gif" width="{$percent}" height="15" /><img src="includes/images/cer_graph_cap.gif" /><img src="includes/images/spacer.gif" width="{$percenti}" height="1"></td>
						</tr>
				{/foreach}
		   </table>
		</td>
	</tr>
</table>
<br>
{/if}