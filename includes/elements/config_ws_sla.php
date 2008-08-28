<?PHP
require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationSla.class.php");

$sched_handler = new cer_ScheduleHandler();
$sla_handler = new cer_SLA();
$ws_sla = new CerWorkstationSla();
?>

<table width="100%" cellpadding="0" cellspacing="1">
	<tr>
		<td width="1%" nowrap="nowrap" valign="top">
			<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
			  <tr> 
			  	<td class="boxtitle_green_glass" nowrap="nowrap">Service Plans</td>
			  </tr>
			  		<?php
			  		if(is_array($sla_handler->plans)) {
			  		foreach($sla_handler->plans as $plan) { /* @var $plan cer_SLAPlan */
			  		?>
					  <tr bgcolor="#EEEEEE">
					  	<td nowrap="nowrap" class="cer_footer_text">
							<img alt="SLA Plan" src="includes/images/config/icon_sla.gif" width="16" height="16" align="middle"> 
							<a href="<?php echo cer_href("configuration.php?module=ws_sla&pslid=" . intval($plan->sla_id)); ?>" class="cer_footer_text"><?php echo $plan->sla_name; ?></a><BR>
						</td>
						</tr>
					<?php }} else { ?>
					  <tr bgcolor="#EEEEEE">
					  	<td nowrap="nowrap" class="cer_footer_text">
							No SLA plans defined.
						</td>
						</tr>
					<?php } ?>
			</table>
		</td>
		<td width="0%" nowrap="nowrap"><img alt="" src="includes/images/spacer.gif" width="5" height="1"></td>
		<td width="99%" valign="top">
			<?php
			include(FILESYSTEM_PATH . "includes/elements/config_ws_sla_edit.php");
			?>
		</td>
	</tr>
</table>