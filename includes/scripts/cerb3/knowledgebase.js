/*
Copyright (c) 2006 WebGroup Media LLC. All rights reserved.
*/

var CerKbWorkflow = function(label) {
	this.label = label;
	
	function handleFailure(o) {
	}
	
	this.addTag = function() {
		
	}
	
	this.removeTag = function(tagId) {
	   var con = document.getElementById('articleWorkflow'+this.label);
	   if(null == con) return;
		con.innerHTML = "Removing...";
		var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=kb_article_remove_tag&id=' + this.label + '&tag=' + tagId, {
				success: function(o) {
					o.argument.caller.onRemoveTag();
				},
				failure: this.handleFailure,
				argument:{caller:this}
			}
		);
	}
	
	this.onRemoveTag = function() {};
	
}

function autoTags(tagDiv,conDiv) {
	// An XHR DataSource
	var myServer = "rpc.php";
	var mySchema = ["\n", "\t"];
	var myDataSource = new YAHOO.widget.DS_XHR(myServer, mySchema);
	myDataSource.responseType = myDataSource.TYPE_FLAT; 
	myDataSource.scriptQueryAppend = "cmd=auto_tag"; 
	
	var myAutoComp = new YAHOO.widget.AutoComplete(tagDiv, conDiv, myDataSource);
	myAutoComp.typeAhead = false;
	myAutoComp.forceSelection = false;
	myAutoComp.delimChar = ","; 
 
	myAutoComp.formatResult = function(oResultItem, sQuery) {
	   var sKey = oResultItem[0];
	   var nQuantity = oResultItem[1];
	   var sKeyQuery = sKey.substr(0, sQuery.length);
	   var sKeyRemainder = sKey.substr(sQuery.length);
	   var aMarkup = ["<div id='ysearchresult' class='searchresult'><div class='searchquery'>",
	       nQuantity,
	       "</div>",
	       sKeyQuery,
	       "",
	       sKeyRemainder,
	       "</div>"];
	   return (aMarkup.join(""));
	}; 
}

var kbResourcePanel;
var kbResourceCategoriesPanel;

function initPopupResource() {
	kbResourcePanel = new YAHOO.widget.Panel("dynamicKbResource", 
		{ 
		width:"500px", 
		fixedcenter: true, 
		constraintoviewport: true, 
		underlay:"shadow", 
		close:true, 
		visible:false, 
		draggable:true} );
		
	kbResourcePanel.render();
	
	kbResourceCategoriesPanel = new YAHOO.widget.Panel("dynamicKbCategories", 
		{ 
		width:"500px", 
		fixedcenter: true, 
		constraintoviewport: true, 
		underlay:"shadow", 
		close:true, 
		visible:false, 
		draggable:true} );
		
	kbResourceCategoriesPanel.render();
}
YAHOO.util.Event.addListener(window, "load", initPopupResource);

function popupResource(id) {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_get_resource&id=' + id, {
			success: function(o) {
				kbResourcePanel.setHeader("<img src=\"includes/images/cerb_fnr_header.jpg\" align=\"absmiddle\">");
				kbResourcePanel.setBody(o.responseText);
				try {
					tinyMCE.idCounter=0;
					tinyMCE.execCommand('mceAddControl', true, 'elm1');
				} catch(e) {}
				getFnrResourceCategoryManager(o.argument.id,'fnrResourceForm');
				getFnrResourceTagManager(o.argument.id,'fnrResourceForm');
				kbResourcePanel.show();
			},
			failure: function(o) {},
			argument:{caller:this,id:id}
		}
	);
}

function fnrResourceSave(id) {
	try {
		tinyMCE.triggerSave(true,true);
	} catch(e) {}
	
	YAHOO.util.Connect.setForm('fnrResourceForm');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=fnr_save_resource', {
			success: function(o) {
				var div = document.getElementById('fnrResourceForm');
				if(null == div) return;
				
				popupResource(o.argument.id);
			},
			failure: function(o) {},
			argument:{caller:this,id:id}
		}
	);
}

function fnrResourceUntag(id) {
	YAHOO.util.Connect.setForm('fnrResourceForm');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=fnr_resource_remove_tags', {
			success: function(o) {
				var div = document.getElementById('fnrResourceForm');
				if(null == div) return;
				
//					popupResource(o.argument.id);
				getFnrResourceTagManager(o.argument.id,'fnrResourceForm');
			},
			failure: function(o) {},
			argument:{caller:this,id:id}
		}
	);
}

function fnrResourceTag(id) {
	YAHOO.util.Connect.setForm('fnrResourceForm');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=fnr_resource_apply_tags', {
			success: function(o) {
				var div = document.getElementById('fnrResourceForm');
				if(null == div) return;
//					popupResource(o.argument.id);
				getFnrResourceTagManager(o.argument.id,'fnrResourceForm');
			},
			failure: function(o) {},
			argument:{caller:this,id:id}
		}
	);
}

