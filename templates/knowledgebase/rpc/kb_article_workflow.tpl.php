<table border="0" cellpadding="0" cellspacing="0" class="table_green" width="100%">
   <tr>
     <td class="bg_green"><table width="100%" border="0" cellspacing="0" cellpadding="0">
         <tr>
           <td><span class="text_title_white"><img src="includes/images/icone/16x16/bookmark.gif" alt="A tag" width="16" height="16" /> Tags </span></td>
         </tr>
     </table></td>
   </tr>
   <tr>
     <td bgcolor="#F0F0FF"><span class="box_text">
			<table border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF" width="100%">
				{if empty($article->tags)}
					<tr>
						<td bgcolor="#F0F0FF">No tags applied.</td>
					</tr>
				{/if}
				{foreach from=$article->tags key=tagId item=tag name=tags}
		        <tr>
		          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
		            <tr title="">
		              <td width="0%" align="center" nowrap="nowrap" bgcolor="#FF8000"><img src="includes/images/icone/16x16/folder_network.gif" alt="A folder" width="16" height="16" /></td>
		              <td width="100%" align="left" nowrap="nowrap" class="quickworkflow_item">{$tag->name}</td>
		              <td width="0%" align="right" nowrap="nowrap"><a href="javascript:;" onclick="kbWorkflow.removeTag('{$tagId}');" class="text_ticket_links" title="Remove tag from article"><b>X</b></a></td>
		          </tr>
		          </table></td>
		        </tr>
				{/foreach}
		   </table>
		</td>
	</tr>
</table>

<br>
