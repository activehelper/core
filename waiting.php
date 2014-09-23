<?php
include_once('import/constants.php');

include('./import/config_database.php');
include('./import/class.mysql.php');
include('./import/config.php');

if (isset($_REQUEST['DOMAINID'])){
  $domainId = (int) $_REQUEST['DOMAINID'];
}

if (!isset($_REQUEST['COMPLETE'])){ $_REQUEST['COMPLETE'] = ''; }
$complete = (bool) $_REQUEST['COMPLETE'];
if ($complete == '') { $complete = false; }

$query = "UPDATE " . $table_prefix . "sessions SET `datetime` = NOW() WHERE `id` = '$guest_login_id'";
$SQL->miscquery($query);

$query = "SELECT `server` FROM " . $table_prefix . "sessions WHERE `id` = '$guest_login_id'";
$row = $SQL->selectquery($query);
if (is_array($row)) {
        $server = $row['server'];
}
header('Content-type: text/html; charset=' . CHARSET);

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';
if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo($livehelp_name); ?></title>
<link href="./style/styles.php?<?php echo('DOMAINID='.$domainId);?>" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--
function loadOfflineSupport() {
        top.document.location.href = 'offline.php?<?php echo('DOMAINID='.$domainId);?>';
}

var timer = setTimeout('loadOfflineSupport()', 15000);

//-->
</script>
</head>
<body text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>">
<div align="center">
  <table border="0" cellpadding="2" cellspacing="2">
    <tr>
      <td><!-- img src="pictures/note.gif" alt="<?php echo($error_label); ?>" width="53" height="57" border="0" ---></td>
      <td><div align="center"><span class="heading"><?php echo($please_note_label); ?></span><br>
          <?php echo($please_wait_heavy_load_label); ?>:</div><br>
          <table border="0" align="center" cellpadding="2" cellspacing="2">
            <tr>
              <td><a href="blank.php?<?php echo('DOMAINID='.$domainId);?>" onClick="clearTimeout(timer);" class="normlink"><?php echo($continue_waiting_label); ?></a></td>
              <td>-</td>
              <td><a href="offline.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?><?php echo('&DOMAINID='.$domainId);?>" target="_top" class="normlink"><?php echo($offline_support_label); ?></a></td>
            </tr>
          </table><br>
          <div align="center"><span class="small"><?php echo($redirecting_label); ?>... </span></div></td>
    </tr>
  </table>
  </div>
</body>
</html>