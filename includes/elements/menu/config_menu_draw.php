<table width="200" border="0" cellspacing="0" cellpadding="3" class="table_orange">
      <tr class="bg_orange">
			<td align="center" nowrap="nowrap" class="text_title_white">
        	<?php echo $menuTitle; ?>
			</td>
      </tr>
      <?php
      $menuRow = 0;
      if(is_array($menuLinks))
      foreach($menuLinks as $menuLink) {
      	$menuRowBgColor = ($menuRow % 2) ? "#E8F3FF" : "#FFFFFF";
      ?>
      <tr bgcolor="<?php echo $menuRowBgColor; ?>">
	      <td valign="top">
	        	<img alt="Menu Item" src="<?php echo $menuLink->image; ?>" border="0" align="middle"> 
	        	<a href="<?php echo $menuLink->link; ?>" class="link_ticket_cmd"><?php echo $menuLink->text; ?></a><br />
	         <span class="box_text"><?php echo $menuLink->subtext; ?></span>
      	</td>
      </tr>
      <?php
      	$menuRow++; 
     	}
     	?>
</table>