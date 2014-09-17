<?php
ob_start("ob_gzhandler");

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
if (!isset($_REQUEST['REQUEST']))
{
   $_REQUEST['REQUEST'] = '';
} else $_REQUEST['REQUEST'] = htmlspecialchars ( (string) $_REQUEST['REQUEST'], ENT_QUOTES );

if (!isset($_REQUEST['TRANSCRIPTION']))
{
   $_REQUEST['TRANSCRIPTION'] = '';
} else $_REQUEST['TRANSCRIPTION'] = (int) $_REQUEST['TRANSCRIPTION'];

if (!isset($_REQUEST['OPERATORID']))
{
   $operator_login_id = $_REQUEST['OPERATORID'];
} $operator_login_id = $_REQUEST['OPERATORID'] = (int) $_REQUEST['OPERATORID'];

$action           = $_REQUEST['ACTION'];
$request          = $_REQUEST['REQUEST'];
$transcription_id = $_REQUEST['TRANSCRIPTION'];


define('LANGUAGE_TYPE', $language);

$language_file = '../i18n/' . LANGUAGE_TYPE . '/lang_service_' . LANGUAGE_TYPE . '.php';
if (LANGUAGE_TYPE != '') {
        include($language_file);
}
else {
        include('../i18n/en/lang_service_en.php');
}

$charset = 'utf-8';
header('Content-type: text/xml; charset=' . $charset);
/*echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");*/

if ($action == 'read')
{

  $query = "select privilege from " . $table_prefix . "users where `id` =" .$operator_login_id ;
  
  $row = $SQL->selectquery($query);
  if (is_array($row))
      {
         $privilege  = $row['privilege'];         
      }       
  }
?>

<?php

   $query = "SELECT id, username, message  FROM " . $table_prefix .
            "messages WHERE session =" . $transcription_id .
            " ORDER BY datetime";
            
?>

<Messages>
<?php      
      $rows = $SQL->selectall($query);
      if (is_array($rows))
      {
         foreach ($rows as $key => $row)
         {

            if (is_array($row))
            {
               $current_chat_id  = $row['id'];
               $current_username = $row['username'];
               $current_message  = $row['message'];                              
              
?>
<Message>
<Username><?php echo(xmlinvalidchars($current_username));?></Username>
<Line><![CDATA[<?php echo($current_message);?>]]></Line>
</Message>
<?php
  }
?>
<?php
 }
?>
<?php
 }
?>
</Messages>
