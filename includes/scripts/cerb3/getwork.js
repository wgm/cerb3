/*
Copyright (c) 2006 WebGroup Media LLC. All rights reserved.
*/
function handleFailure(o) {
//	document.getElementById('getWorkContent').innerHTML = "Failed!";
	// [TODO]: Log, report
}
function getWorkToolTip(id,txt) {
   var con = document.getElementById('getworktip' + id);
   if(null == con) return;
   
   if(0 == txt.length) {
   	con.innerHTML = "";
   	con.style.visibility = "hidden";
   } else {
	   con.innerHTML = txt;
	   con.style.visibility = "visible";
   }
}
function doGetTeamWorkloads(o) {
   var con = document.getElementById('teamWorkloads');
	if(null == con) return;
	try {
		con.innerHTML = o.responseText;
	} catch(e) {}
}
var aryWorkflow = new Array();
function doGetWorkWorkflow(o) {
   var con = document.getElementById('getworkopts'+o.argument.id);
	if(null == con) return;
	try {
		con.innerHTML = o.responseText;
		aryWorkflow[o.argument.id] = new CerQuickWorkflow(o.argument.id,'frmQuickWorkflowSearch_'+o.argument.id);
		aryWorkflow[o.argument.id].postResultsAction = function() {
			this.refresh();
		}
		aryWorkflow[o.argument.id].refresh();
		autoTags('tag_input_' + o.argument.id,'searchcontainer_' + o.argument.id);
	} catch(e) {}
}
// [JAS]: [TODO] saveWorkflow is now saveProperties
function doSaveWorkflow(o) {
   var con = document.getElementById('getworkopts'+o.argument.id);
   if(null == con) return;
	clearGetWorkOpts(o.argument.id);
	getWorkId(o.argument.id);
	document.location = "#ticket" + o.argument.id;
}
function doSaveReply(o) {
   var con = document.getElementById('getworkpre'+o.argument.id);
   if(null == con) return;
	con.innerHTML = "";
	getWorkId(o.argument.id);
}
function doSaveComment(o) {
   var con = document.getElementById('getworkpre'+o.argument.id);
   if(null == con) return;
	con.innerHTML = "";
	getWorkId(o.argument.id);
}
function doGetWorkItem(o) {
   var con = document.getElementById('getwork'+o.argument.id);
   if(null == con) return;
   con.innerHTML = o.responseText;
}
function doGetWork(o) {
   var con = document.getElementById('myTicketsContent');
   if(null == con) return;
   con.innerHTML = o.responseText;
   getTeamWorkloads();
}
function doMyTickets(o) {
   var con = document.getElementById('myTicketsContent');
   if(null == con) return;
   con.innerHTML = o.responseText;
}
function doDispatcher(o) {
   var con = document.getElementById('dispatcherContent');
   if(null == con) return;
   con.innerHTML = o.responseText;
}
function doSuggestedTickets(o) {
   var con = document.getElementById('suggestedTicketsContent');
   if(null == con) return;
   con.innerHTML = o.responseText;
}
function doMonitor(o) {
   if(!o.responseText) return;

   var con = document.getElementById('monitorContent');
   var div = document.createElement("div");
   div.innerHTML = o.responseText;

   var anim = new YAHOO.util.Anim(div, {opacity: {from:0,to:1}}, 1, YAHOO.util.Easing.easeOut);

	if(null == con.childNodes[0]) {
		con.appendChild(div);
	} else {
		con.insertBefore(div,con.childNodes[0]);
	}
	
	anim.animate();
}

function getWork() {
	document.getElementById('myTicketsContent').innerHTML = "Loading...";
	YAHOO.util.Connect.setForm('formGetWork');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {success: doGetWork,failure: handleFailure});
}

