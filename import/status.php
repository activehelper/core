<?php
include_once('constants.php');

if (!isset($_SERVER['HTTP_REFERER'])){ $_SERVER['HTTP_REFERER'] = ''; }
if (!isset($_REQUEST['DEPARTMENT'])){ $_REQUEST['DEPARTMENT'] = ''; } else $_REQUEST['DEPARTMENT'] = htmlspecialchars( (string) $_REQUEST['DEPARTMENT'], ENT_QUOTES );
if (!isset($_REQUEST['SERVER'])){ $_REQUEST['SERVER'] = ''; } else $_REQUEST['SERVER'] = htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES );
if (!isset($_REQUEST['STATUS'])){ $_REQUEST['STATUS'] = ''; } else $_REQUEST['STATUS'] = (bool) $_REQUEST['STATUS'];

$showImage = true; if (!isset($_REQUEST['USERID'])){ $_REQUEST['USERID'] = null; } else $_REQUEST['USERID'] = (int) $_REQUEST['USERID'];

$_REQUEST['service_id'] = isset( $_REQUEST['service_id'] ) ? (int) $_REQUEST['service_id'] : '';

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

if($domainIsValid == false){
  exit;
}
// Get Agent ID
$agent_id =0;
if (isset($_REQUEST['AGENTID'])){
          $agent_id = (int) $_REQUEST['AGENTID'];                             
        }
 

$service_id = mysql_real_escape_string($_REQUEST['service_id']);

if (!isset($_REQUEST['oUSERID'])){ $_REQUEST['oUSERID'] = null; } else $_REQUEST['oUSERID'] = (int) $_REQUEST['oUSERID'];

if(isset($_REQUEST['oUSERID'])) {
        $query = "SELECT id_domain FROM " . $table_prefix . "domain_user WHERE id_user = ".mysql_real_escape_string($_REQUEST['oUSERID'])." Limit 1";
        $rows = $SQL->selectquery($query);
        $row = mysql_fetch_array($rows);
        $domain_id = $rows["id_domain"];
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
}

if ($installed == false) {
        include($install_path . $install_directory . '/import/settings_default.php');
        header('Content-type: image/gif');
        if (@readfile('../../' . $online_install_logo) == false) {
                        header("Location: ../../" . $online_install_logo);
        }
        exit();
}

$department = mysql_real_escape_string($_REQUEST['DEPARTMENT']);
$status_enabled = $_REQUEST['STATUS'];
$userid = mysql_real_escape_string($_REQUEST['USERID']);
$ouserid = mysql_real_escape_string($_REQUEST['oUSERID']);

if ($status_enabled == '') { $status_enabled = 'true'; }

