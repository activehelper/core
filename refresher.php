<?php
include_once('import/constants.php');
include_once('import/functions.php');

include('./import/config_database.php');
include('./import/class.mysql.php');
include('./import/config.php');

if (!isset($_REQUEST['JS'])){ $_REQUEST['JS'] = false; }
if (!isset($_REQUEST['INIT'])){ $_REQUEST['INIT'] = false; }
if (!isset($_REQUEST['TYPING'])){ $_REQUEST['TYPING'] = ''; }
if (!isset($_REQUEST['TIME'])){ $_REQUEST['TIME'] = ''; }
if (!isset($_REQUEST['COOKIE'])){ $_REQUEST['COOKIE'] = ''; }

$javascript = (bool) $_REQUEST['JS'];
$initalised = $_REQUEST['INIT'];
$status = (int) $_REQUEST['TYPING'];
$lastMessageID = (int) $_REQUEST['lastMessageID'];

if ($_REQUEST['COOKIE'] != '') {
        $cookie_domain = htmlspecialchars( (string) $_REQUEST['COOKIE'], ENT_QUOTES );
}
/*
//error_log("Refresher:setCookie      \n", 3, "/var/www/html/error.log");
foreach ($_COOKIE as $key => $value) {
 //error_log("Refresher:setCookie: ".$_COOKIE[$key].":".$_COOKIE[$value]."\n", 3, "/var/www/html/error.log");
}
*/

if ($javascript == true) {

        $query = "SELECT `typing` FROM " . $table_prefix . "sessions WHERE `id` = '$guest_login_id'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $typing = $row['typing'];

                if (isset($_COOKIE['LiveHelpOperator'])) {
                        if ($status) { // Currently Typing
                                switch($typing) {
                                case 0: // None
                                        $result = 2;
                                        break;
                                case 1: // Guest Only
                                        $result = 3;
                                        break;
                                case 2: // Operator Only
                                        $result = 2;
                                        break;
                                case 3: // Both
                                        $result = 3;
                                        break;
                                }
                        }
                        else { // Not Currently Typing
                                switch($typing) {
                                case 0: // None
                                        $result = 0;
                                        break;
                                case 1: // Guest Only
                                        $result = 1;
                                        break;
                                case 2: // Operator Only
                                        $result = 0;
                                        break;
                                case 3: // Both
                                        $result = 1;
                                        break;
                                }
                        }
                } else {
                        if ($status) { // Currently Typing
                                switch($typing) {
                                case 0: // None
                                        $result = 1;
                                        break;
                                case 1: // Guest Only
                                        $result = 1;
                                        break;
                                case 2: // Operator Only
                                        $result = 3;
                                        break;
                                case 3: // Both
                                        $result = 3;
                                        break;
                                }
                        }
                        else { // Not Currently Typing
                                switch($typing) {
                                case 0: // None
                                        $result = 0;
                                        break;
                                case 1: // Guest Only
                                        $result = 0;
                                        break;
                                case 2: // Operator Only
                                        $result = 2;
                                        break;
                                case 3: // Both
                                        $result = 2;
                                        break;
                                }
                        }
                }

                // Update the typing status of the specified login id
                $query = "UPDATE " . $table_prefix . "sessions SET `typing` = '$result' WHERE `id` = '$guest_login_id'";
                $SQL->miscquery($query);

        }
}

// Check if an operator has accepted the chat request
$query = "SELECT `active`, `datetime`, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`)) AS `waiting` FROM " . $table_prefix . "sessions WHERE `id` = '$guest_login_id'";
$row = $SQL->selectquery($query);
if (is_array($row)) {
        $active = $row['active'];
        $datetime = $row['datetime'];
        $waiting = $row['waiting'];
}


$session = array();


$session1 = array();
if (isset($domain_id) && isset($_COOKIE[$cookieName])) {
  $session1 = unserialize($_COOKIE[$cookieName]);
}else {
  $session1 = unserialize($_COOKIE[$cookieName]);
}