function getTeamWorkloads() {
	var limit = 5;
	
	var frm = document.formGetWork;
	if(null != frm) {
		limit = document.formGetWork.getwork_limit.value;
	}
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_team_loads&getwork_limit=' + limit, {success: doGetTeamWorkloads,failure: handleFailure});
}
function doCheckTicketFlag(o) {
	id = o.argument.id;
	thid = o.argument.thid;
	cb = o.argument.cb;
	
	if(0 == o.responseText.length || o.responseText == ' ') { // clear
		cb(id,thid);
	} else { // flagged by other
		getWorkId(id);
	}
}
function checkTicketFlag(id,callback,thid) {
	var div = document.getElementById('getworkopts' + id);
	if(null == div) return;
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_checkflag&id='+id, {success: doCheckTicketFlag,failure: handleFailure,argument:{id:id,cb:callback,thid:thid}});
}
function getWorkId(id) {
	var div = document.getElementById('getwork' + id);
	if(null == div) return;
	div.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_item&id='+id, {success: doGetWorkItem,failure: handleFailure,argument:{id:id}});
}
function getSuggestedTickets() {
	document.getElementById('suggestedTicketsContent').innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_suggested', {success: doSuggestedTickets,failure: handleFailure});
}
function getMyTickets() {
	document.getElementById('myTicketsContent').innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_my', {success: doMyTickets,failure: handleFailure});
}
function getWorkWorkflow(id) {
	var div = document.getElementById('getworkopts' + id);
	if(null == div) return;
	div.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_workflow&id='+id, {success: doGetWorkWorkflow,failure: handleFailure,argument:{id:id}});
}
function parseArrayKey(idString) {
	idString = idString.replace(']','');
	idSplit = idString.split('[');
	var id = idSplit[1];
	return id;
}
function setDueCal(e) {
	var date = this.getSelectedDates()[0];
	var id = parseArrayKey(this.id);
	var frm = document.getElementById('getworkform' + id);
	if(frm == null) return;
	month = date.getMonth() + 1;
	day = date.getDate();
	year = date.getFullYear();
	var dateString = month + '/' + day + '/' + year + ' 12:00AM';
	frm.ticket_due.value = dateString;
}
function setDelayCal(e) {
	var date = this.getSelectedDates()[0];
	var id = parseArrayKey(this.id);
	var frm = document.getElementById('getworkform' + id);
	if(frm == null) return;
	month = date.getMonth() + 1;
	day = date.getDate();
	year = date.getFullYear();
	var dateString = month + '/' + day + '/' + year + ' 12:00AM';
	frm.ticket_delay.value = dateString;
}
var duecal = new Array();
function drawTicketDueCalendar(id) {
	var cal = document.getElementById('getworkduecal' + id);
	if(null != cal) cal.innerHTML = '';
	duecal[id] = new YAHOO.widget.Calendar("duecal[" + id + "]","getworkduecal" + id);
	duecal[id].minDate = new Date();
	duecal[id].onSelect = setDueCal;
	duecal[id].render();
}
var delaycal = new Array();
function drawTicketDelayCalendar(id) {
	var cal = document.getElementById('getworkdelaycal' + id);
	if(null != cal) cal.innerHTML = '';
	delaycal[id] = new YAHOO.widget.Calendar("delaycal[" + id + "]","getworkdelaycal" + id);
	delaycal[id].minDate = new Date();
	delaycal[id].onSelect = setDelayCal;
	delaycal[id].render();
}
function getWorkShowClose(id) {
	var div = document.getElementById('getworkclose' + id);
	if(null == div) return;
	
	if(div.style.display == "block") {
		div.style.display = "none";
	} else {
		div.style.display = "block";
	}
}
function saveWorkflow(id) {
	var div = document.getElementById('getworkform' + id);
	if(null == div) return;
	YAHOO.util.Connect.setForm('getworkform' + id);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=save_workflow', {success: doSaveWorkflow,failure: handleFailure,argument:{id:id}});
}
function getMonitorEvents() {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_monitor', {success: doMonitor,failure: handleFailure});
}

function clearMonitorEvents() {
   var con = document.getElementById('monitorContent');
   if(null == con) return;
   
   con.innerHTML = "";
}
function clearGetWorkOpts(id) {
   var con = document.getElementById('getworkopts'+id);
   if(null == con) return;
   con.innerHTML = "";
}

function clearGetWorkPreview(id) {
	var div = document.getElementById("getworkpre"+id);
	if(null == div) return;
	div.innerHTML = "";
	div.style.display = "none";
}
function doGetReply(o) {
	var div = document.getElementById("getworkpre"+o.argument.id);
	if(null == div) return;
	div.innerHTML = o.responseText;
}
function doGetComment(o) {
	var div = document.getElementById("getworkpre"+o.argument.id);
	if(null == div) return;
	div.innerHTML = o.responseText;
}
function doGetWorkPreview(o) {
	var div = document.getElementById("getworkpre"+o.argument.id);
	if(null == div) return;
	div.innerHTML = o.responseText;
}
function doGetWorkTake(o) {
}
function doGetWorkRelease(o) {
}
function doGetWorkSpam(o) {
}
function doGetWorkTrash(o) {
}
function doGetWorkClose(o) {
}
function preQuickReply(id,thid) {
	checkTicketFlag(id, quickReply, thid);
}
function preWorkTake(id) {
	checkTicketFlag(id, getWorkTake);
}
function getWorkTake(id) {
	div = document.getElementById('getwork'+id);
	if(null == id || null == div) return;

	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_take&id=' + id, {success: doGetWorkTake,failure: handleFailure});
	getWorkDispose(id);
}

