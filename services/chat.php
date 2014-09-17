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

if (!isset($_REQUEST['ID']))
{
   $_REQUEST['ID'] = '';
} else $_REQUEST['ID'] = (int) $_REQUEST['ID'];
if (!isset($_REQUEST['MESSAGE']))
{
   $_REQUEST['MESSAGE'] = '';
} else $_REQUEST['MESSAGE'] = htmlspecialchars ( (string) $_REQUEST['MESSAGE'], ENT_QUOTES );
if (!isset($_REQUEST['STAFF']))
{
   $_REQUEST['STAFF'] = '';
} else $_REQUEST['STAFF'] = (bool) $_REQUEST['STAFF'];
if (!isset($_REQUEST['TYPING']))
{
   $_REQUEST['TYPING'] = '';
} else $_REQUEST['TYPING'] = (bool) $_REQUEST['TYPING'];

if (!isset($_REQUEST['OPERATORID']))
{
   $operator_login_id = $_REQUEST['OPERATORID'];
} $operator_login_id = $_REQUEST['OPERATORID'] = (int) $_REQUEST['OPERATORID'];

$current_typing_status = $_REQUEST['TYPING'];
$guest_login_id = $_REQUEST['ID'];
$staff = $_REQUEST['STAFF'];
$message = $_REQUEST['MESSAGE'];

if (!$staff)
{
   $query = "SELECT `server`, `active` FROM " . $table_prefix . "sessions WHERE `id` = '$guest_login_id'";
   $row = $SQL->selectquery($query);
   if (is_array($row))
   {
      $server = $row['server'];
      $active = $row['active'];
   }
   else
   {
      $server = '';
      $active = '';
   }
}
else
{
   $active = $guest_login_id;
   $server = '';
}

if ($active > 0 && !$staff)
{

   // Setup the chat for monitoring
   $query = "SELECT `email`, `typing` FROM " . $table_prefix . "sessions WHERE `id` = '$guest_login_id'";
   $row = $SQL->selectquery($query);
   if (is_array($row))
   {
      $typing = $row['typing'];
      $email = $row['email'];

      if ($current_typing_status)
      {// Currently Typing
         switch($typing)
         {
            case 0:
               $typingresult = 2;
               break;
            case 1:
               $typingresult = 3;
               break;
            case 2:
               $typingresult = 2;
               break;
            case 3:
               $typingresult = 3;
               break;
         }
      }
      else
      {// Not Currently Typing
         switch($typing)
         {
            case 0:
               $typingresult = 0;
               break;
            case 1:
               $typingresult = 1;
               break;
            case 2:
               $typingresult = 0;
               break;
            case 3:
               $typingresult = 1;
               break;
         }
      }

      // Update the typing status of the specified chatting visitor
      $query = "UPDATE " . $table_prefix . "sessions SET `typing` = '$typingresult' WHERE `id` = '$guest_login_id'";
      $SQL->miscquery($query);
   }
}
else
{
   $typingresult = 0;
}

if ($staff)
{
 /*  $query = "SELECT `id`, `user`, `username`, `message`, `align`, `status` FROM " . $table_prefix .
            "administration WHERE (`user` = '$guest_login_id' OR `user` = '$operator_login_id') AND".
            " `status` <= '3' AND `id` > '$message' AND (UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP".
            "('$login_datetime')) > '0' And id_domain in (" . $domains_set . ") ORDER BY `datetime`";

   */

 $query = "SELECT `id`, `user`, `username`, `message`, `align`, `status` FROM " . $table_prefix .
            "administration WHERE (`user` = '$guest_login_id' OR `user` = '$operator_login_id') AND (`operator_id` = '$guest_login_id' OR `operator_id` = '$operator_login_id') AND".           
            " `status` <= '3' AND `id` > '$message' AND (UNIX_TIMESTAMP(`datetime`) - UNIX_TIMESTAMP".
            "(NOW() - INTERVAL 1 DAY)) > '0' ORDER BY `datetime`";

//  error_log("SQL ". $query. "\n", 3, "chat.log");           
}
else
{
   $query = "SELECT `id`, `session`, `username`, `message`, `align`, `status`, id_user FROM " . $table_prefix .
            "messages WHERE `session` = '$guest_login_id' AND `status` <= '6' AND `id` > '$message' And id_domain in (" .
            $domains_set . ") ORDER BY `datetime`";
               
}
$rows = $SQL->selectall($query);
if (is_array($rows))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $lastmessage = $row['id'];
      }
   }
}
else
{
   $lastmessage = '';
}


//error_log("memory usage :". number_format(memory_get_usage(), 0, '.', ',')." memory peak usage :".number_format(memory_get_peak_usage(), 0, '.', ',')." memory end usage :".number_format(memory_get_usage(), 0, '.', ',') ."\n", 3, "memory.log");

$charset = 'utf-8';
header('Content-type: text/xml; charset=' . $charset);
echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");
?><Messages User="<?php echo($guest_login_id);?>" ID="<?php echo($lastmessage);?>" Typing="<?php echo($typingresult);?>" Status="<?php echo($active);?>" Server="<?php echo(xmlinvalidchars($server));?>" Email="<?php echo(xmlinvalidchars($email));?>">
<?php
if (is_array($rows))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {

         if ($staff)
         {
            $id = $row['user'];
         }
         else
         {
            $id = $row['session'];
         }
         $username = xmlinvalidchars($row['username']);
         //$message = xmlinvalidchars($row['message']);
         $message = $row['message'];
         $message = stripslashes($message);

         $align = $row['align'];
         $status = $row['status'];

         $id_user = $row['id_user'];

         // Outputs sent message
         if ((!$staff && $status) || ($staff && $id == $operator_login_id))
         {
?>
<Message ID="<?php echo($id_user);?>" Align="<?php echo($align);?>" Username="<?php echo($username);?>" messageType="<?php echo($status);?>"><![CDATA[<?php echo($message);?>]]></Message>
<?php
         }
         // Outputs received message
         if ((!$staff && !$status) || ($staff && $id == $guest_login_id))
         {
?>
<Message ID="<?php echo($id_user);?>" Align="<?php echo($align);?>" Username="<?php echo($username)?>"  messageType="<?php echo($status);?>"><![CDATA[<?php echo($message);?>]]></Message>
<?php
         }
      }
   }
}
?>
</Messages>
