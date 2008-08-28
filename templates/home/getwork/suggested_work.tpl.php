<script type="text/javascript">
	{literal}
	function cbGetSuggested(e, obj) {
		getSuggestedTickets();
	}
	{/literal}

	YAHOO.util.Event.addListener(window,"load",cbGetSuggested);
</script>
<span class="text_title">Suggested Tickets</span><br />
<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" alt="" width="1" height="1" /></td></tr></table>
<br />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" valign="top">
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		   <tr>
		     <td bgcolor="#3C96FC"><img alt="A scroll" src="includes/images/icone/16x16/scroll_view.gif" width="16" height="16" /><span class="text_title_white">Recommended to Me by Others</span><span class="text_heading"> &nbsp; </span> <a href="javascript:void(0);" onclick="getSuggestedTickets();" class="link_box_edit">refresh</a></td>
		   </tr>
		</table>
	   <span id="suggestedTicketsContent"></span><br>
	 </td>
  </tr>
</table>