function getWorkRelease(id) {
	div = document.getElementById('getwork'+id);
	if(null == id || null == div) return;

	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_release&id=' + id, {success: doGetWorkRelease,failure: handleFailure});
	getWorkDispose(id);
}

function getWorkReleaseDelay(id) {
	div = document.getElementById('getwork'+id);
	if(null == id || null == div) return;
	
	YAHOO.util.Connect.setForm('frmrelease'+id);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {success: doGetWorkRelease,failure: handleFailure,argument:{id:id}});
	
	getWorkDispose(id);
}

function getWorkSpam(id) {
	div = document.getElementById('getwork'+id);
	if(null == div) return;
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_spam&id=' + id, {success: doGetWorkTake,failure: handleFailure});
	getWorkDispose(id);
}

function getWorkTrash(id) {
	div = document.getElementById('getwork'+id);
	if(null == div) return;
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_trash&id=' + id, {success: doGetWorkTake,failure: handleFailure});
	getWorkDispose(id);
}

function getWorkClose(id) {
	div = document.getElementById('getwork'+id);
	if(null == div) return;
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_set_closed&id=' + id, {success: doGetWorkClose,failure: handleFailure});
	getWorkDispose(id);
}
function quickReplySend(id) {
	div = document.getElementById('getworkpre'+id);
	if(null == div) return;
	YAHOO.util.Connect.setForm('getworkreply'+id);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=getwork_reply_save', {success: doSaveReply,failure: handleFailure,argument: {id:id}});
	
}
function quickCommentSend(id) {
	div = document.getElementById('getworkpre'+id);
	if(null == div) return;
	YAHOO.util.Connect.setForm('getworkcomment'+id);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=getwork_comment_save', {success: doSaveComment,failure: handleFailure,argument: {id:id}});
	
}
function quickReply(id,thid) {
	div = document.getElementById('getworkpre'+id);
	if(null == div) return;

	div.style.display = "block";
	div.innerHTML = "Loading...";
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_reply&id=' + id + "&thid=" + thid, {success: doGetReply,failure: handleFailure,argument: {id:id}});
}
function quickComment(id) {
	div = document.getElementById('getworkpre'+id);
	if(null == div) return;

	div.style.display = "block";
	div.innerHTML = "Loading...";
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_comment&id=' + id, {success: doGetWorkPreview,failure: handleFailure,argument: {id:id}});
}
function getWorkShowPreview(id,thid) {
	div = document.getElementById('getworkpre'+id);
	if(null == div) return;
	
	div.style.display = "block";
	div.innerHTML = "Loading...";
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=getwork_preview&id=' + id + '&thid=' + thid, {success: doGetWorkPreview,failure: handleFailure,argument: {id:id}});
}
function getWorkDispose(id) {
	workcon = document.getElementById('getWorkContent');
	workitem = document.getElementById('getwork' + id);
	
	var deleteElement = function() {
		var el = this.getEl();
		if(null == el || null == el.parentNode) return;
		el.parentNode.removeChild(el);
	}
	
	var attributes = {
      opacity: { from:1, to: 0 }
   }
   
   var anim = new YAHOO.util.Anim('getwork' + id, attributes, 0.5, YAHOO.util.Easing.easeOut);	
   anim.onComplete.subscribe(deleteElement);
	anim.animate();
}

function getWorkCheckAll(bool) {
	checks = document.getElementById('getWorkContent').getElementsByTagName('input');
	for(x=0;x<checks.length;x++) {
		if(checks[x].type=="checkbox") {
			checks[x].checked = bool;
		}
	}
}
function doGetWorkSpellCheck(box) {
	var textbox = document.getElementById(box);
	if(null == textbox) return;
	document.spellform.caller.value = box;
	document.spellform.spellstring.value = textbox.value;
	window.open("", "spellWindow", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=640,height=480');
	document.spellform.submit();
}
