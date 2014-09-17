<?php

if (!defined('__CONFIG_INC')) {
        define('__CONFIG_INC', 1);

        include_once('constants.php');
        include_once('class.mysql.php');
        include_once('string_util.php');

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



        include($install_path . $install_directory . '/import/version.php');




        include('../import/functions.php');
        if (!get_magic_quotes_gpc()) {
                foreach ($_REQUEST as $key => $value) {
                        $_REQUEST[$key] = addslashes($value);                                             
                }
        } else {
                foreach ($_COOKIE as $key => $value) {
                        $_COOKIE[$key] = stripslashes($value);      
                }
        }
        // Open MySQL Connection
        $SQL = new MySQL();
        $SQL->connect();



        if (!isset($_SERVER['HTTP_REFERER'])){ $_SERVER['HTTP_REFERER'] = ''; }
        if (!isset($_REQUEST['COOKIE'])){ $_REQUEST['COOKIE'] = ''; } else $_REQUEST['COOKIE'] = htmlspecialchars( (string) $_REQUEST['COOKIE'], ENT_QUOTES );
        if (!isset($_REQUEST['SERVER'])){ $_REQUEST['SERVER'] = ''; } else $_REQUEST['SERVER'] = htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES );

        // Set a custom cookie domain or automatically create domain inc. sub domains.
        $cookie_domain = $_SERVER['HTTP_HOST'];



        // Set session domain parameter to empty for localhost
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
                $cookie_domain = '';
        }
        elseif ($_REQUEST['COOKIE'] != '') {
                $cookie_domain = $_REQUEST['COOKIE'];
        }
        else {
                // Set session cookie timeout and domain
                if (preg_match("/^(http)[s](:\/\/)?([^\/]+)/i", $_SERVER['HTTP_REFERER'], $matches)) {
                        if (is_array($matches)) {
                                $hostname = $matches[2];
                                preg_match('/[^\.\/]+\.[^\.\/]+(\.[^\.\/]{2})?$/', $hostname, $matches);

                                if (count($matches) != 0) {
                                        $cookie_domain = $matches[0];
                                }
                        }
                }
        }

        // Remove www. if at the start of string
        if (substr($cookie_domain, 0,4) == 'www.') {
                $cookie_domain = substr($cookie_domain, 4);
        }

        $domain_id = 0;
        $domainIsValid = true;
        if (isset($_REQUEST['DOMAINID'])){
          $domain_id = (int) $_REQUEST['DOMAINID'];
        }
        
         // Agent ID
         $agent_id = 0;
        
        if (isset($_REQUEST['AGENTID'])){
            $agent_id = (int) $_REQUEST['AGENTID'];                    
        }

        if (isset($_REQUEST['LANGUAGE'])){
          $language = htmlspecialchars( (string) $_REQUEST['LANGUAGE'], ENT_QUOTES);
        }

        if(isset($_REQUEST['oUSERID'])) { $_REQUEST['oUSERID'] = (int) $_REQUEST['oUSERID'];
          $query = "SELECT id_domain FROM " . $table_prefix . "domain_user WHERE id_user = ".$_REQUEST['oUSERID']." Limit 1";
          $rows = $SQL->selectquery($query);
          $row = mysql_fetch_array($rows);
          $domain_id = $rows["id_domain"];
        }

        $refDomain = getReferrer();
        //$refDomain = $_REQUEST['URL'] == "" ? $_SERVER['HTTP_REFERER'] : $_REQUEST['URL'];

        $cookieName = str_replace(".", "", $refDomain);

        //  new condition and refresh value from 5 to 30 in order to support safari third-party cookies restriction

        if (!isset($_COOKIE[$cookieName])) {
          $domainIsValid = false;
          if(isset($domain_id) && ($domain_id != ''))
          {
            $query = "SELECT name FROM " . $table_prefix . "domains WHERE id_domain = '".$domain_id."'";
            $row = $SQL->selectquery($query);
            $domainName = $row['name'];
            if ((!(strripos($domainName, $refDomain) === false)))
            {
              $domainIsValid = true;
            }
          }

                           
            if ((isset($domain_id)) && (!(strripos($domainName, $refDomain) === false)))
            {
              if (!isset($_REQUEST['WIDTH'])){ $_REQUEST['WIDTH'] = ''; } else $_REQUEST['WIDTH'] = (int) $_REQUEST['WIDTH'];
              if (!isset($_REQUEST['HEIGHT'])){ $_REQUEST['HEIGHT'] = ''; } else $_REQUEST['HEIGHT'] = (int) $_REQUEST['HEIGHT'];

              $ipaddress = $_SERVER['REMOTE_ADDR'];
              $useragent = $_SERVER['HTTP_USER_AGENT'];
              $width = $_REQUEST['WIDTH'];
              $height = $_REQUEST['HEIGHT'];

              // Buscar un registro en los últimos 60 segundos, no 5.
              $query = "SELECT * FROM " . $table_prefix . "requests WHERE ipaddress = '$ipaddress' And useragent = '$useragent' And resolution = '$width x $height' And (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`)) < 30";
              $row = $SQL->selectquery($query);

              if (is_array($row))
              {
                $request_id = $row['id'];
                $session = array();
                $session['REQUEST']  = $request_id;
                $session['CHARSET']  = CHARSET;
                $session['USERID']   = mysql_real_escape_string($_REQUEST['USERID']);
                $session['DOMAINID'] = $domain_id;
                $session['AGENTID']  = $agent_id;

                if (isset($_REQUEST['LANGUAGE'])){
                  $session['LANGUAGE'] = $_REQUEST['LANGUAGE'];
                }else{
                  $session['LANGUAGE'] = LANGUAGE_TYPE;
                }

                $session['SERVICE'] = mysql_real_escape_string($_REQUEST['SERVICE']);

                //$session['ACCOUNT'] = $account;
                //$session['TRACKING'] = $tracking;
                //$session['STATUS_INDICATOR'] = $status_indicator;

                $data = serialize($session);

                setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);
                header("P3P: CP='$p3p'");

                $_COOKIE[$cookieName] = $data;
              }
            }
        }

        // Retrieve COOKIE variables and unserialize
        if (isset($_COOKIE[$cookieName])) {
                $session = array();
                $session = unserialize($_COOKIE[$cookieName]);

                if (!isset($session['GUEST_LOGIN_ID'])){ $session['GUEST_LOGIN_ID'] = 0; }
                if (!isset($session['GUEST_USERNAME'])){ $session['GUEST_USERNAME'] = ''; }
                if (!isset($session['MESSAGE'])){ $session['MESSAGE'] = 0; }
                if (!isset($session['CHATTING'])){ $session['CHATTING'] = 0; }
                if (!isset($session['SECURITY'])){ $session['SECURITY'] = ''; }
                if (!isset($session['OPERATOR'])){ $session['OPERATOR'] = ''; }
                if (!isset($session['TOTALOPERATORS'])){ $session['TOTALOPERATORS'] = 0; }
                if (!isset($session['USERID'])){ $session['USERID'] = 0; }

                $request_id = $session['REQUEST'];
                $guest_login_id = $session['GUEST_LOGIN_ID'];
                $guest_username = $session['GUEST_USERNAME'];
                $guest_message = $session['MESSAGE'];
                $operator_username = $session['OPERATOR'];
                $total_operators = $session['TOTALOPERATORS'];
                $chatting = $session['CHATTING'];
                $security = $session['SECURITY'];

                if (!isset($language)){
                  $language = $session['LANGUAGE'];;
                }

                define('LANGUAGE_TYPE', $language);

                $charset = $session['CHARSET'];

                if (isset($session['VALID'])){
                  $domainIsValid = $session['VALID'];
                }

                if (isset($session['DOMAINID']) && $session['DOMAINID'] != '0'){
                  $domain_id = $session['DOMAINID'];
                }
              
                if (isset($session['AGENTID']) && $session['AGENTID'] != 0){
                  $agent_id = $session['AGENTID'];
                
                }
                
                               
                $user_id = $session['USERID'];
                $webCall_id = $session['WEBCALLID'];
                unset($session);
        }

        if(($command != 'tracker') &&($domain_id != 0) && ($domain_id != '')){

          $query = "SELECT `name`, `value` FROM " . $table_prefix . "settings Where id_domain = " . $domain_id;
          $rows = $SQL->selectall($query);
          if (is_array($rows)) {
                  foreach ($rows as $key => $row) {
                          if (is_array($row)) {
                                  $variable = $row['name'];
                                  $$variable = $row['value'];
                          }
                  }
          }
          else {
                  return false;
                  exit();
          }

          $query = "SELECT * FROM " . $table_prefix . "ge_global_settings";
          $rows = $SQL->selectall($query);
          if (is_array($rows)) {
                  foreach ($rows as $key => $row) {
                          if (is_array($row)) {
                                  $variable = "gbs_".$row['id'];
                                  $$variable = $row['value'];
                          }
                  }
          }
          else {
                  return false;
                  exit();
          }
        }

        $server = $eserverHostname;

        if (!isset($request_id)){ $request_id = ''; }

        define('LANGUAGE_TYPE', $language);

        $query = "SELECT charset FROM " . $table_prefix . "languages WHERE `code` = '$language'";
        $row = $SQL->selectquery($query);
        define('CHARSET', $row['charset']);
       // define('CHARSET', "utf-8");

        if(($language != '') && (!isset($charset) || ($charset == '') || ($charset == "CHARSET")))
        {

          $query = "SELECT charset FROM " . $table_prefix . "languages WHERE `code` = '$language'";

          $row = $SQL->selectquery($query);
          if (is_array($row)) {
                  define('CHARSET', $row['charset']);
                  $charset = $row['charset'];
          }
        }

        //  Modify the language pack image locations unless images have been relocated and file exists



        $lang_images_directory = $install_directory."/domains/";
        $lang_images_directory_agents = $install_directory."/agents/";


        if (!isset($_REQUEST['IMAGES'])){
          $_REQUEST['IMAGES'] = '';
        } else $_REQUEST['IMAGES'] = htmlspecialchars( (string) $_REQUEST['IMAGES'], ENT_QUOTES );
        $custom_images_directory = $_REQUEST['IMAGES'];
        if ($custom_images_directory != '')
        {
          $lang_images_directory = $custom_images_directory;

        }


        $showImage =1;
        // Domain status indicator 
        if(($domain_id != 0) && ($showImage == true) && ($domain_id != '') && ($agent_id ==0) )
        {
          $livehelp_logo = $lang_images_directory.$domain_id."/i18n/".LANGUAGE_TYPE."/pictures/"."online" . '.' . $status_indicator_img_type ;
          $offline_logo = $lang_images_directory.$domain_id."/i18n/".LANGUAGE_TYPE."/pictures/"."offline" . '.' . $status_indicator_img_type;
          $online_logo = $lang_images_directory.$domain_id."/i18n/".LANGUAGE_TYPE."/pictures/"."online" . '.' . $status_indicator_img_type;
          $offline_logo_without_email = $lang_images_directory.$domain_id."/i18n/".LANGUAGE_TYPE."/pictures/"."offline" . '.' . $status_indicator_img_type;
          $online_brb_logo = $lang_images_directory.$domain_id."/i18n/".LANGUAGE_TYPE."/pictures/"."brb" . '.' . $status_indicator_img_type;
          $online_away_logo = $lang_images_directory.$domain_id."/i18n/".LANGUAGE_TYPE."/pictures/"."away" . '.' . $status_indicator_img_type;
        } 
        else
        
         // Agents status indicator 
        if( ($showImage == true) && ($agent_id !=0) )
        {
          $livehelp_logo = $lang_images_directory_agents.$agent_id."/i18n/".LANGUAGE_TYPE."/"."online" . '.' . $status_indicator_img_type;
          $offline_logo = $lang_images_directory_agents.$agent_id."/i18n/".LANGUAGE_TYPE."/"."offline" . '.' . $status_indicator_img_type;
          $online_logo = $lang_images_directory_agents.$agent_id."/i18n/".LANGUAGE_TYPE."/"."online" . '.' . $status_indicator_img_type;
          $offline_logo_without_email = $lang_images_directory_agents.$agent_id."/i18n/".LANGUAGE_TYPE."/"."offline" . '.' . $status_indicator_img_type;
          $online_brb_logo = $lang_images_directory_agents.$agent_id."/i18n/".LANGUAGE_TYPE."/"."brb" . '.' . $status_indicator_img_type;
          $online_away_logo = $lang_images_directory_agents.$agent_id."/i18n/".LANGUAGE_TYPE."/"."away" . '.' . $status_indicator_img_type;
        }

        $disable_chat_username =0;

        if ($disable_chat_username == true)
        {
          $chat_username = '0';
        } else {
          $chat_username = '1';
        }

        //Calculate timezone difference respective of LOCAL and REMOTE timezones
        $local_timezone_sign = substr($timezone, 0, 1);
        $local_timezone_hours = substr($timezone, 1, 2);
        $local_timezone_minutes = substr($timezone, 3, 4);

        // Convert LOCAL time to decimal format
        if ($local_timezone_minutes != '00') { $local_timezone_minutes = ($local_timezone_minutes / 60); }
        $local_timezone = $local_timezone_sign . $local_timezone_hours + $local_timezone_minutes;

        $remote_timezone_sign = substr(date("O"), 0, 1);
        $remote_timezone_hours = substr(date("O"), 1, 2);
        $remote_timezone_minutes = substr(date("O"), 3, 4);

        // Convert REMOTE time to decimal format
        if ($remote_timezone_minutes != '00') { $remote_timezone_minutes = ($remote_timezone_minutes / 60); }
        $remote_timezone = $remote_timezone_sign . $remote_timezone_hours + $remote_timezone_minutes;

        // Calculate difference between decimal LOCAL time and REMOTE time and CONVERT to eg. +/-0430 format
        $difference_timezone_hours = round(($local_timezone - $remote_timezone) - 0.1);
        $difference_timezone_minutes = (($local_timezone - $remote_timezone) - $difference_timezone_hours) * 60;

        //return true;

  } /* __CONFIG_INC */

?>
