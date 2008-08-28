<script type="text/javascript">
	{literal}
	function cbGetMy(e, obj) {
		getMyTickets();
	}
	{/literal}

	YAHOO.util.Event.addListener(window,"load",cbGetMy);
</script>
<img src="includes/images/icone/16x16/flag_green.gif" width="16" height="16" border="0" alt="A flag"><span class="text_title">My Tickets</span><br />
<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" alt="" width="1" height="1" /></td></tr></table>
<br />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" valign="top">
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		   <tr>
		     <td bgcolor="#3C96FC"><img alt="A scroll" src="includes/images/icone/16x16/scroll_view.gif" width="16" height="16" /><span class="text_title_white">Active Tickets</span><span class="text_heading"> &nbsp; </span> <a href="javascript:void(0);" onclick="getMyTickets();" class="link_box_edit">refresh</a></td>
		   </tr>
		</table>
	   <span id="myTicketsContent"></span><br>
	 </td>
  </tr>
</table>