foreach ($session1 as $key => $value) {
  $session[$key] =  $value;
}
unset($session1);


$session['REQUEST'] = $request_id;
$session['GUEST_LOGIN_ID'] = $guest_login_id;
$session['GUEST_USERNAME'] = $guest_username;
$session['SECURITY'] = $security;
$session['LANGUAGE'] = LANGUAGE_TYPE;
$session['CHARSET'] = CHARSET;

if (isset($domain_id)){
  $session['DOMAINID'] = $domain_id;
}else{
  $session['DOMAINID'] = $id_domain;
}


if($session['MESSAGE'] != $lastMessageID) {
        writeToLog($guest_login_id."==".$lastMessageID."==".$session['MESSAGE']);
}



if ($active > 0) {
        $session['CHATTING'] = 1;
}
else {
        $session['CHATTING'] = 0;
}

if ($active > 0 && $chatting > 0) {

/*
        if ($initalised) {
                $query = "SELECT `id`, `datetime`, `username`, `message`, `align`, `status` FROM " . $table_prefix . "messages WHERE `session` = '$guest_login_id' AND `status` >= '1' AND `id` > '$guest_message' ORDER BY `datetime`";
        } else {
                $query = "SELECT `id`, `datetime`, `username`, `message`, `align`, `status` FROM " . $table_prefix . "messages WHERE `session` = '$guest_login_id' AND `status` >= '0' AND `id` > '$guest_message' ORDER BY `datetime`";
        }*/
        $query = "SELECT `id`, `datetime`, `username`, `message`, `align`, `status` FROM " . $table_prefix . "messages WHERE `session` = '$guest_login_id' AND `status` >= '0' AND `id` > '$lastMessageID' And username <> '$guest_username' ORDER BY `datetime`";
        $rows = $SQL->selectall($query);
        if (is_array($rows)) {

                $messages = array();
                $messages = $rows;


                // Count the total operators in the current conversation
                $query = "SELECT count(DISTINCT `username`) as cnt FROM " . $table_prefix . "messages WHERE `session` = '$guest_login_id' AND `status` >= '0'";

                $row = $SQL->selectquery($query);
                if (is_array($row)) {
                        $operators = $row['cnt'];
                }
                $joined = $total_operators;

                foreach ($rows as $key => $row) {
                        if (is_array($row)) {
                                $guest_message = $row['id'];

                                // If message datetime is greater than datetime of session
//                              if ((unixtimestamp($row['datetime']) - unixtimestamp($datetime)) > 0) {
//                                      if ($operators > 0 && ($operators > $joined)) {
                                                $username = $row['username'];
                                                $status = $row['status'];

                                                // If the username is not equal to the original operator
                                                // and the message was from an operator
                                                // and the joined conversation system message has not been sent
                                                if (($operator_username != $username) && $status > 0) {

                                                        // Select supporters full name
                                                        $query = "SELECT `username`, `firstname`, `lastname` FROM " . $table_prefix . "users WHERE `username` = '$username'";
                                                        $row = $SQL->selectquery($query);
                                                        if (is_array($row)) {
                                                                $first = $row['firstname'];
                                                                $last = $row['lastname'];

                                                                if (!($first == '' || $last == '')) {
                                                                        // Send message to notify user they are out of Pending status
                                                                        $message_joined = "$first $last";
                                                                }
                                                        }
                                                        $joined++;
                                                        $session['TOTALOPERATORS'] = $operators;
                                                }
//                                      }
//                              }
                        }
                }
                $session['MESSAGE'] = $guest_message;
        }
}



if (!isset($session['MESSAGE'])) { $session['MESSAGE'] = $guest_message; }

