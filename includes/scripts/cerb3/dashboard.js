/*
Copyright (c) 2006, WebGroup Media, LLC.
*/
function handleFailure(o) {
//	document.getElementById('getWorkContent').innerHTML = "Failed!";
	// [TODO]: Log, report
}
function doGetDashboardLoads(dashId) {
   var con = document.getElementById('dashboardLoads');
   if(null == con) return;
	con.innerHTML = "Loading...<br>";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=dashboard_loads&dash_id=' + dashId, {success: cbGetDashboardLoads,failure: handleFailure});
}
function cbGetDashboardLoads(o) {
   var con = document.getElementById('dashboardLoads');
   if(null == con) return;
	con.innerHTML = o.responseText;
}
function changeMassAction(slot,opt) {
	resetMassActions(slot);
	
	switch(opt) {
		default:
			toggleDivInline(slot + '_mass_' + opt,1);
			toggleDivInline(slot + '_mass_commit',1);
			break;
	}
}
function resetMassActions(slot) {
	toggleDivInline(slot + '_mass_status',0);
	toggleDivInline(slot + '_mass_priority',0);
	toggleDivInline(slot + '_mass_queue',0);
	toggleDivInline(slot + '_mass_due',0);
	toggleDivInline(slot + '_mass_spam',0);
	toggleDivInline(slot + '_mass_waiting',0);
	toggleDivInline(slot + '_mass_blocked',0);
	toggleDivInline(slot + '_mass_merge',0);
	toggleDivInline(slot + '_mass_flag',0);
	toggleDivInline(slot + '_mass_tag',0);
	toggleDivInline(slot + '_mass_workflow',0);
	
	toggleDivInline(slot + '_mass_commit',0);
}
function addMassAction(slot,frm,v) {
	var actIdx = frm.mass_action.selectedIndex;
	var act = frm.mass_action.options[actIdx].value;
	var actStr = frm.mass_action.options[actIdx].text;
	
	switch(act) {
		case "due":
		case "merge":
			nukeDupeMassActions(frm,act);
			_addMassAction(frm, actStr + ' ' + v.value, act + "__" + v.value);
			break;
		
		case "tag":
			_addMassAction(frm, actStr + ' ' + v.value, act + "__" + escape(v.value));
			break;
			
		case "workflow":
//			nukeDupeMassActions(frm,act);
			var wo = frm.workflow_mode;
			for(var x=0;x<wo.length;x++) {
				if(wo[x].checked) {
					var mode = wo[x].value;
					break;
				}
			}
		
			if(mode==1) { // add
				actStr = "Add";
				actPrefix = "w+";
			} else { // remove
				actStr = "Remove";
				actPrefix = "w-";
			}
			
			var addDiv = document.getElementById(slot + '_mass_' + act);
			if(null == addDiv) break;
			
			var addWorkflow = addDiv.getElementsByTagName('input');
			if(null == addWorkflow) break;
			
			for(x=0;x<addWorkflow.length;x++) {
				if(addWorkflow[x].type == "checkbox" && addWorkflow[x].name == "workflow[]" && addWorkflow[x].checked == true) {
					var valStr = addWorkflow[x].title;
					var val = addWorkflow[x].value;
					_addMassAction(frm, actStr + ' ' + valStr, actPrefix + "__" + val);
				}
			}
			
			break;
			
		default:
			var valIdx = v.selectedIndex;
			var val = v.options[valIdx].value;
			var valStr = v.options[valIdx].text;
			
			nukeDupeMassActions(frm,act);
			_addMassAction(frm, actStr + ' ' + valStr, act + "__" + val);
			break;
	}
}
function _addMassAction(frm,lbl,val) {
	var opt = new Option(lbl,val);
	frm.mass_commit.options[frm.mass_commit.options.length] = opt;
}
function removeMassAction(frm) {
	var actIdx = frm.mass_commit.selectedIndex;
	frm.mass_commit.options[actIdx] = null;
}
function commitMassActions(frm) {
	buildMassActionList(frm);
	frm.submit();
}
function buildMassActionList(frm) {
	var opts = frm.mass_commit.options;
	var optLen = opts.length - 1;
	var str = "";
	
	for(x=0;x<=optLen;x++) {
		str += opts[x].value;
		if(x != optLen) str += "||";
	}
	
//	alert(str);
	frm.mass_commit_list.value = str;
	return true;
}
function nukeDupeMassActions(frm,tag) {
	var opts = frm.mass_commit.options;
	var optLen = opts.length - 1;
	var tagLen = tag.length;
	
	for(x=optLen;x>=0;x--) {
		if(opts[x].value.substr(0,tagLen) == tag) {
			frm.mass_commit.options[x] = null;
		}
	}
}
function cancelMassAction(frm,slot) {
	clearMassActions(frm);
	frm.mass_action.selectedIndex = 0;

	resetMassActions(slot);
	
	var div = document.getElementById(slot + '_mass_commit');
	if(null != div) div.style.display = 'none';
}
function clearMassActions(frm) {
	var opts = frm.mass_commit.options;
	var optLen = opts.length - 1;
	
	for(x=optLen;x>=0;x--) {
		frm.mass_commit.options[x] = null;
	}
}
function echoMassAction(frm) {
	buildMassActionList(frm);
}