<form action="#" name="frmRequesterAdd" id="frmRequesterAdd" style="margin:0px;" onsubmit="return false;">
<div id="divTicketRequesters"></div>
</form>
<script type="text/javascript">
	YAHOO.util.Event.addListener(document.body,"load",getRequesters({$wsticket->id}));
</script>
