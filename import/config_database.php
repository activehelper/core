<?php


 if (!defined('__CONFIG_DATABASE_INC')) {


 define('__CONFIG_DATABASE_INC', 1);

 define( 'DS', DIRECTORY_SEPARATOR );

 include_once('constants.php');
 include_once('jlhconst.php');

 require_once J_CONF_PATH.DS.'configuration.php';

 $config = new JConfig();

 define("DB_HOST", $config->host);
 define("DB_USER", $config->user);
 define("DB_PASS", $config->password);
 define("DB_NAME", $config->db);

 $table_prefix = 'jos_livehelp_';

 }
?>