if($ouserid == "") {
        $query = "SELECT id_domain FROM " . $table_prefix . "domains WHERE id_domain = '".$domain_id."'";
        $rows = $SQL->selectquery($query);
        if (!is_array($rows)) {
                        $query = "SELECT id_domain FROM " . $table_prefix . "domain_alias WHERE id_domain = '".$domain_id."'";
                        $rows = $SQL->selectquery($query);
                        if (!is_array($rows)) {
                                exit;
                        }
        }
        $row = mysql_fetch_array($rows);
        $domain_id = $rows["id_domain"];
} else {
        $query = "SELECT id_domain FROM " . $table_prefix . "domain_user WHERE id_user = ".$ouserid." Limit 1";
        $rows = $SQL->selectquery($query);
        if (!is_array($rows)) {
                exit;
        }
        $row = mysql_fetch_array($rows);
        $domain_id = $rows["id_domain"];
}
// get all users of this account
$users_set = "";
//$query = "Select id_user From " . $table_prefix . "domain_user ad Where id_domain = ".$domain_id;
$query = "Select id_user From " . $table_prefix . "domain_user du, " . $table_prefix . "sa_domain_user_role dur, " . $table_prefix . "sa_role_services rs  Where du.id_domain = ".$domain_id." and dur.id_domain_user = du.id_domain_user And rs.id_role = dur.id_role And rs.id_service = ".$service_id;
$rows = $SQL->selectall($query);
foreach ($rows as $key => $row) {
        $users_set .= $row["id_user"].",";
}
$users_set = substr($users_set, 0, -1);
// If the Live Help Status Image needs to be shown then...
if ($status_enabled == true) {

        $num_support_available_users = 0;
        $num_support_hidden_users = 0;
        $num_support_online_users = 0;
        $num_support_away_users = 0;
        $num_support_brb_users = 0;

        if( $userid != "") {
                $query = "Update " . $table_prefix . "users Set status = 1, refresh = NOW() WHERE id = " . $userid;
                $SQL->miscquery($query);
        }
        // Counts the total number of support users within each Online/Offline/BRB/Away status mode
        $query = "SELECT DISTINCT `status`, count(`id`) FROM " . $table_prefix . "users WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' And id in (" . $users_set . ")";
        
        if($department != '' && $departments && $agent_id ==0) 
          { $query .= " and  `answers`='1' and  `department` LIKE '%$department%'"; }
        else         
        if($agent_id ==0) 
          { $query .= " and `answers`='1' "; }
        else 
        if($agent_id !=0) 
        { $query .= " and `answers`='2' and `id`= $agent_id"; }
        
       /* error_log("department:".$department."\n", 3, "status.log");
        error_log("departments:".$departments."\n", 3, "status.log");
        error_log("agent_id:".$agent_id."\n", 3, "status.log");
        error_log("$query:".$query."\n", 3, "status.log");
        */
        
        if($userid != '') { $query .= " AND id <> " . $userid; }
        $query .= " GROUP BY `status`";
        $rows = $SQL->selectall($query);
        if(is_array($rows)) {
                foreach ($rows as $key => $row) {
                        if (is_array($row)) {           
                                switch ($row['status']) {
                                        case 0: // Offline - Hidden
                                           $num_support_hidden_users = $row['count(`id`)'];
                                           break;
                                        case 1: // Online
                                           $num_support_online_users = $row['count(`id`)'];
                                           break;
                                        case 2: // Be Right Back
                                           $num_support_brb_users = $row['count(`id`)'];
                                           break;
                                        case 3: // Away
                                           $num_support_away_users = $row['count(`id`)'];
                                           break;
                                }
                        }
                }
        }
        $num_support_available_users = $num_support_online_users + $num_support_away_users + $num_support_brb_users;

        // Set Be Right Back active status if all users are in BRB mode inc. Departments
        if ($num_support_available_users == $num_support_brb_users && $num_support_brb_users > 0 ) {
                $brb_mode_active = true;
        }
        else {
                $brb_mode_active = false;
        }
        // Set Away active status if all users are in Away mode inc. Departments
        if ($num_support_available_users == $num_support_away_users && $num_support_away_users > 0 ) {
                $away_mode_active = true;
        }
        else {
                $away_mode_active = false;
        }
        
        // HTTP/1.1
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        
        // HTTP/1.0
        header('Pragma: no-cache');

        if($num_support_online_users > 0 || $brb_mode_active == 'true' || $away_mode_active == 'true') {
                // If Be Right Back Mode is to be displayed then print out...
                if ($brb_mode_active == true) {
                        header('Content-type: image/gif');

                        $online_brb_logo_serv_path = $online_brb_logo;

                        if ($server != '' && ini_get('allow_url_fopen') == true) {
                                $fp = @fopen($online_brb_logo_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $online_brb_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        else {
                                $fp = @fopen($online_brb_logo_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $online_brb_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        exit();
                }
                elseif ($away_mode_active == true) {
                        header('Content-type: image/gif');

                        $online_away_logo_serv_path = $online_away_logo;

                        if ($server != '' && ini_get('allow_url_fopen') == true) {
                                $fp = @fopen($online_away_logo_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $online_away_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        else {
                                $fp = @fopen($online_away_logo_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $online_away_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        exit();
                }
                else {

                        header('Content-type: image/gif');
                        $online_logo_serv_path = $livehelp_logo;

                        if ($server != '' && ini_get('allow_url_fopen') == true) {
                                $fp = @fopen($online_logo_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $online_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        else {
                                $fp = @fopen($online_logo_serv_path, 'r');

                                if ($fp == false) {
                                        header("Location: " . $online_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        exit();
                }
        }
        else {
                if ($disable_offline_email == true) {
                        header('Content-type: image/gif');

                        $offline_logo_without_email_serv_path = $offline_logo_without_email;

                        if ($server != '' && ini_get('allow_url_fopen') == true) {
                                $fp = @fopen($offline_logo_without_email_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $offline_logo_without_email_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        else {
                                $fp = @fopen($offline_logo_without_email_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $offline_logo_without_email_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        exit();
                }
                else {
                        header('Content-type: image/gif');

                        $offline_logo_serv_path = $offline_logo;

                        if ($server != '' && ini_get('allow_url_fopen') == true) {

                                $fp = @fopen($offline_logo_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $offline_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        } else {
                                $fp = @fopen($offline_logo_serv_path, 'r');
                                if ($fp == false) {
                                        header("Location: " . $offline_logo_serv_path);
                                } else {
                                        while($contents_ = fread($fp, 1000)) {
                                                $contents .= $contents_;
                                        }
                                        echo($contents);
                                }
                                fclose($fp);
                        }
                        exit();
                }
        }
}
?>