if ($active > 0 && $chatting == 0) {
        $css = '<link href="' . $install_directory . '/style/styles.php?'.'DOMAINID='.$domain_id.'" rel="stylesheet" type="text/css"><script language="JavaScript">if (top.document.message_form) { top.document.message_form.MESSAGE.disabled = false; }</script>';

        // Select supporters full name
        $query = "SELECT `username`, `firstname`, `lastname` FROM " . $table_prefix . "users WHERE `id` = '$active'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $username = $row['username'];
                $first = $row['firstname'];
                $last = $row['lastname'];

                if (!($first == '' || $last == '')) {
                        // Send message to notify user they are out of Pending status
                        $message = "$first $last";
                }
        }

        if ($disable_chat_username == true) { $username = ''; }

        $session['OPERATOR'] = $username;
        $session['TOTALOPERATORS'] = $total_operators + 1;
}

if (!isset($session['OPERATOR'])) { $session['OPERATOR'] = $operator_username; }
if (!isset($session['TOTALOPERATORS'])) { $session['TOTALOPERATORS'] = $total_operators; }

$data = serialize($session);
setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);

// HTTP/1.1
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);

// HTTP/1.0
header('Pragma: no-cache');
//header('Content-type: text/html; charset=iso-8859-5');
header('Content-type: text/javascript; charset=' . CHARSET);

if ($domain_id > 0) {
$lan = $session['LANGUAGE'];

#error_log("domain : " . $domain_id  . " languaje : " . $lan, 3,  "languaje.log");

$query = "select  `welcome_message` from " . $table_prefix . "languages_domain where id_domain = $domain_id and code = '$lan'";
 $row = $SQL->selectquery($query);
      if (is_array($row)) {
          $wel_messg_18 = $row['welcome_message'];
          }
}

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';
if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}
if (isset($message_joined)) {
        $message_joined .= ' ' . $joined_conversation_label;
        $message_joined = addslashes($message_joined);
        if ($javascript == false) {
?>
<script language="JavaScript">
<!--
<?php
        }
?>
top.display('', '<?php echo($message_joined); ?>', '2', '1');
<?php
        if ($javascript == false) {
?>
//-->
</script>
<?php
        }
}
?>
top.lastMessageID = "<?php echo $guest_message; ?>"
<?php
if ($javascript == false) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<?php
}

if ($active > 0 && $chatting == 0) {
        if ($javascript == false) {
?>
<script language="JavaScript">
<!--
<?php
        }
?>

top.display('', '<?php echo($css); ?>', '2', '1');


<?php

//error_log("CSS : " .$css, 3, "error.log");


if (isset($message)) {
        $message = $now_chatting_with_label . ' ' . $message;
        $message = addslashes($message);
?>

top.display('', '<?php echo($message); ?>', '1', '1');

<?php
}

$welcome = addslashes($wel_messg_18);
$ref_nomber_message = addslashes($ref_nomber_message_i18).$guest_login_id;

// Get aget photo
  $query = "SELECT jlu.`id`, `photo` FROM " . $table_prefix . "sessions jls, "  . $table_prefix . "users jlu  WHERE jls.`id` = '$guest_login_id' and jlu.`id` = jls.`id_user` ";
  $row = $SQL->selectquery($query);
  if (is_array($row)) {
     $agent_img = $row['photo'];
     $agent_folder_id = $row['id']; 
    }

$img_path ="./agents/" . $agent_folder_id .'/';

?>

 top.display('', '<?php echo($ref_nomber_message); ?>', '1', '1');
 top.display('<?php echo($first." ".$last); ?>', '<?php echo($welcome); ?>', '1', '1');

<?php if ($agent_img !='') {  ?>
  window.parent.document.getElementById('ImageID').src ='<?php echo( $img_path . $agent_img ); ?>';
<?php }  ?>

<?php
        if ($javascript == false) {
?>
//-->
</script>

<?php
        }
}
elseif ($active == -3 || $active == -1) {
        // Send message to notify user the chat is closed or declined and send JavaScript to disable input
        $message = addslashes('<link href="/' . $install_directory . '/style/styles.php?'.'DOMAINID='.$domain_id.'" rel="stylesheet" type="text/css">' . $closed_user_message_label . ' ' . $improve_service_label . ' <a href="#" onClick="top.windowLogout(); return false;" class="message">' . $rate_operator_label . '</a>');
        $close = addslashes('<script language="JavaScript">top.document.message_form.MESSAGE.disabled = true; top.window.clearTimeout(top.MessageTimer);</script>');

        if ($javascript == false) {
?>
<script language="JavaScript">
<!--
<?php
        }
?>
top.display('', '<?php echo($message); ?>', '2', '1');
top.display('', '<?php echo($close); ?>', '2', '1');

<?php
        if ($javascript == false) {
?>
//-->
</script>
<?php
        }
}

