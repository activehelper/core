<?php
include_once('import/constants.php');

$_REQUEST['REFERER'] = !isset( $_REQUEST['REFERER'] ) ? '' : (string) $_REQUEST['REFERER'];
$_REQUEST['URL'] = !isset( $_REQUEST['URL'] ) ? '' : (string) $_REQUEST['URL'];
$_REQUEST['SERVER'] = !isset( $_REQUEST['SERVER'] ) ? '' : htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES );
$_REQUEST['TITLE'] = !isset( $_REQUEST['TITLE'] ) ? '' : htmlspecialchars( (string) $_REQUEST['TITLE'], ENT_QUOTES );
$_REQUEST['DEPARTMENT'] = !isset( $_REQUEST['DEPARTMENT'] ) ? '' : htmlspecialchars( (string) $_REQUEST['DEPARTMENT'], ENT_QUOTES );
$_REQUEST['ERROR'] = !isset( $_REQUEST['ERROR'] ) ? '' : htmlspecialchars( (string) $_REQUEST['ERROR'], ENT_QUOTES );

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

$installed = false;

include('./import/constants.php');
$database = include($install_path . $install_directory . '/import/config_database.php');
if ($database) {
        include($install_path . $install_directory . '/import/block_spiders.php');
        include($install_path . $install_directory . '/import/class.mysql.php');
        $installed = include($install_path . $install_directory . '/import/config.php');
} else {
        $installed = false;
}

if ($installed == false) {
        header('Location: offline.php');
        exit();
}


header('Content-type: text/html; charset=' . CHARSET);

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';

if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}

header("Content-Type: text/html; charset=uft-8");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo($livehelp_name); ?></title>
<style type="text/css">
<!--
.background {
        background-repeat: no-repeat;
        background-position: right top;
        margin-left: 0px;
        margin-top: 0px;
}
.header-background {
        background-image: url(./pictures/eserver_chat_header_bg.gif);
        background-repeat: no-repeat;
        background-position: left top;
}

.proper{
border-left:1px solid #B0BEC7;
border-right:1px solid #B0BEC7;
border-bottom:1px solid #B0BEC7;
width: 469px;
border:1px solid #91A7B4;
padding: 15px;
}
-->
</style>
<link href="./style/styles.php?<?php echo('DOMAINID='. ( (int) $domain_id ) ); ?>" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--

function disableForm() {

        document.login.Submit.disabled = true;
        return true;
}

//-->
</script>
</head>
<body text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>" class="background">
<table class="proper">
<tr>
<td>
<div align="left">
    <p>
    <?php echo($welcome_to_label); ?> <?php echo($site_name); ?>, <?php echo($our_live_help_label); ?><br>
    <?php echo($also_send_message_label); ?>.
    </p>
    <p><span><strong> <?php echo($cookies_error_label); ?></strong></span><strong><br>
      </strong><?php echo($cookies_enable_label); ?></p>
    <p> <?php echo($cookies_else_label); ?></p>

  <p class="small"><?php echo($velaio_copyright_label); ?></p>
</div>
</td>
</tr>
</table>
</body>
</html>
