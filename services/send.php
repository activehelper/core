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

ignore_user_abort(true);

if (!isset($_REQUEST['ID']))
{
   $_REQUEST['ID'] = '';
} else $_REQUEST['ID'] = (int) $_REQUEST['ID'];
if (!isset($_REQUEST['MESSAGE']))
{
   $_REQUEST['MESSAGE'] = '';
} else $_REQUEST['MESSAGE'] = (string) $_REQUEST['MESSAGE'];
if (!isset($_REQUEST['STAFF']))
{
   $_REQUEST['STAFF'] = '';
} else $_REQUEST['STAFF'] = (bool) $_REQUEST['STAFF'];
if (!isset($_REQUEST['TYPE']))
{
   $_REQUEST['COMMANDTYPE'] = '';
}  else $_REQUEST['TYPE'] = htmlspecialchars( (string) $_REQUEST['TYPE'], ENT_QUOTES );
if (!isset($_REQUEST['NAME']))
{
   $_REQUEST['COMMANDNAME'] = '';
} else $_REQUEST['NAME'] = htmlspecialchars( (string) $_REQUEST['NAME'], ENT_QUOTES );
if (!isset($_REQUEST['CONTENT']))
{
   $_REQUEST['CONTENT'] = '';
} else $_REQUEST['CONTENT'] = htmlspecialchars( (string) $_REQUEST['CONTENT'], ENT_QUOTES );

$current_username = $operator_name;

$to = $_REQUEST['ID'];
$message = $_REQUEST['MESSAGE'];
$staff = $_REQUEST['STAFF'];
$type = $_REQUEST['TYPE'];
$name = $_REQUEST['NAME'];
$content = $_REQUEST['CONTENT'];
$result = '0';

$operator_name = $current_first_name . " " . $current_last_name;

// Get id_domain for this message
$query = "SELECT s.id_domain FROM " . $table_prefix . "sessions s, " . $table_prefix .
         "requests r WHERE s.id = " . $to . " And r.id = s.request";

$rows = $SQL->selectall($query);
if (is_array($rows))
{
   foreach ($rows as $key => $row)
   {
      if (is_array($row))
      {
         $id_domain = $row['id_domain'];
      }
   }
}

//TODO REVISAR ESTO
if ($disable_chat_username == true)
{
   $current_username = '';
}


// Check if the message contains any content else return headers
if ($message == '' && $type == '' && $name == '' && $content == '')
{
   $charset = 'utf-8';
   header('Content-type: text/xml; charset=' . $charset);
   echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");
?>
<SendMessage xmlns="urn:LiveHelp"><?php echo($message_result);
?></SendMessage>
<?php
   exit();
}

if ($type != '' && $name != '' && $content != '')
{

   // Strip the slashes because slashes will be added to whole string
   $name = stripslashes(trim($name));
   $content = stripslashes(trim($content));
   $operator = '';

   switch ($type)
   {
      case 'LINK':
         $type = 2;
         $command = addslashes($name . " \r\n " . $content);
         break;
      case 'IMAGE':
         $type = 3;
         $command = addslashes($name . " \r\n " . $content);
         break;
      case 'PUSH':
         $type = 4;
         $command = addslashes($content);
         $operator = addslashes('The ' . $name . ' has been PUSHed to the visitor.');
         break;
      case 'JAVASCRIPT':
         $type = 5;
         $command = addslashes($content);
         $operator = addslashes('The ' . $name . ' has been sent to the visitor.');
         break;
   }

   if ($command != '')
   {
      $query = "INSERT INTO " . $table_prefix . "messages ( `session`, `datetime`,".
               " `message`, `align`, `status`, id_domain) VALUES ('$to', NOW(), '$command', '2', '$type', $id_domain)";

      if ($operator != '')
      {
         $query .= ", ('', '$to', NOW(), '$operator', '2', '-1')";
      }
      $id = $SQL->insertquery($query);
      if ($id != false)
      {
         $result = '1';
      }
   }
}

// Format the message string
$message = trim($message);

if ($message != '')
{

  //error_log("Send:".$message."\n", 3, "../error.log");
   $message = str_replace ('<21>', '!', $message);
   $message = str_replace ('<2A>', '*', $message);
   $message = str_replace ('<27>', "'", $message);
   $message = str_replace ('<28>', '(', $message);
   $message = str_replace ('<29>', ')', $message);
   $message = str_replace ('<3B>', ';', $message);
   $message = str_replace ('<3A>', ':', $message);
   $message = str_replace ('<40>', '@', $message);
   $message = str_replace ('<26>', '&', $message);
   $message = str_replace ('<3D>', '=', $message);
   $message = str_replace ('<2B>', '+', $message);
   $message = str_replace ('<24>', '$', $message);
   $message = str_replace ('<2C>', ',', $message);
   $message = str_replace ('<2F>', '/', $message);
   $message = str_replace ('<3F>', '?', $message);
   $message = str_replace ('<25>', '%', $message);
   $message = str_replace ('<23>', '#', $message);
   $message = str_replace ('<5B>', '[', $message);
   $message = str_replace ('<5D>', ']', $message);
   //error_log("Send:".$message."\n", 3, "../error.log");
   $message = addslashes($message);

   if (!$staff)
   {
      // Send messages from POSTed data
      $query = "INSERT INTO " . $table_prefix . "messages ( `session`, `username`,".
               " `datetime`, `message`, `align`, `status`, id_domain, id_user) VALUES( '$to',".
               " '$operator_name', NOW(), '$message', '1', $type, $id_domain, $operator_login_id)";


      $id = $SQL->insertquery($query);
      if ($id != false)
      {
         $result = '1';
      }
   }
   else
   {

   /*   $query = "INSERT INTO " . $table_prefix . "administration ( `user`, `username`,".
               " `datetime`, `message`, `align`, `status`) VALUES( '$to', '$operator_name', NOW(), '$message', '1', '1', $id_domain)";
     */

       $query = "INSERT INTO " . $table_prefix . "administration ( `user`, `username`, `operator_id` ,".
               " `datetime`, `message` , `align`, `status`) VALUES( '$to', '$operator_name', '$operator_login_id' , NOW(), '$message' , '1', '1')";

      $id = $SQL->insertquery($query);

      if ($id != false)
      {
         $result = '1';
      }
   }
}

$charset = 'utf-8';
header('Content-type: text/xml; charset=' . $charset);
echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");?><SendMessage xmlns="urn:LiveHelp" Result="<?php echo($result);?>"></SendMessage>