$typingresult = 0;
// Check the typing status of the current operator
$query = "SELECT `typing` FROM " . $table_prefix . "sessions WHERE `id` = '$guest_login_id'";
$row = $SQL->selectquery($query);
//if (is_array($row)) {
        $typing = $row['typing'];

        switch($typing) {
        case 0: // None
                $typingresult = 0;
                break;
        case 1: // Guest Only
                $typingresult = 0;
                break;
        case 2: // Operator Only
                $typingresult = 1;
                break;
        case 3: // Both
                $typingresult = 1;
                break;
        }
//}


if (isset($messages)) {
        if (is_array($messages)) {
                foreach ($messages as $key => $row) {
                        if (is_array($row)) {
                                $username = $row['username'];
                                $message = $row['message'];
                                $align = $row['align'];
                                $status = $row['status'];

                        //error_log("Refresher:status:".$status."\n", 3, "error.log");
                        //error_log("Refresher:javascript:".$javascript."\n", 3, "error.log";

                            if((isset($status)) && ($status != '6') ){
                              $message = str_replace('<', '&lt;', $message);
                              $message = str_replace('>', '&gt;', $message);
                              $message = preg_replace("/(\r\n|\r|\n)/", '<br />', $message);
                            }else if((isset($status)) && ($status == '6') ){
                              $message = preg_replace("/(\r\n|\r|\n)/", ' ', $message);
                            }
                            $message = addslashes($message);
                                //error_log("Refresher:Normaliza:".$message."\n", 3, "error.log");
                                // Search and replace smilies with images if smilies are on
//                              if ($guest_smilies == true) {
//                                      $message = htmlSmilies($message, './pictures/');
//                              }

                                // Output message
                                if ($status >= 0) {
                                        if ($javascript == false) {
?>
<script language="JavaScript">
<!--
<?php
                                        }
?>
//ADVERTENCIA
top.display('<?php echo($username); ?>', '<?php echo($message); ?>', '<?php echo($align); ?>', '<?php echo($status); ?>');

<?php
                                        if ($javascript == false) {
?>
//-->
</script>
<?php
                                        }
                                }
                        }
                }
?>
top.initalisedChat = 1;
<?php
        }
}

// Update last refresh so user is online
$query = "UPDATE " . $table_prefix . "sessions SET `refresh` = NOW() WHERE `id` = '$guest_login_id'";
$SQL->miscquery($query);

if ($javascript == false) {
?>
<script language="JavaScript" type="text/JavaScript">
<!--

<?php
}

if (!$typingresult || !($active > 0)) {
?>
//alert("<?php echo $typing."=".$typingresult."=".$active; ?>")
top.setWaiting();
<?php
}
else {
?>
//alert("<?php echo $typing."=".$typingresult."=".$active; ?>")
top.setTyping();
<?php
}
if ($active == 0 && $chatting == 0) {
        if ($waiting > $guest_login_timeout) {
?>
if (top.displayFrame.displayContentsFrame) {
        top.displayFrame.displayContentsFrame.location.href = '<?php echo $install_directory; ?>/waiting.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?><?php echo('&DOMAINID='.$domain_id);?>';
}
<?php
        }
}
if ($active == -1 || $active == -3) {
?>
top.chatEnded = true;
<?php
}
else if ($javascript == false) {
?>
//top.refreshDisplayer();
//-->
</script>
</body>
</html>
<?php
}
?>