function popupResourceCategories() {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_manage_categories', {
			success: function(o) {
				kbResourceCategoriesPanel.setHeader("<img src=\"includes/images/cerb_fnr_header.jpg\" align=\"absmiddle\">");
				kbResourceCategoriesPanel.setBody(o.responseText);
				kbResourceCategoriesPanel.show();
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function getFnrCategoryNew() {
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_get_new_category', {
			success: function(o) {
				var div = document.getElementById('fnrCategoryForm');
				if(null == div) return;
				
				div.innerHTML = o.responseText;
				div.category_name.select();
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function doFnrCategoryNew() {
	var div = document.getElementById('fnrCategoryForm');
	if(null == div) return;
	
	YAHOO.util.Connect.setForm('fnrCategoryForm');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=fnr_do_new_category', {
			success: function(o) {
				var div = document.getElementById('fnrCategoryForm');
				if(null == div) return;
				
				popupResourceCategories();
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function getFnrSuggestions(id) {
	var div = document.getElementById('ticket_display_suggestions');
	if(null == div) return;
	
	div.innerHTML = "Loading...";

	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_get_ticket_suggestions&id=' + id, {
			success: function(o) {
				var div = document.getElementById('ticket_display_suggestions');
				if(null == div) return;
				
				div.innerHTML = o.responseText;	
				
//				popupResource(o.argument.id);
			},
			failure: function(o) {},
			argument:{caller:this,id:id}
		}
	);
}

function getFnrCategoryEdit(id) {
	var div = document.getElementById('fnrCategoryForm');
	if(null == div) return;
	
	div.innerHTML = "Loading...";
	
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_get_edit_category&id=' + id, {
			success: function(o) {
				var div = document.getElementById('fnrCategoryForm');
				if(null == div) return;
				
				div.innerHTML = o.responseText;
				div.category_name.select();
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function doFnrCategoryEdit() {
	var div = document.getElementById('fnrCategoryForm');
	if(null == div) return;
	
	YAHOO.util.Connect.setForm('fnrCategoryForm');
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=fnr_do_edit_category', {
			success: function(o) {
				var div = document.getElementById('fnrCategoryForm');
				if(null == div) return;
				
				popupResourceCategories();
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function doFnrCategoryDelete() {
	var div = document.getElementById('fnrCategoryForm');
	if(null == div) return;
	
	if(!confirm("Are you sure you want to delete this category?\nAny content using this category will be unlinked."))
		return;
	
//	YAHOO.util.Connect.setForm('fnrCategoryForm');
	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_do_delete_category&id=' + div.category_id.value, {
			success: function(o) {
				var div = document.getElementById('fnrCategoryForm');
				if(null == div) return;
				
				popupResourceCategories();
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function getFnrResourcePermalinks(id) {
	var div = document.getElementById('fnr_tab_permalinks');
	if(null == div) return;

	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_get_permalinks&id=' + id, {
			success: function(o) {
				var div = document.getElementById('fnr_tab_permalinks');
				if(null == div) return;
				
				div.innerHTML = o.responseText;
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function getFnrResourceCategoryManager(id,d) {
	var div = document.getElementById('kbResourceCategoryManager');
	if(null == div) return;

	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_get_resource_category_manager&id=' + id + "&div=" + d, {
			success: function(o) {
				var div = document.getElementById('kbResourceCategoryManager');
				if(null == div) return;
				
				div.innerHTML = o.responseText;
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function getFnrResourceTagManager(id,d) {
	var div = document.getElementById('kbResourceTagManager');
	if(null == div) return;

	var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=fnr_get_resource_tag_manager&id=' + id + "&div=" + d, {
			success: function(o) {
				var div = document.getElementById('kbResourceTagManager');
				if(null == div) return;
				
				div.innerHTML = o.responseText;
				autoTags('float_tag_input','float_searchcontainer');
			},
			failure: function(o) {},
			argument:{caller:this}
		}
	);
}

function setFnrResourceCategories(id,frm) {
	var div = document.getElementById('kbResourceCategoryManager');
	if(null == div) return;
	
	YAHOO.util.Connect.setForm(frm);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=fnr_set_resource_categories', {
			success: function(o) {
				var div = document.getElementById('kbResourceCategoryManager');
				if(null == div) return;
				
				getFnrResourceCategoryManager(o.argument.id,frm);
				getFnrResourcePermalinks(o.argument.id);
			},
			failure: function(o) {},
			argument:{caller:this,id:id}
		}
	);
}

function unsetFnrResourceCategories(id,frm) {
	var div = document.getElementById('kbResourceCategoryManager');
	if(null == div) return;
	
	YAHOO.util.Connect.setForm(frm);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'rpc.php?cmd=fnr_unset_resource_categories', {
			success: function(o) {
				var div = document.getElementById('kbResourceCategoryManager');
				if(null == div) return;
				
				getFnrResourceCategoryManager(o.argument.id,frm);
				getFnrResourcePermalinks(o.argument.id);
			},
			failure: function(o) {},
			argument:{caller:this,id:id}
		}
	);
}

function fnrShowTab(tab) {
	try {
		document.getElementById('fnr_tab_view').style.display = 'none';
		document.getElementById('fnr_tab_edit').style.display = 'none';
		document.getElementById('fnr_tab_categories').style.display = 'none';
		document.getElementById('fnr_tab_tags').style.display = 'none';
		document.getElementById('fnr_tab_permalinks').style.display = 'none';

		switch(tab) {
			case 0: // view
				document.getElementById('fnr_tab_view').style.display = 'block';
				break;
			case 1: // edit
				document.getElementById('fnr_tab_edit').style.display = 'block';
				break;
			case 2: // cats
				document.getElementById('fnr_tab_categories').style.display = 'block';
				break;
			case 3: // tags
				document.getElementById('fnr_tab_tags').style.display = 'block';
				break;
			case 4: // links
				document.getElementById('fnr_tab_permalinks').style.display = 'block';
				break;
		}
	
	} catch(e) {}
}
