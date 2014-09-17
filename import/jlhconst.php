<?php 
 $httpsReq = strtolower($_SERVER['HTTPS']); 
 $protocol = 'http';
 $ssl = 0;
 
 if ($httpsReq == 'on') {
     $protocol = 'https';
     $ssl = 1;
      }
 
 
 
 define("J_HOST",$protocol.'://'.'localhost');
 define("J_DOMAIN_SET_PATH",'F:\dev\htdocs\j15\components\com_activehelper_livehelp\server\domains');
 define("J_DIR_PATH",'/j15/components/com_activehelper_livehelp');
 define("J_CONF_PATH",'F:\dev\htdocs\j15');
 define("J_CONF_SSL",$ssl);
 
 
?>