<?php
ob_start( 'ob_gzhandler' );
include_once('../import/constants.php');
include('../import/config_database.php');
include('../import/class.mysql.php');
include('../import/functions.php');

session_start();

// Open MySQL Connection
$SQL = new MySQL();
$SQL->connect();

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

  if (!isset($_REQUEST['USERNAME']))
  {
     $_REQUEST['USERNAME'] = '';
  } else $_REQUEST['USERNAME'] = htmlspecialchars( (string) $_REQUEST['USERNAME'], ENT_QUOTES );
  if (!isset($_REQUEST['PASSWORD']))
  {
     $_REQUEST['PASSWORD'] = '';
  } else $_REQUEST['PASSWORD'] = htmlspecialchars( (string) $_REQUEST['PASSWORD'], ENT_QUOTES );
  if (!isset($_REQUEST['ACCOUNT']))
  {
     $_REQUEST['ACCOUNT'] = '';
  } else $_REQUEST['ACCOUNT'] = htmlspecialchars( (string) $_REQUEST['ACCOUNT'], ENT_QUOTES );
  if (!isset($_REQUEST['SERVER']))
  {
     $_REQUEST['SERVER'] = '';
  } else $_REQUEST['SERVER'] = htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES );
  if (!isset($_REQUEST['LANGUAGE']))
  {
     $_REQUEST['LANGUAGE'] = '';
  } else $_REQUEST['LANGUAGE'] = htmlspecialchars( (string) $_REQUEST['LANGUAGE'], ENT_QUOTES );

  $login_timeout = 20;

  $language = $_REQUEST['LANGUAGE'];
  define('LANGUAGE_TYPE', $language);


  $language_file = '../i18n/' . LANGUAGE_TYPE . '/lang_service_' . LANGUAGE_TYPE . '.php';
  if ($language != '') {
          include($language_file);
  }
  else {
          include('../i18n/en/lang_service_en.php');
  }

  // Get username, password and email from database and authorise the login details
  $query = "SELECT 1 FROM " . $table_prefix . "users u, " . $table_prefix . "accounts a, " .
           $table_prefix . "domain_user du, " . $table_prefix . "accounts_domain ad WHERE u.username = '" .
           $_REQUEST['USERNAME'] . "' and a.login = '" . $_REQUEST['ACCOUNT'] .
           "' and du.id_user = u.id and ad.id_account = a.id_account and du.id_domain = ad.id_domain";


  $row = $SQL->selectquery($query);
  //deniedRequest($row, $login_account_incorrect);
  if (!is_array($row))
  {
    printError($login_account_incorrect);
    exit;
  }

  //Verifica el password
  $query = "SELECT `id`, `firstname`, `lastname`, `privilege`, `department`  , `schedule` FROM " . $table_prefix .
           "users WHERE `username` REGEXP BINARY '^" . $_REQUEST['USERNAME'] . "$' AND `password` = '" .
           $_REQUEST['PASSWORD']."'";

  $row = $SQL->selectquery($query);
  //deniedRequest($row, $password_incorrect);
  if (!is_array($row))
  {
    printError($password_incorrect);
    exit;
  }
  
 //-- operator settings
   $operator_login_id  = $row['id'];   
   $current_first_name = $row['firstname'];
   $current_last_name  = $row['lastname'];
   $current_privilege  = $row['privilege'];
   $current_department = $row['department'];
   $current_account    = $_REQUEST['ACCOUNT'];
   $operator_schedule  = $row['schedule'];

 //Verifica el schedule
 
 if ($operator_schedule == 1){
  $query = "SELECT `id` FROM " . $table_prefix .
   "users  WHERE `id` = " .$operator_login_id. " and CURTIME() BETWEEN initial_time  AND final_time ";
           
  $row = $SQL->selectquery($query);
  //deniedRequest($row, $password_incorrect);
  if (!is_array($row))
  {
   // error_log("SQL ". $query."\n", 3, "login.log");
    printError($schedule_time_incorrect);
    exit;
  }
   }
   //error_log("1. operator_login_id: ".$operator_login_id."\n", 3, "login.log");

   //Verifica si la session expiro
   $query = "SELECT FLOOR((UNIX_TIMESTAMP(NOW())  - UNIX_TIMESTAMP(refresh))) as time_session FROM " . $table_prefix .
            "users WHERE `username` REGEXP BINARY '^" . $_REQUEST['USERNAME'] . "$' AND `password` = '" .
            $_REQUEST['PASSWORD']."'";

   $row = $SQL->selectquery($query);
   if($row['time_session'] < 20){
     $message = $session_expired.' '.(20 - $row['time_session'].' '.$seconds);
     //deniedRequest('', $message);
      printError($message);
      exit;
   }

   //Crea la session con la informacion del usuario que se conecto
   session_start();

   $_SESSION["id"] = $operator_login_id;
   $_SESSION["firstname"] = $current_first_name;
   $_SESSION["lastname"] = $current_last_name;
   $_SESSION["privilege"] = $current_privilege;
   $_SESSION["department"] = $current_department;
   $_SESSION["account"] = $current_account;

   //------------------------------------------------------------------------
   // get all domains of this user
   //------------------------------------------------------------------------
            
    $domains_set ="";

    $query = "Select id_domain From " . $table_prefix . "domain_user Where id_user = " . $operator_login_id;
    $rows = $SQL->selectall($query);

    foreach ($rows as $key => $row)
    {
       $domains_set .= $row["id_domain"] . ",";
    }
    $domains_set = substr($domains_set, 0, - 1);

   //Adiciona los dominios a la sesion.
   $_SESSION["domains_set"] = $domains_set;

   //------------------------------------------------------------------------
   // Get all users of this domains
   //------------------------------------------------------------------------
  $users_set = "";
  
  if ($current_privilege =='0') {
     
      $query = "Select id_user From " . $table_prefix . "domain_user ad Where id_domain in (" . $domains_set . ")" . 
               "and ad.id_user in ( select id  from " . $table_prefix . "users jlu where lower(jlu.department) =".
               "( select lower(department) from " . $table_prefix . "users jlu where jlu.id =". $operator_login_id .") )";                      
    }
 else
 if ($current_privilege =='1') {
    $query = "Select id_user From " . $table_prefix . "domain_user ad Where id_domain in (" . $domains_set . ")";
    }
    

  $rows = $SQL->selectall($query);
  foreach ($rows as $key => $row)
  {
     $users_set .= $row["id_user"] . ",";
  }
  $users_set = substr($users_set, 0, - 1);

   // Update operator session to database
   $query = "UPDATE " . $table_prefix . "users SET `datetime` = NOW(), `refresh` = NOW(), `status` = '0' WHERE `id` = '$operator_login_id'";

   $SQL->miscquery($query);

   //Adiciona los usuarios de los dominios a la sesion.
   $_SESSION["users_set"] = $users_set;

   //------------------------------------------------------------------------
   //Services   // LiveHelp  = 1
   //-----------------------------------------------------------------------

   $_SESSION["services"] =1;
//--

  $charset = 'utf-8';
  header('Content-type: text/xml; charset=' . $charset);
  echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");
?><Login xmlns="urn:LiveHelp" ID="<?php echo($operator_login_id);?>" Version="<?php echo($web_application_version);?>" Name="<?php echo(xmlinvalidchars($current_first_name . ' ' . $current_last_name));?>" Access="<?php echo($current_privilege);?>">
<services xmlns="urn: eserver ">
</services>
<id_service><?=$_SESSION["services"]?></id_service>
</Login>

