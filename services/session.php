<?php
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

  $id = $_REQUEST['ID'];


  // Get id_domain for this message
  $query = "update " . $table_prefix . "sessions set active = -1 where id = " . $id;

  $charset = 'utf-8';
  header('Content-type: text/xml; charset=' . $charset);
  echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");

  if ($SQL->miscquery($query))
  {
     echo('<chat_session><enable>true</enable></chat_session>');
  }
  else
  {
     echo('<chat_session><enable>false</enable></chat_session>');
  }
?>
