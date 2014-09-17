<?php

ob_start( 'ob_gzhandler' );

include_once('../import/constants.php');
include('../import/config_database.php');
include('../import/class.mysql.php');
include('../import/functions.php');


checkSession();

// Open MySQL Connection
$SQL = new MySQL();
$SQL->connect();

if (!isset($_REQUEST['ACTION']))
{
   $_REQUEST['ACTION'] = '';
} else $_REQUEST['ACTION'] = htmlspecialchars ( (string) $_REQUEST['ACTION'], ENT_QUOTES );
if (!isset($_REQUEST['ID']))
{
   $_REQUEST['ID'] = '';
} else $_REQUEST['ID'] = (int) $_REQUEST['ID'];
if (!isset($_REQUEST['TRANSFER']))
{
   $_REQUEST['TRANSFER'] = '';
} else $_REQUEST['TRANSFER'] = htmlspecialchars ( (string) $_REQUEST['TRANSFER'], ENT_QUOTES );

$action = $_REQUEST['ACTION'];
$login_id = $_REQUEST['ID'];
$transfer_id = $_REQUEST['TRANSFER'];
$id_service = isset($_REQUEST['IDSERVICE']) ? (int) $_REQUEST['IDSERVICE']: "";

$operator_login_id = $_REQUEST['OPERATORID'] = (int) $_REQUEST['OPERATORID'];



$query = "SELECT datetime, ((UNIX_TIMESTAMP(NOW())  - UNIX_TIMESTAMP(refresh))) as time_session , answers FROM " . $table_prefix .
         "users WHERE id = ".$operator_login_id;



$row = $SQL->selectquery($query);
if (!is_array($row))
{
   if (strpos(php_sapi_name(), 'cgi') === false )
   {
      header('HTTP/1.0 403 Forbidden');
   }
   else
   {
      header('Status: 403 Forbidden');
   }
   exit;
}
else
{
   $time_session = $row['time_session'];
   $login_datetime = $row['datetime'];
   $indicator_type = $row['answers'];

   // Update the username, password and make no mode changes
   if($time_session >= 6){
     $query = "UPDATE " . $table_prefix . "users SET `refresh` = NOW() WHERE `id` = '$operator_login_id'";
     $SQL->miscquery($query);


   }

}


// Check for actions and process
if ($action == 'Accept' && $login_id != '0')
{

   if($id_service == 1 || $id_service == "")
   {
      // Check if already assigned to a Support operator
      $query = "SELECT `active` FROM " . $table_prefix . "sessions WHERE `id` = '$login_id'";
      $row = $SQL->selectquery($query);


      if (is_array($row))
      {
         if ($row['active'] == '0' || $row['active'] == '-2')
         {
            // Update the active flag of the guest user to the ID of their
            //supporter and update the support_user to the username of their supporter
            $query = "UPDATE " . $table_prefix . "sessions SET `active` = '$operator_login_id', `id_user` = '$operator_login_id' WHERE `id` = '$login_id'";
            $SQL->miscquery($query);




         }
      }
   }
}
elseif ($action == 'Close' && $login_id != '0')
{

   // Update active of user to -3 to remove from users panel
   if($id_service == 1 || $id_service == "")
   {
      $query = "UPDATE " . $table_prefix . "sessions SET `active` = '-1' WHERE `id` = '$login_id'";

   }
   $SQL->miscquery($query);

}
elseif ($action == 'Transfer' && $login_id != '0' && $transfer_id != '0')
{

   $query = "UPDATE " . $table_prefix . "sessions SET `datetime` = NOW(), ".
            "`active`= '-2', `transfer` = '$transfer_id' WHERE `id` = '$login_id'";

   $SQL->miscquery($query);



}
elseif ($action == 'Hide' && $login_id != '0')
{
   if($id_service == 1 || $id_service == "")
   {
      // Update active of user to -3 to remove from users panel
      $query = "UPDATE " . $table_prefix . "sessions SET `active` = '-3' WHERE `id` = '$login_id'";

   }
   $SQL->miscquery($query);

}
elseif ($action == 'Hidden' || $action == 'Offline')
{
   // Update the username, password and change to hidden mode
   $query = "UPDATE " . $table_prefix . "users SET `refresh` = NOW(), `status` = '0' WHERE `id` = '$operator_login_id'";
   $SQL->miscquery($query);

}
elseif ($action == 'Online')
{
   // Update the username, password and change to online mode
   $query = "UPDATE " . $table_prefix . "users SET `refresh` = NOW(), `status` = '1' WHERE `id` = '$operator_login_id'";
   $SQL->miscquery($query);

}
elseif ($action == 'BRB')
{
   // Update the username, password and change to be right back mode
   $query = "UPDATE " . $table_prefix . "users SET `refresh` = NOW(), `status` = '2' WHERE `id` = '$operator_login_id'";
   $SQL->miscquery($query);


}
elseif ($action == 'Away')
{
   // Update the username, password and change to be right back mode
   $query = "UPDATE " . $table_prefix . "users SET `refresh` = NOW(), `status` = '3' WHERE `id` = '$operator_login_id'";
   $SQL->miscquery($query);

}

