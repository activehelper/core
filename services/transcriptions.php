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
if (!isset($_REQUEST['VISITOR_EMAIL']))
{
   $_REQUEST['VISITOR_EMAIL'] = '';
} else $_REQUEST['VISITOR_EMAIL'] = htmlspecialchars ( (string) $_REQUEST['VISITOR_EMAIL'], ENT_QUOTES );
if (!isset($_REQUEST['DATE_START']))
{
   $_REQUEST['DATE_START'] = '';
} else $_REQUEST['DATE_START'] = htmlspecialchars ( (string) $_REQUEST['DATE_START'], ENT_QUOTES );
if (!isset($_REQUEST['DATE_END']))
{
   $_REQUEST['DATE_END'] = '';
} else $_REQUEST['DATE_END'] = htmlspecialchars ( (string) $_REQUEST['DATE_END'], ENT_QUOTES );

if (!isset($_REQUEST['TRANSCRIPTION']))
{
   $_REQUEST['TRANSCRIPTION'] = '';
} else $_REQUEST['TRANSCRIPTION'] = htmlspecialchars ( (string) $_REQUEST['TRANSCRIPTION'], ENT_QUOTES );

if (!isset($_REQUEST['OPERATORID']))
{
   $operator_login_id = $_REQUEST['OPERATORID'];
} $operator_login_id = $_REQUEST['OPERATORID'] = (int) $_REQUEST['OPERATORID'];

$action           = $_REQUEST['ACTION'];
$request          = $_REQUEST['REQUEST'];
$visitor_email    = $_REQUEST['VISITOR_EMAIL'];
$transcription_id = $_REQUEST['TRANSCRIPTION'];
$date_start       = $_REQUEST['DATE_START'];
$date_end         = $_REQUEST['DATE_END'];


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


// get the agent security level

if ($action == 'list')
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


 if ($transcription_id != '')
    {
     $query_where = "jls.id =" .$transcription_id ;
    }
   else
 if ($visitor_email != '')
   {
      $query_where = "jls.email = '$visitor_email' "; 
   }
 else   
 if ($date_start != '' && $date_end != '')
   {
      $query_where = "DATE_FORMAT(jls.datetime, '%Y-%m-%d')>= '$date_start' and DATE_FORMAT(jls.datetime, '%Y-%m-%d')<= '$date_end' "; 
   }


   if ($privilege == '0' )
   {
       $query = "select  jls.id, jls.username , jls.company, jls.phone, jld.name , DATE_FORMAT(jls.refresh,'%m/%d/%Y') date , (TIMEDIFF(jls.refresh, jls.datetime)) Duration ,  jls.email ".
                "from " . $table_prefix . "sessions  jls , " . $table_prefix . "domains jld  ".
                "where ". "$query_where" . " and jls.id_user = '$operator_login_id'  and  jls.id_domain =  jld.id_domain";
   }
   else
 if ($privilege == '1' )  
   {
            $query = "select  jls.id, jls.username , jls.company, jls.phone, jld.name , DATE_FORMAT(jls.refresh,'%m/%d/%Y') date , (TIMEDIFF(jls.refresh, jls.datetime)) Duration ,  jls.email ".
                "from " . $table_prefix . "sessions  jls , "  . $table_prefix . "domain_user jldu , " . $table_prefix . "domains jld  ".
                "where jldu.id_user = '$operator_login_id' and jls.id_domain = jldu.id_domain and " . "$query_where" .  " and  jls.id_domain =  jld.id_domain";
   }

?>

<Transcriptions>
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
               $current_domain   = $row['name'];               
               $current_date     = $row['date'];
               $current_duration = $row['Duration'];
               $current_email    = $row['email'];
               $current_company  = $row['company'];
               $current_phone    = $row['phone'];
               
              
?>
<Transcription>
<Id><?php echo(xmlinvalidchars($current_chat_id));?></Id>
<domain><?php echo(xmlinvalidchars($current_domain));?></domain>
<Username><?php echo(xmlinvalidchars($current_username));?></Username>
<Email><?php echo(xmlinvalidchars($current_email));?></Email>
<Company><?php echo(xmlinvalidchars($current_company));?></Company>
<Phone><?php echo(xmlinvalidchars($current_phone));?></Phone>
<Date><?php echo(xmlinvalidchars($current_date));?></Date>
<Duration><?php echo(xmlinvalidchars($current_duration));?></Duration>
</Transcription>

<?php
  }
?>

<?php
 }
?>
<?php
 }
?>
</Transcriptions>
