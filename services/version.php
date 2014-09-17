<?php
include_once('../import/constants.php');

include('../import/config_database.php');
include('../import/class.mysql.php');
include('../import/config.php');
include('../import/version.php');

if (!isset($_REQUEST['WEB'])){ $_REQUEST['WEB'] = ''; }
if (!isset($_REQUEST['WINDOWS'])){ $_REQUEST['WINDOWS'] = ''; }

$current_web_version = htmlspecialchars( (string) $_REQUEST['WEB'], ENT_QUOTES );
$current_windows_version = htmlspecialchars( (string) $_REQUEST['WINDOWS'], ENT_QUOTES );

if ( $current_windows_version == $windows_application_version ) { $result = 'true'; } else { $result = 'false'; }

$charset = 'utf-8';
header('Content-type: text/xml; charset=' . $charset);
echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");
?>
<Version xmlns="urn:LiveHelp" Web="" Windows="<?php echo($result); ?>"/>
