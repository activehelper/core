<?php                
include_once('constants.php');

?>
<a href="<?php echo($site_address); ?><?php echo $install_directory; ?>/index.php" target="_blank" onclick="openLiveHelp(); closeInfo(); return false"><img src="<?php echo($site_address); ?><?php echo $install_directory; ?>/import/status.php" id="LiveHelpStatus" name="LiveHelpStatus" border="0" onmouseover="openInfo(this, event);" onmouseout="closeInfo();"/></a>
