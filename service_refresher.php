<?php
include_once('import/constants.php');

include('import/config_database.php');
include('import/class.mysql.php');
include('import/config.php');

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

$webCallId = (int) $_REQUEST["webCallId"];
?>
<html>
<head>
 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv='Pragma' content='no-cache'><meta http-equiv='expires' content='0'>
</head>
<body>
<?
echo $query = "SELECT s.service_description FROM " . $table_prefix . "webcall wc, " . $table_prefix . "statuses s WHERE wc.id_webcall = '". ( (int) $webCall_id )."' and s.id_status = wc.status And s.id_service = 4";
$rows = $SQL->selectquery($query);
?>
<script>
parent.update("<?=$rows["service_description"]?>")
function sf() {
        document.location.reload()
        return true
}
setTimeout("sf()", 5000)
</script>
</body>
</html>
