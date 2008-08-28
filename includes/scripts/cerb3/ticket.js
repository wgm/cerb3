/*
Copyright (c) 2006 WebGroup Media LLC. All rights reserved.
*/
function handleFailure(o) {
//	document.getElementById('getWorkContent').innerHTML = "Failed!";
	// [TODO]: Log, report
}
function doSaveCreateTicket(o) {
   var con = document.getElementById('divCreateTicket');
	con.innerHTML = '';
	var id = o.responseText;
	if(id) {
		document.location = "display.php?ticket=" + id;
	}
}
function clearCreateTicket() {
	var div = document.getElementById("divCreateTicket");
	if(null == div) return;
	div.innerHTML = "";
}
function doGetCreateTicket(o) {
	var div = document.getElementById("divCreateTicket");
	if(null == div) return;
	div.innerHTML = o.responseText;
	
	// An XHR DataSource
	var myServer = "rpc.php";
	var mySchema = ["\n", "\t"];
	var myDataSource = new YAHOO.widget.DS_XHR(myServer, mySchema);
	myDataSource.responseType = myDataSource.TYPE_FLAT; 
	myDataSource.scriptQueryAppend = "cmd=auto_queue_addresses"; 
	
	var myAutoComp = new YAHOO.widget.AutoComplete('nt_to','searchcontainer', myDataSource);
	myAutoComp.typeAhead = true;
	myAutoComp.forceSelection = false;
 
	myAutoComp.formatResult = function(oResultItem, sQuery) {
	   var sKey = oResultItem[0];
	   var nQuantity = oResultItem[1];
	   var sKeyQuery = sKey.substr(0, sQuery.length);
	   var sKeyRemainder = sKey.substr(sQuery.length);
	   var aMarkup = ["<div id='ysearchresult' class='searchresult'><div class='ysearchquery' class='searchquery'>",
	       nQuantity,
	       "</div><span style='font-weight:bold'>",
	       sKeyQuery,
	       "</span>",
	       sKeyRemainder,
	       "</div>"];
	   return (aMarkup.join(""));
	}; 
	
}
function createTicket() {
	div = document.getElementById('divCreateTicket');
	if(null == div) return;

	div.style.display = "block";
	div.innerHTML = "Loading...";
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=create_ticket', {success: doGetCreateTicket,failure: handleFailure});
}
function createTicketWithFrom(e) {
	div = document.getElementById('divCreateTicket');
	if(null == div) return;

	div.style.display = "block";
	div.innerHTML = "Loading...";
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=create_ticket&email=' + e, {success: doGetCreateTicket,failure: handleFailure});
}
function createTicketSend() {
	frm = document.getElementById('reply_0');
	if(null == frm) return false;
	
	if(frm.nt_from.value.length == 0) {
		alert("Error: You must provide a valid From address.");
		return false;
	}
	if(frm.nt_subject.value.length == 0) {
		alert("Error: You must provide a valid Subject.");
		return false;
	}
	if(frm.nt_body.value.length == 0) {
		alert("Error: You must provide a message body.");
		return false;
	}
	
	return true;
	
//	YAHOO.util.Connect.setForm('reply_0');
//	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=create_ticket_save', {success: doSaveCreateTicket,failure: handleFailure});
	
}
function ticketAddAttach(threadId) {
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
function ticketReplyToolTip(id,txt) {
   var con = document.getElementById('replytip' + id);
   if(null == con) return;
   
   if(0 == txt.length) {
   	con.innerHTML = "";
   	con.style.visibility = "hidden";
   } else {
	   con.innerHTML = txt;
	   con.style.visibility = "visible";
   }
}
function ticketTemplate(id,fill) { 
	window.open("update_show_templates.php?ticket_id=" + id + "&fill=" + fill, "wdwTemplate", "fullscreen=no,toolbar=no,status=no,menubar=no,scrollbars=yes,resizable=yes,directories=no,location=no,width=620,height=500");
}
function ticketSpellCheck(box) {
	var textbox = document.getElementById(box);
	if(null == textbox) return;
	document.spellform.caller.value = box;
	document.spellform.spellstring.value = textbox.value;
	window.open("", "spellWindow", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=640,height=480');
	document.spellform.submit();
}
function doTicketGetSig(o) {
   var con = document.getElementById(o.argument.fill);
	if(null == con) return;
	con.value = con.value += o.responseText;
}
function ticketGetSig(fill) {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=get_signature', {success: doTicketGetSig,failure: handleFailure,argument: {fill:fill} });
}
function runScheduledTasks() {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'cron.php', {success: function(o){},failure:function(o){}});
}