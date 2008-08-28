/*
Copyright (c) 2006 WebGroup Media LLC. All rights reserved.
*/
function handleWorkflowFailure(o) {
}

// [JAS]: [TODO] Start moving methods/members into these objects
var CerQuickWorkflow = function(label,frm) {
	this.label = label;
	this.frm = frm;
	
	function handleFailure(o) {
	}
	
	this.selectFirst = function() {
		var con = document.getElementById('searchmodes_'+this.label);
		if(null == con) return;
		
		var checks = con.getElementsByTagName('input');
		if(checks.length) {
			checks[0].checked = true;
			checks[0].onclick();
		}
	}
	
	// [JAS]: [TODO] Make ticket specific refresh?  Subclass?
	this.refresh = function() {
	   var con = document.getElementById('workflowSnapshot_'+this.label);
	   if(null == con) return;
		con.innerHTML = "Loading...";
		var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=workflow_snapshot&id=' + this.label, {
				success: function(o) {
				   var con = document.getElementById('workflowSnapshot_'+o.argument.label);
				   if(null == con) return;
					con.innerHTML = o.responseText;
				},
				failure: this.handleFailure,
				argument:{label:this.label}
			});
	}
	
	this.refreshArticleWorkflow = function() {
	   var con = document.getElementById('articleWorkflow'+this.label);
	   if(null == con) return;
		con.innerHTML = "Loading...";
		var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=kb_article_workflow&id=' + this.label, {
				success: function(o) {
				   var con = document.getElementById('articleWorkflow'+o.argument.label);
				   if(null == con) return;
					con.innerHTML = o.responseText;
				},
				failure: this.handleFailure,
				argument:{label:this.label}
			});
	}
	
	this.addResultsToArticle = function() {
		YAHOO.util.Connect.setForm(this.frm);
		var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=kb_article_add_tags', {
				success: function(o) {
					o.argument.caller.refreshArticleWorkflow();
				},
				failure: this.handleFailure,
				argument:{caller:this}
			});
	}
	
	this.setMode = function(mode) {
		if(mode) {
			var div = document.getElementById("quickWorkflowMode0_" + this.label);
			div.style.display = 'none';
			div = document.getElementById("quickWorkflowMode1_" + this.label);
			div.style.display = 'block';
		} else {
			var div = document.getElementById("quickWorkflowMode0_" + this.label);
			div.style.display = 'block';
			div = document.getElementById("quickWorkflowMode1_" + this.label);
			div.style.display = 'none';
		}
	}
	
	this.tagAction = function() {
		var tagDiv = document.getElementById("tag_input_" + this.label);
		if(null == tagDiv) return;

//		YAHOO.util.Connect.setForm(this.frm);
		var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_ticket_tag&id=' + this.label + '&tags=' + escape(tagDiv.value), {
			success: function(o) {
//				o.argument.caller.postResultsAction();
				o.argument.tagDiv.value = '';
				o.argument.tagDiv.focus();
				o.argument.caller.refresh();
				o.argument.caller.postAddTagAction();
			},
			failure: handleWorkflowFailure,
			argument: {caller:this,tagDiv:tagDiv}
		});
	}
	
	this.search = function() {
	   var div = document.getElementById("quickWorkflowResults_" + this.label);
	   var fref = document.getElementById(this.frm);
		if(null == div || null == fref) return;
		div.innerHTML = "Searching...";
		
		// [JAS]: Find the selected category
		var cat = "tag"; // default
		
		if(fref.category.length) {
			for (var i=0; i < fref.category.length; i++) {
		   		if (fref.category[i].checked) {
			      	cat = fref.category[i].value;
		      	}
			}		
		} else {
			cat = fref.category.value;
		}
		
		var q = fref.keyword.value;
		var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=workflow_search&c=' + cat + '&q=' + q, {
				success: function(o) {
				   var div = document.getElementById("quickWorkflowResults_" + o.argument.caller.label);
					if(null == div) return;
					div.innerHTML = o.responseText;
				},
				failure: this.handleFailure,
				argument:{caller:this}
			});
	}
	
	this.resultsAction = function() {
		this.post();
	}
	
	this.postResultsAction = function() {}
	
	this.postAddTagAction = function() {}
	
	this.enterKiller = function() {
		var fref = document.getElementById(this.frm);
		if(null == fref) return;
		//if(null == fref.onsubmit) {
			fref.onsubmit = function() {
				this.find.click();
				return false;
			}
		//}
	}
	
	this.handleEnter = function(f, event) {
		var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
		if (keyCode == 13) {
			var i;
			for (i = 0; i < f.form.elements.length; i++)
				if (f == f.form.elements[i])
					break;
			i = (i + 1) % f.form.elements.length;
			
			// check element name and run search if it was the workflow query box.
			f.form.find.click();

			return false;
		} 
		else
		return true;
	}      
	
	this.post = function() {
		var div = document.getElementById("quickWorkflowResults_" + this.label);
		var fref = document.getElementById(this.frm);
		
		if(null == div || null == fref) return;
		fref.quickworkflow_string.value = '';
		var checks = div.getElementsByTagName("input");
		if(null == checks) return;
		
		for(x=0;x<checks.length;x++) {
			if(checks[x].type=="checkbox" && true == checks[x].checked) {
				if(''!=fref.quickworkflow_string.value) fref.quickworkflow_string.value += "||";
				fref.quickworkflow_string.value += checks[x].value;
			}
		}
		
		if(''!=fref.quickworkflow_string.value) {
//			div.innerHTML = 'Setting workflow...';
			YAHOO.util.Connect.setForm(this.frm);
			var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {
				success: function(o) {
					o.argument.caller.postResultsAction();
				},
				failure: handleWorkflowFailure,
				argument: {caller:this}
			});
		}
		
