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
}
if (!isset($_REQUEST['REQUEST']))
{
   $_REQUEST['REQUEST'] = '';
}
if (!isset($_REQUEST['MESSAGE']))
{
   $_REQUEST['MESSAGE'] = '';
}

if (!isset($_REQUEST['EMAIL']))
{
   $_REQUEST['EMAIL'] = '';
}

if (!isset($_REQUEST['SUBJECT']))
{
   $_REQUEST['SUBJECT'] = '';
}

if (!isset($_REQUEST['VISITOR_ID']))
{
   $_REQUEST['VISITOR_ID'] = '';
}


$action        = $_REQUEST['ACTION'];
$request       = $_REQUEST['REQUEST'];
$message       = $_REQUEST['MESSAGE'];
$email         = $_REQUEST['EMAIL'];
$subject       = $_REQUEST['SUBJECT'];
$visitor       = $_REQUEST['VISITOR_ID'];


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

if ($action == 'send')
{

 // Get domain ID
        $query = "SELECT id_domain FROM " . $table_prefix . "sessions jls  where jls.request = $visitor ";
                $row = $SQL->selectquery($query);
                if (is_array($row)) {
                        $domainId = $row['id_domain'];
                }


       // from email                               
        $query = "SELECT value FROM " . $table_prefix . "settings where name = 'from_email' and id_domain = $domainId";
                $row = $SQL->selectquery($query);
                if (is_array($row)) {
                        $from_email = $row['value'];
                }
        
       // from name         
        $query = "SELECT value FROM " . $table_prefix . "settings where name = 'site_name' and id_domain = $domainId";
                $row = $SQL->selectquery($query);
                if (is_array($row)) {
                        $from_name = $row['value'];
                }
                
                
  if (($from_email != '') && ($from_name != '') && ($email != ''))
    { 
        mail($email,  '=?utf-8?B?'.base64_encode($subject).'?=' , $message, $headers);
  ?>
       
       <Messages>
         <Message>Message was send</Message>
       </Messages>

  <?php           
    }            
    
  }
?>

