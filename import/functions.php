<?php
include_once('constants.php');

$operator_login_id = '';
$current_first_name = '';
$current_last_name = '';
$current_privilege = '';
$current_department = '';
$current_account = '';
$domains_set = '';
$users_set = '';
$services = '';
$agent_id ='';

function checkSession()
{
   global $operator_login_id;
   global $current_first_name;
   global $current_last_name;
   global $current_privilege;
   global $current_department;
   global $current_account;
   global $domains_set;
   global $users_set;
   global $services;
   global $agent_id;

   session_start();

   #error_log("1. operator_login_id :"."\n", 3, "func.log");

   if(isset($_SESSION["id"]))
   {
      $operator_login_id = $_SESSION["id"];
      $current_first_name = $_SESSION["firstname"];
      $current_last_name = $_SESSION["lastname"];
      $current_privilege = $_SESSION["privilege"];
      $current_department = $_SESSION["department"];
      $current_account = $_SESSION["account"];
      $domains_set = $_SESSION["domains_set"];
      $users_set = $_SESSION["users_set"];
      $services = $_SESSION["services"];
      $agent_id = $_SESSION["agent_id"];

      return $_SESSION["id"];

  #   error_log("2. operator_login_id :". $operator_login_id."\n", 3, "func.log");

   }
   else
   {
    $charset = 'utf-8';
    header('Content-type: text/xml; charset=' . $charset);
    echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");
?>
<Session>
  <status>Invalid session</status>
</Session>
<?php
      exit;
   }
}

function deniedRequest($row, $message)
{

  if (!is_array($row))
  {
     if (strpos(php_sapi_name(), 'cgi') === false )
     {
        //header('HTTP/1.0 403 Forbidden');
        header('HTTP/1.0 403 '.$message);
        exit(1);

     }
     else
     {
        //header('Status: 403 Forbidden');
        header('Status: 403 '.$message);
        exit(1);
     }
     exit;
  }

}

function printError($message)
{
$charset = 'utf-8';
header('Content-type: text/xml; charset=' . $charset);
echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");
?>
<error>
  <date><?php echo date("d/m/y:H:i:s", time()); ?></date>
  <code>-1</code>
  <description><?php echo($message); ?></description>
</error>
<?php
      exit;

}


function htmlSmilies($message, $path)
{

   $smilie[0] = ':-)';
   $smilieImage[0] = 'smilie_01.gif';
   $smilie[1] = ':-(';
   $smilieImage[1] = 'smilie_02.gif';
   $smilie[2] = '$-D';
   $smilieImage[2] = 'smilie_03.gif';
   $smilie[3] = ';-P';
   $smilieImage[3] = 'smilie_04.gif';
   $smilie[4] = ':-/';
   $smilieImage[4] = 'smilie_05.gif';
   $smilie[5] = ':(';
   $smilieImage[5] = 'smilie_06.gif';
   $smilie[6] = "8-)";
   $smilieImage[6] = 'smilie_07.gif';
   $smilie[7] = ":)";
   $smilieImage[7] = 'smilie_08.gif';
   $smilie[8] = ':-|';
   $smilieImage[8] = 'smilie_09.gif';
   $smilie[9] = ':--';
   $smilieImage[9] = 'smilie_10.gif';
   $smilie[10] = '/-|';
   $smilieImage[10] = 'smilie_11.gif';
   $smilie[11] = ':-O';
   $smilieImage[11] = 'smilie_12.gif';

   for($i = 0; $i < count($smilie); $i++)
   {
      $message = str_replace($smilie[$i], '<image src="' . $path . $smilieImage[$i] . '">', $message);
   }
   return $message;
}

function time_layout($unixtime)
{

   global $minutes_label;
   global $hours_label;

   $minutes = (int)($unixtime / 60);
   if ($minutes > 60)
   {
      $hours = (int)(($unixtime / 60) / 60);
      $minutes = (int)(($unixtime / 60) - ($hours * 60));

      if ($minutes < 10)
      {
         $minutes = '0' . (int)(($unixtime / 60) - ($hours * 60));
      }

      $seconds = ($unixtime % 60);

      if ($seconds < 10)
      {
         $seconds = '0' . ($unixtime % 60);
      }
      return $hours . ':' . $minutes . ':' . $seconds . ' ' . $hours_label;
   }
   else
   {
      if ($minutes < 10)
      {
         $minutes = '0' . (int)($unixtime / 60);
      }

      $seconds = ($unixtime % 60);

      if ($seconds < 10)
      {
         $seconds = '0' . ($unixtime % 60);
      }
      return $minutes . ':' . $seconds . ' ' . $minutes_label;
   }
}

function pendingUsersPopup($timeout)
{

   global $table_prefix;
   global $departments;
   global $current_department;
   global $SQL;

   // PENDING USERS QUERY displays pending users not logged in on users users table depending on department settings
   if ($departments == true)
   {
      $departments_sql = departmentsSQL($current_department);
      $query = "SELECT DISTINCT (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`)) AS `display` FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$timeout' AND active = '0' AND $departments_sql";
   }
   else
   {
      $query = "SELECT DISTINCT (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`)) AS `display` FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$timeout' AND active = '0'";
   }
   $rows = $SQL->selectall($query);

   // Initalise user status to false
   $user_status = 'false';

   if (is_array($rows))
   {
      foreach ($rows as $key => $row)
      {
         if (is_array($row))
         {
            $display_flag = $row['display'];
            if ($display_flag < $timeout)
            {
               $user_status = 'true';
            }
         }
      }
   }

   return $user_status;
}