// get all sessions of this account
$sessions_set = "";
$query = "Select s.id From " . $table_prefix . "requests r, " . $table_prefix . "sessions s where s.request = r.id And r.id_domain in (" .
         $domains_set . ") And (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(s.refresh)) < '$connection_timeout'";



$rows = $SQL->selectall($query);
foreach ($rows as $key => $row)
{
   $sessions_set .= $row["id"] . ",";
}
$sessions_set = substr($sessions_set, 0, - 1);

$charset = 'utf-8';
header('Content-type: text/xml; charset=' . $charset);
echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");
?><UserList xmlns="urn:LiveHelp">
        <Staff>
<?php
// ONLINE ADMIN USERS QUERY
 $query = "SELECT `id`, `username`, `status` FROM " . $table_prefix . "users WHERE (UNIX_TIMESTAMP(NOW()) - ".
         "UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND (`status` = '1' OR `status` = '2' OR `status` = '3') and id in (" .
         $users_set . ") ORDER BY `username`";

//error_log("query: ".$query."\n", 3, "users.log");

// ONLINE ADMIN USERS QUERY

 /*$agent_id = 1;

 $query = "SELECT `id`, `username`, `status` FROM " . $table_prefix . "users WHERE (UNIX_TIMESTAMP(NOW()) - ".
         "UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND (`status` = '1' OR `status` = '2' OR `status` = '3') and id =" . $agent_id ." ORDER BY `username`";
  
  error_log("agent_id : ".$agent_id."\n", 3, "users.log");
  error_log("query: ".$query."\n", 3, "users.log");       
   */ 
 
 $agent_id = 1;
        
$rows = $SQL->selectall($query);


if (is_array($rows))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $login_id = $row['id'];
         $status = $row['status'];
         $username = xmlinvalidchars($row['username']);

       /*
         // Count the total NEW messages that have been sent to the current login
         $query = "SELECT count(`id`)  FROM " . $table_prefix . "administration WHERE `user` = '$login_id' AND ".
                  "(UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP('$login_datetime')) > '0'";


       $row = $SQL->selectquery($query);
         if (is_array($row))
         {
            $messages = $row['count(`id`)'];
         } */

         $messages = 0;

?>
                <User ID='<?php echo($login_id);?>' Messages='<?php echo($messages);?>' Status='<?php echo($status);?>' services='<?=$services?>'>
                <?php echo($username);?>
                </User>
<?php
      }
   }
}
?>
        </Staff>
        <Online>
  <?php
// ONLINE GUEST USERS QUERY


if($sessions_set != ""){
  $query = "SELECT `id`, `username`, email, language, request FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW())".
           " - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND `active` = '$operator_login_id' And id in (" .
           $sessions_set . ") ORDER BY `username`";
   

  $rows = $SQL->selectall($query);
}

if (is_array($rows) && $sessions_set != "")
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $login_id = $row['id'];
         $username = $row['username'];
         $email = $row['email'];
         $user_language = $row['language'];
         $request_id = $row['request'];



        /*
         // Count the total NEW messages that have been sent to the current login
         $query = "SELECT max(`id`) FROM " . $table_prefix . "messages WHERE `session` = '$login_id' AND `status` <= '3' AND" .
                  " (UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP('$login_datetime')) > '0'";

         $row = $SQL->selectquery($query);
         if (is_array($row))
         {
            $messages = $row['max(`id`)'];
         }
         */
         $messages = 0;
