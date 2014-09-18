<?php
include_once('import/constants.php');
if (!isset($_SERVER['DOCUMENT_ROOT'])){ $_SERVER['DOCUMENT_ROOT'] = ''; }
if (!isset($_REQUEST['TITLE'])){ $_REQUEST['TITLE'] = ''; }
if (!isset($_REQUEST['URL'])){ $_REQUEST['URL'] = ''; }
if (!isset($_REQUEST['INITIATE'])){ $_REQUEST['INITIATE'] = ''; }
if (!isset($_REQUEST['REFERRER'])){ $_REQUEST['REFERRER'] = ''; }
if (!isset($_REQUEST['WIDTH'])){ $_REQUEST['WIDTH'] = ''; }
if (!isset($_REQUEST['HEIGHT'])){ $_REQUEST['HEIGHT'] = ''; }
if (!isset($_REQUEST['COOKIE'])){ $_REQUEST['COOKIE'] = ''; }

if (isset($_SERVER['PATH_TRANSLATED']) && $_SERVER['PATH_TRANSLATED'] != '') { $env_path = $_SERVER['PATH_TRANSLATED']; } else { $env_path = $_SERVER['SCRIPT_FILENAME']; }
$full_path = str_replace("\\\\", "\\", $env_path);
$livehelp_path = $_SERVER['PHP_SELF'];
if (strpos($full_path, '/') === false) { $livehelp_path = str_replace("/", "\\", $livehelp_path); }
$pos = strpos($full_path, $livehelp_path);
if ($pos === false) {
        $install_path = $full_path;
}
else {
        $install_path = substr($full_path, 0, $pos);
}

include($install_path . $install_directory . '/import/config_database.php');
include($install_path . $install_directory . '/import/class.mysql.php');
include($install_path . $install_directory . '/import/config.php');

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
<style type="text/css">
<!--
.background {
 /*       background-image: url(./i18n/<?php echo LANGUAGE_TYPE; ?>/pictures/background.gif);
   */     background-repeat: no-repeat;
        background-position: right top;
        margin-left: 0px;
        margin-top: 0px;
}
-->
</style>
</head>
<body bgcolor="<?php echo($background_color); ?>" text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>" class="background">
<div align="center"><br>
<h2><?php echo $Offline_msg_from_email; ?></h2>
</div>
</body>
</html>
