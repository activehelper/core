<?php

include_once('import/config_database.php');
include_once('import/class.mysql.php');
include_once('import/config.php');
include_once('import/block_spiders.php');
require_once('import/jlhconst.php');

if (isset($_REQUEST['DOMAINID'])){
  $domainId = (int) $_REQUEST['DOMAINID'];
}


// Find total guest visitors that are pending within the selected department
$query = "SELECT `department` FROM " . $table_prefix . "sessions WHERE `id` = '" . ( (int) $guest_login_id ) . "'";
$row = $SQL->selectquery($query);
if (is_array($row)) {
        $department = $row['department'];
        $query = "SELECT count(`id`) FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND `active` = '0' AND `department` LIKE '%$department%'";
}
else {
        $query = "SELECT count(`id`) FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND `active` = '0'";
}
$row = $SQL->selectquery($query);
if (is_array($row)) {
        $users_online = $row['count(`id`)'];
}
else {
        $users_online = '1';
}


header("Content-type: text/html; charset=utf-8");

$language = substr(LANGUAGE_TYPE,0,2); 
$language_file = './i18n/' . $language . '/lang_guest_' . $language . '.php';


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

<style type="text/css">
<!--
.background {
        background-image: url(./i18n/<?php echo($language); ?>/pictures/connecting.gif);
        background-repeat: no-repeat;
        background-position: center center;
}
-->
</style>

<style type="text/css">body {margin: 0;padding: 0;height: 220px;}/** * * Horizontal Scrollbar * */.myScrollbarH {  position:absolute;  z-index:100;  height:7px;  bottom:1px;  left:2px;  right:7px}.myScrollbarH > div {  height:100%;}/** * * Vertical Scrollbar * */.myScrollbarV {  position:absolute;  z-index:100;  width:7px;bottom:7px;top:2px;right:1px}.myScrollbarV > div {  width:100%;}/** * * Both Scrollbars * */.myScrollbarH > div,.myScrollbarV > div {  position:absolute;  z-index:100;  /* The following is probably what you want to customize  -webkit-box-sizing:border-box;  -moz-box-sizing:border-box;  -o-box-sizing:border-box;  box-sizing:border-box;    border-width:3px;  -webkit-border-image:url(scrollbar.png) 6 6 6 6;  -moz-border-image:url(scrollbar.png) 6 6 6 6;  -o-border-image:url(scrollbar.png) 6 6 6 6;  border-image:url(scrollbar.png) 6 6 6 6;  */    	background-color: #ccc;	border-radius: 5px;}#wrapper {	position:absolute; z-index:1;	top:0; bottom:0; left:0;	width:100%;	overflow:auto;}#scroller {	position:relative;/*	-webkit-touch-callout:none; */	-webkit-tap-highlight-color: rgba(0,0,0,0);	float:left;	width:100%;	padding:0;}#scroller tr {	position:relative;	list-style:none;	padding:0;	margin:0;	width:100%;	text-align:left;}#scroller tr td {	padding:0 10px;}</style>

<script type="text/javascript" src="<?php echo J_DIR_PATH; ?>/server/js/iscroll.js"></script><script type="text/javascript">	var myScroll;	function load_myScroll() {		myScroll = new iScroll('wrapper', { scrollbarClass: 'myScrollbar' });	}	document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);	// document.addEventListener('DOMContentLoaded', loaded, false);</script>

</head>
<body bgcolor="#FFFFFF" class="background">
<div align="center">
  <table width="100%" border="0" cellspacing="2" cellpadding="2">
    <tr>
      <td align="center"><?php echo($thank_you_patience_label); ?></td>
    </tr>
    <tr>
      <td height="76">&nbsp;</td>
    </tr>
    <tr>
      <td align="center"><div align="right"><span class="small"><?php echo($currently_label . ' ' . $users_online . ' ' . $users_waiting_label); ?>. [<a href="#" class="normlink" onClick="top.displayFrame.displayContentsFrame.location.reload(true);"><?php echo($refresh_label); ?></a>] </span></div></td>
    </tr>
  </table>
</div>
</body>
</html>
