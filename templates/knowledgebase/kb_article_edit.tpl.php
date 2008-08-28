{literal}
<!-- TinyMCE -->
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		plugins : "table,save,advhr,advimage,advlink,insertdatetime,preview,searchreplace,contextmenu,paste,directionality,noneditable",
		theme_advanced_buttons1_add_before : "save,separator",
		theme_advanced_buttons1_add : "fontselect,fontsizeselect",
		theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
		theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
		theme_advanced_buttons3_add_before : "tablecontrols,separator",
		theme_advanced_buttons3_add : "advhr,separator,ltr,rtl",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_path_location : "bottom",
		content_css : "example_full.css",
	    plugin_insertdate_dateFormat : "%Y-%m-%d",
	    plugin_insertdate_timeFormat : "%H:%M:%S",
		extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		theme_advanced_resize_horizontal : true,
		theme_advanced_resizing : true
	});
</script>
<!-- /TinyMCE -->
{/literal}

<script type="text/javascript">
{literal}
function checkSave()
	{
		if (document.knowledgebase.kb_title.value == "" ) {
		{/literal}alert("{$smarty.const.LANG_KB_ARTICLE_ERROR_SUMMARY}");{literal}
		document.knowledgebase.kb_title.focus();
		return false;
		}
	}
{/literal}
</script>

<br>
<form method="post" name="knowledgebase" action="knowledgebase.php" onSubmit="return checkSave()">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="form_submit" value="kb_edit_entry">
<input type="hidden" name="kbid" value="{$article->article_id}">
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr> 
    <td class="boxtitle_green_glass" colspan="2">
    	Knowledgebase Article
    </td>
  </tr>
  <tr bgcolor="#CCCCCC" valign="bottom"> 
    <td width="15%" bgcolor="#CCCCCC" height="18" class="cer_maintable_heading" valign="top"> 
      <div align="right" class="cer_maintable_heading">{$smarty.const.LANG_KB_SUMMARY}: </div>
    </td>
    <td width="85%" bgcolor="#DDDDDD" height="18"> 
      <input type="text" name="kb_title" size="75" maxlength="128" value="{$article->article_title|short_escape}" style="width:98%;">
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="top" align="right">{$smarty.const.LANG_KB_ARTICLE_USE}: </td>
    <td width="85%" bgcolor="#DDDDDD">
    	<label><input type="radio" name="kb_public" value="1" {if $article->article_public==1}CHECKED{/if}> {$smarty.const.LANG_WORD_PUBLIC} &nbsp;</label>
    	<label><input type="radio" name="kb_public" value="0" {if $article->article_public==0}CHECKED{/if}> {$smarty.const.LANG_WORD_PRIVATE}</label>
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle">&nbsp;</td>
    <td width="85%" class="cer_maintable_text" bgcolor="#DDDDDD">&nbsp;</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="top" align="right">Article Content:</td>
    <td width="85%" bgcolor="#DDDDDD" valign="top">
      <textarea id="elm1" name="kb_content" rows="15" cols="80" style="width: 100%">{$article->article_content}</textarea>
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text">
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle">&nbsp;</td>
    <td width="85%" class="cer_maintable_text" bgcolor="#DDDDDD">&nbsp;</td>
  </tr>  
  <tr bgcolor="#A5A5A5" class="cer_maintable_text" align="right">
    <td colspan="2" class="cer_maintable_heading">
      <input type="submit" class="cer_button_face" value="&lt;&lt; Back" onclick="javascript:history.back();">
      <input type="submit" class="cer_button_face" name="Submit" value="{$smarty.const.LANG_BUTTON_SAVE}">
    </td>
  </tr>
</table>
</form>
