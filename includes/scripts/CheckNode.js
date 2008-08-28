/*
CheckNode is Adapted from Yahoo's TextNode by WebGroup Media, LLC.
*/
YAHOO.widget.CheckNode = function(oData, oParent, expanded) {
	if (oParent) {
		this.init(oData, oParent, expanded);
		this.setUpLabel(oData);
	}
};

YAHOO.widget.CheckNode.prototype = new YAHOO.widget.Node();

/**
 * The CSS class for the label href.  Defaults to ygtvlabel, but can be
 * overridden to provide a custom presentation for a specific node.
 *
 * @type string
 */
YAHOO.widget.CheckNode.prototype.labelStyle = "ygtvlabel";

/**
 * The derived element id of the label for this node
 *
 * @type string
 */
YAHOO.widget.CheckNode.prototype.labelElId = null;

/**
 * The text for the label.  It is assumed that the oData parameter will
 * either be a string that will be used as the label, or an object that
 * has a property called "label" that we will use.
 *
 * @type string
 */
YAHOO.widget.CheckNode.prototype.label = null;

/**
 * Sets up the node label
 *
 * @param oData string containing the label, or an object with a label property
 */
YAHOO.widget.CheckNode.prototype.setUpLabel = function(oData) {
	if (typeof oData == "string") {
		oData = { label: oData };
	}
	this.label = oData.label;

	if(oData.checkName) {
		this.checkName = oData.checkName;
	}
	
	if(oData.checkValue) {
		this.checkValue = oData.checkValue;
	}

	if(oData.isChecked) {
		this.isChecked = oData.isChecked;
	}
	
	this.labelElId = "ygtvlabelel" + this.index;
};

/**
 * Returns the label element
 *
 * @return {object} the element
 */
YAHOO.widget.CheckNode.prototype.getLabelEl = function() {
	return document.getElementById(this.labelElId);
};

// overrides YAHOO.widget.Node
YAHOO.widget.CheckNode.prototype.getNodeHtml = function() {
	var sb = new Array();

	sb[sb.length] = '<table border="0" cellpadding="0" cellspacing="0">';
	sb[sb.length] = '<tr>';

	for (i=0;i<this.depth;++i) {
		// sb[sb.length] = '<td class="ygtvdepthcell">&nbsp;</td>';
		sb[sb.length] = '<td class="' + this.getDepthStyle(i) + '">&nbsp;</td>';
	}

	var getNode = 'YAHOO.widget.TreeView.getNode(\'' +
					this.tree.id + '\',' + this.index + ')';

	sb[sb.length] = '<td';
	sb[sb.length] = ' id="' + this.getToggleElId() + '"';
	sb[sb.length] = ' class="' + this.getStyle() + '"';
	if (this.hasChildren(true)) {
		sb[sb.length] = ' onmouseover="this.className=';
		sb[sb.length] = getNode + '.getHoverStyle()"';
		sb[sb.length] = ' onmouseout="this.className=';
		sb[sb.length] = getNode + '.getStyle()"';
	}
	sb[sb.length] = ' onclick="javascript:' + this.getToggleLink() + '">&nbsp;';
	sb[sb.length] = '</td>';
	sb[sb.length] = '<td>';
	sb[sb.length] = '<label><input type="checkbox" name="' + this.checkName + '" value="' + this.checkValue + '" ' + ((this.isChecked) ? "checked" : "") + '>';
	sb[sb.length] = this.label;
	sb[sb.length] = '</label>';
	sb[sb.length] = '</td>';
	sb[sb.length] = '</tr>';
	sb[sb.length] = '</table>';

	return sb.join("");
};

