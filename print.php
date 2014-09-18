<?php
include_once('import/constants.php');

include('./import/config_database.php');
include('./import/class.mysql.php');
include('./import/config.php');

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
<link href="<?php echo $install_directory; ?>/style/styles.php" rel="stylesheet" type="text/css">
</head>
<body bgcolor="<?php echo($background_color); ?>" text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>" onLoad="parent.printFrame.focus();window.print();">
<table width="100%" border="0" align="center">
  <tr>
    <td width="22"><img src="./pictures/fileprint.gif" alt="<?php echo($print_chat_transcript_label); ?>" width="22" height="22"></td>
    <td><em class="heading"><?php echo($print_chat_transcript_label); ?> - <?php echo($support_username); ?></em></td>
  </tr>
</table>
<?php
include('displayer_include.php');
?>
</body>
</html>
