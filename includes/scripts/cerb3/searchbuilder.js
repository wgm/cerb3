/*
Copyright (c) 2006 WebGroup Media LLC. All rights reserved.
*/
function handleFailure(o) {
}
function doGetCriteria(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaOptions');
   if(null == con) return;
	con.innerHTML = "Loading...";
	YAHOO.util.Connect.setForm(lbl + "_searchBuilderForm");
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {success: cbGetCriteria,failure: handleFailure, argument: {lbl:lbl} });
}
function cbGetCriteria(o) {
	var lbl = o.argument.lbl;
   var con = document.getElementById(lbl + "_searchCriteriaOptions");
   if(null == con) return;
	con.innerHTML = o.responseText;
	
	var frm = document.getElementById(lbl + "_searchBuilderForm");
	var criteria = frm.criteria.options[frm.criteria.selectedIndex].value;
	
	// [JAS]: Response-specific behavior
	if(criteria=="tags") {
		autoTags('tag_input_' + lbl,'searchcontainer_' + lbl);
		
		var con = document.getElementById('searchmodes_'+lbl);
		if(null == con) return;
		var checks = con.getElementsByTagName('input');
		if(checks.length) {
			checks[0].checked = true;
			checks[0].onclick();
		}
		
	}
}
function doSearchCriteriaSet(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaForm');
   if(null == con) return;
//	con.innerHTML = "Loading...";
	YAHOO.util.Connect.setForm(lbl + '_searchCriteriaForm');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {success: cbSearchCriteriaSet,failure: handleFailure, argument: {lbl:lbl} });
}
function cbSearchCriteriaSet(o) {
	var lbl = o.argument.lbl;
	doSearchCriteriaList(lbl);

	// [JAS]: [TODO] This is a hack for the workflow search needing to reset submit modes.  This should be cleaned up eventually.
   var frm = document.getElementById(lbl + '_searchCriteriaForm');
   if(null == frm) return;
   if(null != frm && null != frm.cmd && null != frm.criteria) {
   	if(frm.criteria.value=="workflow") {
   		frm.cmd.value = "workflow_search";
   	}
   }
}
function doSearchCriteriaList(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaList');
   if(null == con) return;
	con.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=search_list_criteria&label=' + lbl, {success: cbSearchCriteriaList,failure: handleFailure, argument: {lbl:lbl}});
}
function cbSearchCriteriaList(o) {
	var lbl = o.argument.lbl;
   var con = document.getElementById(lbl + '_searchCriteriaList');
   if(null == con) return;
   con.innerHTML = o.responseText;
}
function doSearchEnterKiller(frm) {
	frm.onsubmit = function() {
		return false;
	}
}
function doSearchCriteriaRemove(lbl,c,p,a) {
   var con = document.getElementById(lbl + '_searchCriteriaList');
   if(null == con) return;
	con.innerHTML = "Saving...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=search_remove_criteria&label=' + lbl + '&criteria=' + c + '&param=' + p + '&arg=' + a, {success: cbSearchCriteriaRemove,failure: handleFailure,argument:{lbl:lbl} });
}
function cbSearchCriteriaRemove(o) {
	var lbl = o.argument.lbl;
	doSearchCriteriaList(lbl);
}
function doSearchCriteriaToggle(lbl,c,p) {
   var con = document.getElementById(lbl + '_searchCriteriaList');
   if(null == con) return;
	con.innerHTML = "Saving...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=search_toggle_criteria&label=' + lbl + '&criteria=' + c + '&param=' + p, {success: cbSearchCriteriaToggle,failure: handleFailure,argument:{lbl:lbl}});
}
function cbSearchCriteriaToggle(o) {
	var lbl = o.argument.lbl;
	doSearchCriteriaList(lbl);
}
function doSearchCriteriaReset(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaList');
   if(null == con) return;
	con.innerHTML = "Clearing...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=search_clear_criteria&label=' + lbl, {success: cbSearchCriteriaReset,failure: handleFailure,argument:{lbl:lbl} });
}
function cbSearchCriteriaReset(o) {
	var lbl = o.argument.lbl;
	doSearchCriteriaList(lbl);
}
function doSearchCriteriaGetSave(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaIO');
   if(null == con) return;
	con.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=search_save_get&label=' + lbl, {success: function(o) {
			var lbl = o.argument.lbl;
	   	var con = document.getElementById(lbl + '_searchCriteriaIO');
		   if(null == con) return;
	   	con.innerHTML = o.responseText;
		},
		failure: handleFailure,
		argument:{lbl:lbl}
	});
}
function doSearchCriteriaGetLoad(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaIO');
   if(null == con) return;
	con.innerHTML = "Loading...";
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=search_load_get&label=' + lbl, {success: function(o) {
			var lbl = o.argument.lbl;
		   var con = document.getElementById(lbl + '_searchCriteriaIO');
		   if(null == con) return;
		   con.innerHTML = o.responseText;
		},
		failure: handleFailure,
		argument:{lbl:lbl}
	});
}
function doSearchCriteriaSave(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaIO');
   if(null == con) return;
//	con.innerHTML = "Saving search...";
	YAHOO.util.Connect.setForm(lbl + "_searchCriteriaIOForm");
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {success: function(o) {
			var lbl = o.argument.lbl;
	   	var con = document.getElementById(lbl + '_searchCriteriaIO');
		   if(null == con) return;
	   	con.innerHTML = o.responseText;
		},
		failure: handleFailure,
		argument:{lbl:lbl}
	});
}
function doSearchCriteriaLoad(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaIO');
   if(null == con) return;
//	con.innerHTML = "Loading search...";
	YAHOO.util.Connect.setForm(lbl + "_searchCriteriaIOForm");
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {success: function(o) {
			var lbl = o.argument.lbl;
	   	var con = document.getElementById(lbl + '_searchCriteriaIO');
		   if(null == con) return;
//	   	con.innerHTML = o.responseText;
	   	doSearchCriteriaList(lbl);
		},
		failure: handleFailure,
		argument:{lbl:lbl}
	});
}
function doSearchCriteriaDelete(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaIO');
   if(null == con) return;
   
	YAHOO.util.Connect.setForm(lbl + "_searchCriteriaIOForm");
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php', {success: function(o) {
			var lbl = o.argument.lbl;
	   	var con = document.getElementById(lbl + '_searchCriteriaIO');
		   if(null == con) return;
	   	doSearchCriteriaList(lbl);
		},
		failure: handleFailure,
		argument:{lbl:lbl}
	});
}
function doSearchCriteriaClearIO(lbl) {
   var con = document.getElementById(lbl + '_searchCriteriaIO');
   if(null == con) return;
	con.innerHTML = "";
}