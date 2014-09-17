<?php 
include_once('../import/constants.php');

if (isset($_REQUEST['DOMAINID'])){
  $domain_id = (int) $_REQUEST['DOMAINID'];
}

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
$database = include($install_path . $install_directory . '/import/config_database.php');
if ($database) {
        include($install_path . $install_directory . '/import/block_spiders.php');
        include($install_path . $install_directory . '/import/class.mysql.php');
        $installed = include($install_path . $install_directory . '/import/config.php');
} else {
        $installed = false;
}

if ($installed == false) {
        header('Location: ' . $install_directory . '/style/styles_default.php');
}
header('Content-type: text/css');
?>


<!--
div, p, td {
        font-family: <?php echo($font_type); ?>;
        font-size: <?php echo($font_size); ?>;
        color: <?php echo($font_color); ?>;
}

a.normlink:link, a.normlink:visited, a.normlink:active {
        color: <?php echo($font_link_color); ?>;
        text-decoration: none;
        font-family: <?php echo($font_type); ?>;
        border-bottom-width: 0.05em;
        border-bottom-style: solid;
        border-bottom-color: #CCCCCC;
}
a.normlink:hover {
        color: <?php echo($font_link_color); ?>;
        text-decoration: none;
        font-family: <?php echo($font_type); ?>;
        border-bottom-width: 0.05em;
        border-bottom-style: solid;
        border-bottom-color: <?php echo($font_link_color); ?>;
}
.heading {
        font-family: <?php echo($font_type); ?>;
        font-size: 16px;
}
.small {
        font-family: <?php echo($font_type); ?>;
        font-size: 10px;
}
.headingusers {
        font-family: <?php echo($font_type); ?>;
        font-size: 18px;
}
.smallusers {
        font-family: <?php echo($font_type); ?>;
        font-size: 10px;
        color: #CBCBCB;
}
.message {
        font-family: <?php echo($chat_font_type); ?>;
        font-size: <?php echo($guest_chat_font_size); ?>;
}
a.message:link, a.message:visited, a.message:active {
        color: <?php echo($font_link_color); ?>;
        text-decoration: none;
        font-family: <?php echo($chat_font_type); ?>;
        font-size: <?php echo($guest_chat_font_size); ?>;
        border-bottom-width: 0.05em;
        border-bottom-style: solid;
        border-bottom-color: #CCCCCC;
}
a.message:hover {
        color: <?php echo($font_link_color); ?>;
        text-decoration: none;
        font-family: <?php echo($chat_font_type); ?>;
        font-size: <?php echo($guest_chat_font_size); ?>;
        border-bottom-width: 0.05em;
        border-bottom-style: solid;
        border-bottom-color: <?php echo($font_link_color); ?>;
}
a.tooltip {
        position: relative;
        font-family: <?php echo($font_type); ?>;
        font-size: 10px;
        z-index: 100;
        color: #000000;
        text-decoration: none;
        border-bottom-width: 0.05em;
        border-bottom-style: dashed;
        border-bottom-color: #CCCCCC;
}
a.tooltip:hover {
        z-index: 150;
        background-color: #FFFFFF;
}
a.tooltip span {
        display: none
}
a.tooltip:hover span {
    display: block;
    position: absolute;
    top: 15px;
        left: -100px;
        width: 175px;
        padding: 5px;
        margin: 10px;
    border: 1px dashed #339;
    background-color: #E8EAFC;
        color: #000000;
    text-align: center
}

//-->
