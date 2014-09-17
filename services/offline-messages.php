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
} else $_REQUEST['ACTION'] = htmlspecialchars( (string) $_REQUEST['ACTION'], ENT_QUOTES );
if (!isset($_REQUEST['REQUEST']))
{
   $_REQUEST['REQUEST'] = '';
} else $_REQUEST['REQUEST'] = htmlspecialchars( (string) $_REQUEST['REQUEST'], ENT_QUOTES );
if (!isset($_REQUEST['MESSAGE']))
{
   $_REQUEST['MESSAGE'] = '';
} else $_REQUEST['MESSAGE'] = htmlspecialchars( (string) $_REQUEST['MESSAGE'], ENT_QUOTES );

if (!isset($_REQUEST['ANSWERED']))
{
   $_REQUEST['ANSWERED'] = '';
} else $_REQUEST['ANSWERED'] = (int) $_REQUEST['ANSWERED'];

if (!isset($_REQUEST['DATE_START']))
{
   $_REQUEST['DATE_START'] = '';
} else $_REQUEST['DATE_START'] = htmlspecialchars( (string) $_REQUEST['DATE_START'], ENT_QUOTES );
if (!isset($_REQUEST['DATE_END']))
{
   $_REQUEST['DATE_END'] = '';
} else $_REQUEST['DATE_END'] = htmlspecialchars( (string) $_REQUEST['DATE_END'], ENT_QUOTES );

if (!isset($_REQUEST['OPERATORID']))
{
   $operator_login_id = $_REQUEST['OPERATORID'];
} $operator_login_id = $_REQUEST['OPERATORID'] = (int) $_REQUEST['OPERATORID'];

$action        = $_REQUEST['ACTION'];
$request       = $_REQUEST['REQUEST'];
$message_id    = $_REQUEST['MESSAGE'];
$answered      = $_REQUEST['ANSWERED'];
$date_start    = $_REQUEST['DATE_START'];
$date_end      = $_REQUEST['DATE_END'];

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

# update the message status
if ($action == 'update')
 {
     $query = "update ". $table_prefix ."offline_messages " . " set answered =" . $answered ." where id = ". $message_id ;
     $SQL->miscquery($query);
   }  
    
  $query = "select privilege from " . $table_prefix . "users where `id` =" .$operator_login_id ;
  
  $row = $SQL->selectquery($query);
  if (is_array($row))
      {
         $privilege  = $row['privilege'];
         
      }       

   if ($privilege == '0' )
   {
       $query = "select  jlom.id , jlom.name , jlom.email ,  jlom.company, jlom.phone, DATE_FORMAT(jlom.datetime,'%m/%d/%Y') date , jld.name Domain ,  jlom.message ".
                " from " . $table_prefix . "offline_messages jlom , " . $table_prefix . "domains jld , " . $table_prefix . "domain_user  jldu ".
                " where jldu.id_user =" . $operator_login_id . " and  jlom.id_domain = jldu.id_domain and DATE_FORMAT(jlom.datetime, '%Y-%m-%d')>= '$date_start' and DATE_FORMAT(jlom.datetime, '%Y-%m-%d')<='$date_end' and " . 
                " jlom.id_domain =jld.id_domain and jlom.answered = 0 "; 
                            
   }
   else
 if ($privilege == '1' )  
   {
            $query = "select  jlom.id , jlom.name , jlom.email , jlom.company, jlom.phone, DATE_FORMAT(jlom.datetime,'%m/%d/%Y') date , jld.name Domain ,  jlom.message ".
                     " from " . $table_prefix . "offline_messages jlom , " . $table_prefix . "domains jld " .
                     " where DATE_FORMAT(jlom.datetime, '%Y-%m-%d')>= '$date_start' and DATE_FORMAT(jlom.datetime, '%Y-%m-%d')<='$date_end' and ".
                     " jlom.id_domain =jld.id_domain and jlom.answered = 0 ";                     
                             
   }
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
               $current_message_id   = $row['id'];
               $current_message_name = $row['name'];
               $current_email        = $row['email'];               
               $current_date         = $row['date'];
               $current_domain       = $row['Domain'];
               $current_message      = $row['message'];
               $current_company  = $row['company'];
               $current_phone    = $row['phone'];
            }  
              
?>
<Message>
<Id><?php echo(xmlinvalidchars($current_message_id));?></Id>
<Name><?php echo(xmlinvalidchars($current_message_name));?></Name>
<Email><?php echo(xmlinvalidchars($current_email));?></Email>
<Company><?php echo(xmlinvalidchars($current_company));?></Company>
<Phone><?php echo(xmlinvalidchars($current_phone));?></Phone>
<Date><?php echo(xmlinvalidchars($current_date));?></Date>
<Domain><?php echo(xmlinvalidchars($current_domain));?></Domain>
<Comment><?php echo(xmlinvalidchars($current_message));?></Comment>
</Message>
<?php
 }
?>
<?php
 }
?>
</Messages>
<?php
/*}*/
?>