//		alert(fref.quickworkflow_string.value);
	}
}

function doGetWorkflowSnapshot(id) {
   var con = document.getElementById('workflowSnapshot_'+id);
   if(null == con) return;
	con.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=workflow_snapshot&id=' + id, {success: cbGetWorkflowSnapshot,failure: handleFailure,argument:{id:id}});
}
function cbGetWorkflowSnapshot(o) {
   var con = document.getElementById('workflowSnapshot_'+o.argument.id);
   if(null == con) return;
	con.innerHTML = o.responseText;
}
//function doQuickWorkflowSearch(label,frm) {
//   var div = document.getElementById("quickWorkflowResults_" + label);
//	if(null == div) return;
//	div.innerHTML = "Searching...";
//	YAHOO.util.Connect.setForm(frm);
//	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=workflow_search', {success: cbQuickWorkflowResults,failure: handleWorkflowFailure,argument: {label:label,frm:frm} });
//}
//function cbQuickWorkflowResults(o) {
//   var div = document.getElementById("quickWorkflowResults_" + o.argument.label);
//	if(null == div) return;
//	div.innerHTML = o.responseText;
//}
//function doPostQuickWorkflow(ticketId) {
//	var div = document.getElementById("quickWorkflowResults_" + ticketId);
//	var fref = document.getElementById(this.frm);
//	
//	if(null == div || null == fref) return;
//	fref.quickworkflow_string.value = '';
//	var checks = div.getElementsByTagName("input");
//	if(null == checks) return;
//	
//	for(x=0;x<checks.length;x++) {
//		if(checks[x].type=="checkbox" && true == checks[x].checked) {
//			if(''!=fref.quickworkflow_string.value) fref.quickworkflow_string.value += "||";
//			fref.quickworkflow_string.value += checks[x].value;
//		}
//	}
//
//	// [JAS]: If we're sending something.
//	if(''!=fref.quickworkflow_string.value) {
//		div.innerHTML = 'Setting workflow...';
//		YAHOO.util.Connect.setForm(this.frm);
//		var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=workflow_set', {success: cbQuickWorkflowSet,failure: handleWorkflowFailure,argument: {ticketId:ticketId} });
//	}
//}
//function cbQuickWorkflowSet(o) {
//   var div = document.getElementById("quickWorkflowResults_" + o.argument.ticketId);
//	if(null == div) return;
//	div.innerHTML = o.responseText;
//	doGetWorkflowSnapshot(o.argument.ticketId);
//}
function blockQuickWorkflowEnter(id,frm) {
	doQuickWorkflowSearch(id,frm);
	return false;
}
function doDisplayRemoveWorkflow(ticketId,itemType,itemId) {
	var url = 'rpc.php?cmd=workflow_unset&id=' + ticketId + '&type=' + itemType + '&itemId=' + itemId;
	var cObj = YAHOO.util.Connect.asyncRequest('GET', url, {
			success: function(o) {
				doGetWorkflowSnapshot(o.argument.ticketId);
				doPostRemoveTagAction(o.argument.ticketId); // listener
			},
			failure: handleWorkflowFailure,
			argument: {ticketId:ticketId} 
	});
}

var doPostRemoveTagAction = function(id) {
}

//function doQuickWorkflowEnterKiller(lbl,frm) {
//	fref.onsubmit = function() {
//		doQuickWorkflowSearch(lbl,fref.name);
//		return false;
//	}
//	if(null!=fref.cmd) {
//		fref.cmd.value = "workflow_search";
//	}
//}