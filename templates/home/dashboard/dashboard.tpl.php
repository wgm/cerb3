<script type="text/javascript">
//	YAHOO.util.Event.addListener(window,"load",getTeamWorkloads);
</script>

{if $selDashboard}
{foreach from=$selDashboard->views item=view key=viewId}
{include file="views/ticket_view.tpl.php" view=$dashboardViews.$viewId}
<br>
{/foreach}
{else}
	<table cellpadding="3" cellspacing="0" border="0" width="100%" class="table_purple" bgcolor="#F0F0FF">
	<tr>
		<td>
		<span class="link_ticket">Welcome to Ticket Dashboards!</span><br>
		You do not have a dashboard selected.<br>
		<br>
		Dashboards allow you to create and save highly customizable lists of tickets.  You can load your 
		dashboards from the 'Dashboards' list to the top right of this box.  You can save your favorite 
		dashboard by selecting it and clicking the 'Save Page Layout' link at the top of this page.<br>
		<br>
		Would you like to create a default dashboard now?<br>
		<a href="{"index.php?form_submit=default_dashboard"|cer_href}">Yes!  Please create a default dashboard for me.</a><br>
		</td>
	</tr>
	</table>
	<br>
{/if}