function browsingUsersPopup($timeout)
{

   global $table_prefix;
   global $SQL;

   // BROWSING USERS QUERY displays browsing users
   $query = "SELECT DISTINCT (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`request`)) AS `display` FROM " . $table_prefix . "requests WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`request`)) < '$timeout' AND status = '0'";
   $rows = $SQL->selectall($query);

   // Initalise user status to false
   $user_status = 'false';

   $count = 0;
   if (is_array($rows))
   {
      foreach ($rows as $key => $row)
      {
         if (is_array($row))
         {
            $display_flag = $row['display'];

            if ($display_flag < $timeout)
            {
               $user_status = 'true';
            }
         }
      }
   }

   return $user_status;
}


function transferredUsersPopup($timeout)
{

   global $table_prefix;
   global $operator_login_id;
   global $SQL;


   //error_log("3. operator_login_id :". $operator_login_id."\n", 3, "func.log");

   // TRANSFERRED USERS QUERY displays transferred users not loged in on users users table
   $query = "SELECT DISTINCT (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`)) AS display FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$timeout' AND active = '-2' AND `transfer` = '$operator_login_id'";
   $rows = $SQL->selectall($query);

   // Initalise user status to false
   $user_status = 'false';

   if (is_array($rows))
   {
      foreach ($rows as $key => $row)
      {
         if (is_array($row))
         {
            $display_flag = $row['display'];

            if ($display_flag < $timeout)
            {
               $user_status = 'true';
            }
         }
      }
   }

   return $user_status;
}

function totalPendingUsers()
{

   global $table_prefix;
   global $connection_timeout;
   global $SQL;

   // PENDING USERS QUERY displays pending site visitors
   $query = "SELECT count(`id`) FROM " . $table_prefix . "sessions WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND `active` = '0'";
   $row = $SQL->selectquery($query);

   // Initalise user status to false
   $total_users = '0';
   if (is_array($row))
   {
      $total_users = $row['count(login_id)'];
   }

   return $total_users;
}

function totalBrowsingUsers()
{

   global $table_prefix;
   global $connection_timeout;
   global $SQL;

   // BROWSING USERS QUERY displays browsing users
   $query = "SELECT count(`id`) FROM " . $table_prefix . "requests WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout'";
   $row = $SQL->selectquery($query);

   // Initalise user status to false
   $total_users = '0';
   if (is_array($row))
   {
      $total_users = $row['count(`id`)'];
   }

   return $total_users;
}

function departmentsSQL($department)
{

   $multi_departments = split ('[;]', $department);
   $sql = '';

   if (is_array($multi_departments))
   {
      $i = 0;
      $length = count($multi_departments);
      if ($length > 1)
      {
         while ($i < $length) :
            $department = trim(addslashes($multi_departments[$i]));
         if ($i == 0)
         {
            $sql = "( `department` = '$department'";
         }
         elseif ($i > 0 && $i < $length - 1)
         {
            $sql .= " OR `department` = '$department'";
         }
         elseif ($i == $length - 1)
         {
            $sql .= " OR `department` = '$department' OR `department` = '' )";
         }
         $i++;
         endwhile;
      }
      else
      {
         $sql = "( `department` = '$department' OR `department` = '' )";
      }
   }
   else
   {
      $sql = "( `department` = '$department' OR `department` = '' )";
   }
   return $sql;
}

function xmlinvalidchars($string)
{
   $string = str_replace(array('>', '<', '"', '&'), array('&gt;', '&lt;', '&quot;', '&amp;'), $string);
   return $string;
}

function unixtimestamp($datetime)
{

   $datetime = explode(" ", $datetime);
   $date = explode("-", $datetime[0]);
   $time = explode(":", $datetime[1]);
   unset($datetime);

   list($year, $month, $day) = $date;
   list($hour, $minute, $second) = $time;

   return mktime(intval($hour), intval($minute), intval($second), intval($month), intval($day), intval($year));

}

function writeToLog($msg)
{
   $f = fopen("debug.txt", "a");
   fwrite($f, $msg . "\n");
   fclose($f);
}

function doTemplateReplace($q1, $q2)
{
   $pieces = explode("<//-", $q1);
   for($i = 0; $i < count($pieces); $i++)
   {
      if(strpos($pieces[$i], "-//>") > 0)
      {
         $pieces[$i] = substr($pieces[$i], 0, strpos($pieces[$i], "-//>"));
      }
      else
      {
         $pieces[$i] = "";
      }
   }
   for($i = 0; $i < count($pieces); $i++)
   {
      if($pieces[$i] != "")
      {
         $q1 = str_replace("<//-" . $pieces[$i] . "-//>", $q2[$pieces[$i]], $q1);
      }
   }
   return $q1;
}
?>