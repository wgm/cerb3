/*
Copyright (c) 2006 WebGroup Media LLC. All rights reserved.
*/
function handleFailure(o) {
//	document.getElementById('getWorkContent').innerHTML = "Failed!";
	// [TODO]: Log, report
}
function displayReplyToolTip(id,txt) {
   var con = document.getElementById('displayreplytip' + id);
   if(null == con) return;
   
   if(0 == txt.length) {
   	con.innerHTML = "";
   	con.style.visibility = "hidden";
   } else {
	   con.innerHTML = txt;
	   con.style.visibility = "visible";
   }
}
var duecal=null;
function drawTicketDueCalendar(id) {
	if(null == duecal) {
		duecal = new YAHOO.widget.Calendar("duecal","duecal");
		duecal.minDate = new Date();
		duecal.onSelect = setDueCal;
		duecal.render();
	}
}
function setDueCal(e) {
	var date = this.getSelectedDates()[0];
	month = date.getMonth() + 1;
	day = date.getDate();
	year = date.getFullYear();
	var dateString = month + '/' + day + '/' + year + ' 12:00AM';
	document.workflowForm.ticket_due.value = dateString;
}
var delaycal=null;
function drawTicketDelayCalendar(id) {
	if(null == delaycal) {
		delaycal = new YAHOO.widget.Calendar("delaycal","delaycal");
		delaycal.minDate = new Date();
		delaycal.onSelect = setDelayCal;
		delaycal.render();
	}
}
function setDelayCal(e) {
	var date = this.getSelectedDates()[0];
	month = date.getMonth() + 1;
	day = date.getDate();
	year = date.getFullYear();
	var dateString = month + '/' + day + '/' + year + ' 12:00AM';
	document.workflowForm.ticket_delay.value = dateString;
}
function doDisplayReply(o) {
   var con = document.getElementById('reply_' + o.argument.threadId);
	if(null == con) return;
	try {
		con.innerHTML = o.responseText;
	} catch(e) {}
}
function displayClearThread(threadId) {
   var con = document.getElementById('reply_' + threadId);
	if(null == con) return;
	con.innerHTML = "";
}
function doDisplayGetSig(o) {
   var con = document.getElementById(o.argument.fill);
	if(null == con) return;
	con.value = con.value += o.responseText;
}
function displayGetSig(fill) {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=get_signature', {success: doDisplayGetSig,failure: handleFailure,argument: {fill:fill} });
}
function displayReply(threadId) {
   var con = document.getElementById('reply_' + threadId);
	if(null == con) return;
	con.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=display_reply&threadId=' + threadId, {success: doDisplayReply,failure: handleFailure,argument: {threadId:threadId} });
}
function displayForward(threadId) {
   var con = document.getElementById('reply_' + threadId);
	if(null == con) return;
	con.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=display_forward&threadId=' + threadId, {success: doDisplayReply,failure: handleFailure,argument: {threadId:threadId} });
}
function displayComment(threadId) {
   var con = document.getElementById('reply_' + threadId);
	if(null == con) return;
	con.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=display_comment&threadId=' + threadId, {success: doDisplayReply,failure: handleFailure,argument: {threadId:threadId} });
}
function displayReplySend(threadId) {
   var con = document.getElementById('reply_' + threadId);
	if(null == con) return;
	YAHOO.util.Connect.setForm('displayreply' + threadId);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=display_reply_send', {success: doDisplayReplySend,failure: handleFailure,argument: {threadId:threadId} });
}
function displayReplyAddAttach(threadId) {
	var frm = document.getElementById('reply_' + threadId);
	if(null == frm) return;
	
	var br = document.createElement('br');
	
	var txt = document.createElement('span');
	txt.innerHTML = "Attachment: ";
	
	var file = document.createElement('input');
	file.setAttribute('name','replyFile[]');
	file.setAttribute('type','file');
	file.setAttribute('size','64');

	frm.appendChild(txt);
	frm.appendChild(file);
	frm.appendChild(br);
}
function doTemplate(id,fill) { 
	window.open("update_show_templates.php?ticket_id=" + id + "&fill=" + fill, "wdwTemplate", "fullscreen=no,toolbar=no,status=no,menubar=no,scrollbars=yes,resizable=yes,directories=no,location=no,width=620,height=500");
}
function doRequestersGet(o) {
   var div = document.getElementById("divTicketRequesters");
	if(null == div) return;
	div.innerHTML = o.responseText;
}
function doRequesterReload(o) {
	getRequesters(o.argument.ticketId);
}
function getRequesters(ticketId) {
	var div = document.getElementById("divTicketRequesters");
	if(null == div) return;
	div.innerHTML = "Loading...<br>";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=display_get_requesters&id=' + ticketId, {success: doRequestersGet,failure: handleFailure,argument: {ticketId:ticketId} });
}
function requesterDel(ticketId,reqId) {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=display_requesters_del&id=' + ticketId + '&req=' + reqId, {success: doRequesterReload,failure: handleFailure,argument: {ticketId:ticketId} });
}
function requesterAdd(ticketId) {
	var frm = document.getElementById('frmRequesterAdd');
	if(null == frm) return;
	if(null == frm.requester_add || frm.requester_add.value == '') return;
	YAHOO.util.Connect.setForm('frmRequesterAdd');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=display_requesters_add', {success: doRequesterReload,failure: handleFailure,argument: {ticketId:ticketId} });
}
function doDisplaySpellCheck(box) {
	var textbox = document.getElementById(box);
	if(null == textbox) return;
	document.spellform.caller.value = box;
	document.spellform.spellstring.value = textbox.value;
	window.open("", "spellWindow", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=640,height=480');
	document.spellform.submit();
}