?>
                <User ID='<?php echo($login_id);?>' id_request='<?php echo($request_id);?>' Messages='<?php echo($messages);?>'>
                        <?php echo($username);
?>
                        <id_service>1</id_service>
                        <email><?=$email?></email>
                        <language><?=$user_language?></language>
                </User>
<?php
      }
   }
}
?>

          </Online>
        <Pending>
<?php
// PENDING USERS QUERY displays pending users not logged in on users users table depending on department settings

include('../import/settings_default.php');

//error_log("department:".$departments."\n", 3, "users.log");
  
if ($indicator_type == 1) {
if ($departments == true && $current_department !='')
{  
   $sql = departmentsSQL($current_department);
   $query = "SELECT DISTINCT `id`, `username`, email, language, request FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW())".
            " - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND `active` = '0' and id_agent='0' ";
            
   if ( $sessions_set !='')                
      { $query .= " and id in (" . $sessions_set . ") AND $sql ORDER BY `username`"; }
   else
     { $query .= " and $sql ORDER BY `username`"; }            
                      
}
else

{
   $query = "SELECT DISTINCT `id`, `username`, email, language, request FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW())".
            " - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND `active` = '0' and id_agent=0 ";  
            
            
    if ( $sessions_set !='')                
      { $query .= " and id in (" . $sessions_set . ")  ORDER BY `username`"; }
   else
     { $query .= " ORDER BY `username`"; }                                             
}

}
else
if ($indicator_type == 2) {
   $query = "SELECT DISTINCT `id`, `username`, email, language, request FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW())".
            " - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' and `active` = '0' " .
            " and id_agent=".$operator_login_id." ORDER BY `username`";
                    
}

  
if($sessions_set != ""){
  $rows = $SQL->selectall($query);
}
  
if (is_array($rows) && ($sessions_set != ""))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $request_id = $row['request'];
         $login_id = $row['id'];
         $username = $row['username'];
         $user_language = $row['language'];



?>
                <User ID='<?php echo($login_id);?>' id_request='<?php echo($request_id);?>'>
                        <id_service>1</id_service>
                        <email><?=$row['email']?></email>
                        <language><?=$user_language?></language>
                        <?php echo($username);
?>
                </User>
<?php
      }
   }
}

?>

<?php

?>
        </Pending>
        <Transferred>
<?php
// TRANFERRED USERS QUERY displays transferred users not logged in on users users table depending on department settings
if($sessions_set != ""){
  $query = "SELECT DISTINCT `id`, `username` FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`))".
           " < '$connection_timeout' AND `active` = '-2' AND `transfer` = '$operator_login_id' And id in (" . $sessions_set . ") ORDER BY".
           " `username`";
  $rows = $SQL->selectall($query);
}

if (is_array($rows) && ($sessions_set != ""))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $login_id = $row['id'];
         $username = $row['username'];
?>
                <User ID='<?php echo($login_id);
?>'><?php echo($username);
?></User>
<?php
      }
   }
}
?>
        </Transferred>
        <Offline>
<?php
// OFFLINE USERS QUERY
if($sessions_set != ""){
  $query = "SELECT DISTINCT `id`, `username` FROM " . $table_prefix . "sessions WHERE `datetime` > '$login_datetime' AND ".
           "(`active` = '$operator_login_id' OR `active` = '0' OR `active` = '-1') AND (UNIX_TIMESTAMP(NOW()) - ".
           "UNIX_TIMESTAMP(`refresh`)) > '$connection_timeout' And id in (" . $sessions_set . ") ORDER BY `username`";
  $rows = $SQL->selectall($query);
}

if (is_array($rows) && ($sessions_set != ""))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $login_id = $row['id'];
         $username = $row['username'];
?>
                <User ID='<?php echo($login_id);?>'>
                <?php echo($username);?>
                </User>
<?php
      }
   }
}
?>
 </Offline>
 <Admins>
<?php
// OFFLINE USERS QUERY
    
    $query = "SELECT `operator_id` FROM " . $table_prefix . "administration  WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`)) < '45' and user ='$operator_login_id' group by operator_id";  
    $rows = $SQL->selectall($query);

if (is_array($rows))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $agent_id = $row['operator_id'];   
?>
  <User Agent='<?php echo($agent_id);?>'></User>
<?php
      }
   }
}
?>
        </Admins>        
</UserList>
