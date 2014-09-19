<?php
include_once('import/constants.php');

include('import/block_spiders.php');
include('import/config_database.php');
include('import/class.mysql.php');
include('import/config.php');


$_REQUEST['USER'] = !isset( $_REQUEST['USER'] ) ? '' : htmlspecialchars( (string) $_REQUEST['USER'], ENT_QUOTES );
$_REQUEST['EMAIL'] = !isset( $_REQUEST['EMAIL'] ) ? '' : htmlspecialchars( (string) $_REQUEST['EMAIL'], ENT_QUOTES );
$_REQUEST['PHONE'] = !isset( $_REQUEST['EMAIL'] ) ? '' : htmlspecialchars( (string) $_REQUEST['PHONE'], ENT_QUOTES );
$_REQUEST['COMPANY'] = !isset( $_REQUEST['EMAIL'] ) ? '' : htmlspecialchars( (string) $_REQUEST['COMPANY'], ENT_QUOTES );
if (!isset($_REQUEST['DEPARTMENT'])){ $_REQUEST['DEPARTMENT'] = ''; }
$_REQUEST['DEPARTMENT'] = !isset( $_REQUEST['DEPARTMENT'] ) ? '' : htmlspecialchars( (string) $_REQUEST['DEPARTMENT'], ENT_QUOTES );

$_REQUEST['SERVER'] = !isset( $_REQUEST['SERVER'] ) ? '' : htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES );
$_REQUEST['URL'] = !isset( $_REQUEST['URL'] ) ? '' : (string) $_REQUEST['URL'];
$_REQUEST['COOKIE'] = !isset( $_REQUEST['COOKIE'] ) ? '' : htmlspecialchars( (string) $_REQUEST['COOKIE'], ENT_QUOTES );

$username   = $_REQUEST['USER'];
$email      = $_REQUEST['EMAIL'];
$phone      = $_REQUEST['PHONE'];
$company    = $_REQUEST['COMPANY'];
$agent_id   = $_REQUEST['AGENTID'];

// Chnage
//$department = $_REQUEST['DEPARTMENT'];

$department =  htmlspecialchars_decode ($_REQUEST['DEPARTMENT'] , ENT_QUOTES );

$referer    = mysql_real_escape_string(urldecode(trim($_REQUEST['URL'])));
$ip_address = $_SERVER['REMOTE_ADDR'];
$domain_id  = !isset( $domain_id ) ? 0 : (int) $domain_id;

//error_log("FRAMES ------------>>  username:".$username."\n", 3, "error.log");
//error_log("FRAMES ------------>>  email:".$email."\n", 3, "error.log");

$URL = $_REQUEST['URL'];

if ($_REQUEST['COOKIE'] != '') {
        $cookie_domain = $_REQUEST['COOKIE'];
}

if ($require_guest_details == true && $disable_login_details == false) {

        if ($username == '' || $email == '') {
                if ($department == '') {
                        header('Location: index.php?ERROR=empty'.'&DOMAINID='.$domain_id);
                        exit();
                }
                else {
                        header('Location: index.php?ERROR=empty&DEPARTMENT=' . $department.'&DOMAINID='.$domain_id);
                        exit();
                }
        }
        else {
                if (!ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.
                        '@'.
                        '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
                        '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email)) {
                        if ($department == '') {
                                header('Location: index.php?ERROR=email'.'&DOMAINID='.$domain_id);
                                exit();
                        }
                        else {
                                header('Location: index.php?ERROR=email&DEPARTMENT=' . $department.'&DOMAINID='.$domain_id);
                                exit();
                        }
                }
        }
}

if ($department == '') { $department  = mysql_real_escape_string($_REQUEST['DEPARTMENT']);}

// Query to see if panel/Operators are Online
$query = "SELECT UNIX_TIMESTAMP(`datetime`) AS `datetime` FROM " . $table_prefix . "users WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' AND `status` = '1'";
if ($departments == true && $department != '') { $query .= " AND department LIKE '%$department%'"; }
$row = $SQL->selectquery($query);
if(is_array($row)) {
        $datetime = $row['datetime'];
}
else {
     
        header('Location: offline.php?SERVER=' . $_REQUEST['SERVER'] . 'LANGUAGE=' . LANGUAGE_TYPE.'&DOMAINID='.$domain_id);
        exit();
}

// Get the applicable hostname to show where the site visitor is located
$current_host = $_REQUEST['URL'];
for ($i = 0; $i < 3; $i++) {
        $substr_pos = strpos($current_host, '/');
        if ($substr_pos === false) {
                break;
        }
        if ($i < 2) {
                $current_host = substr($current_host, $substr_pos + 1);
        }
        else {
                $current_host = substr($current_host, 0, $substr_pos);
        }

}

if (substr($current_host, 0, 4) == 'www.') { $current_host = substr($current_host, 4); }

if ($username == '') { $username = 'guest'; }

// If the site visitor has now previous username or has chatted previously then get new username
if ($guest_username != '' || $guest_login_id == 0) {
        $query = "SELECT count(`id`) FROM " . $table_prefix . "sessions WHERE `username` LIKE '$username%' AND (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < $datetime";
        $row = $SQL->selectquery($query);
        if(is_array($row)) {
                if ($row['count(`id`)'] > 0) {
                        $query = "SELECT `username` FROM " . $table_prefix . "sessions WHERE `username` LIKE '$username%' AND UNIX_TIMESTAMP(datetime) > $datetime ORDER BY `id` DESC LIMIT 1";
                        $row = $SQL->selectquery($query);
                        if (is_array($row)) {
                                $total = substr($row['username'], strlen($username));
                                $id = (int)$total + 1;
                                $username = $username . $id;
                        }
                }
        }
}
$guest_login_id = 0;
// If the site visitor has chatted previously then start new session
if ($guest_login_id == 0) {
        // Add session details
        $query = "INSERT INTO " . $table_prefix . "sessions (`request`, `username`, `datetime`, `email`,  `phone` , `company`, `server`, `department`, `refresh`, `language`, rating, id_domain , id_agent) VALUES ('$request_id', '$username', NOW(), '$email', '$phone', '$company', '$current_host', '$department', NOW(), '".LANGUAGE_TYPE."', -1, $domain_id , $agent_id)";
        $login_id = $SQL->insertquery($query);                                                                 
  }
else {
        $login_id = $guest_login_id;
        $username = $guest_username;

        // Retrieve the current connected server
        $query = "SELECT `server` FROM " . $table_prefix . "sessions WHERE `id` = '$login_id'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $server = $row['server'];

                // Update session details
                $query = "UPDATE " . $table_prefix . "sessions SET `request` = '$request_id', `username` = '$username', `datetime` = NOW(), `email` = '$email', `server` = '$current_host', `department` = '$department', `refresh` = NOW(), `active` = '0' WHERE `id` = '$login_id'";
                $SQL->miscquery($query);
        }
        else {
                // Add session details
                $query = "INSERT INTO " . $table_prefix . "sessions (`request`, `username`, `datetime`, `email`,  `phone` , `company`, `server`, `department`, `refresh`, `language`, rating, id_domain , id_agent) VALUES ('$request_id', '$username', NOW(), '$email', '$phone', '$company', '$current_host', '$department', NOW(), '".LANGUAGE_TYPE."', -1, $domain_id, $agent_id)";
               // echo("query2: " . $query);

        //exit('Termina... 2');
                $login_id = $SQL->insertquery($query);
        }
}

// Remove the Initate chat flag and flag as chatting from the requests as the chat has started.
$query = "UPDATE " . $table_prefix . "requests SET `initiate` = '-4' WHERE `id` = '$request_id'";
$SQL->miscquery($query);

// Retrieve the current connected server
if ($server != '') {
        $query = "SELECT `server` FROM " . $table_prefix . "sessions WHERE `id` = '$login_id'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $server = $row['server'];
        }
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
//   error_log("frames:session:".$key.":".$value."\n", 3, "error.log");
}
unset($session1);

$session['REQUEST'] = $request_id;
$session['GUEST_LOGIN_ID'] = $login_id;
$session['GUEST_USERNAME'] = $username;
$session['MESSAGE'] = 0;
$session['CHATTING'] = 0;
$session['OPERATOR'] = '';
$session['TOTALOPERATORS'] = 0;
$session['SECURITY'] = $security;
$session['LANGUAGE'] = LANGUAGE_TYPE;
$session['CHARSET'] = CHARSET;

if (isset($domain_id)){
  $session['DOMAINID'] = $domain_id;
}else{
  $session['DOMAINID'] = $id_domain;
}

$data = serialize($session);

setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);

header('Content-type: text/html; charset=' . CHARSET);

include('import/settings_default.php');

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';
if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}
/*
if(!stripos("-".$campaign_image, "http") == 1){
        $campaign_image = "./".$campaign_image;
}*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo($livehelp_name); ?></title>
<script language="JavaScript" type="text/JavaScript" src="frames.js.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?>&URL=<?php echo urlencode( $URL ); ?>&DOMAINID=<?php echo($domain_id); ?>">
</script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="JavaScript" type="text/JavaScript">
  jQuery.noConflict();
  jQuery(document).ready(function(){
      w = jQuery('.background').width() + 45;//550;
      h = jQuery('.background').height() + 90;//515;
      wleft = (screen.width - w) / 2;
      wtop = (screen.height - h) / 2;
      if (wleft < 0) {
        w = screen.width;
        wleft = 0;
      }
      if (wtop < 0) {
        h = screen.height;
        wtop = 0;
      }
      window.resizeTo(w, h);
      window.moveTo(wleft, wtop);
  })
  
</script>

<style type="text/css">
<!--
body { background-color: #f2f2f2;}
div.background {
        
        margin: 0 auto;
        background:#e1e1e1 url(./pictures/skins/<?php echo($chat_background_img); ?>/bg.png) repeat-x;
        width:20px auto 0;
        position: relative;
        width:480px;
        height:380px;
        border:1px solid #d4d4d4;
        border-radius:5px
}

textarea { background-color: #f8f8f8; border:1px solid #d4d4d4; border-radius:5px}


-->
</style>
<link href="style/styles.php?URL=<?php echo urlencode( $URL ); ?><?php echo('&DOMAINID='.$domain_id); ?>" rel="stylesheet" type="text/css">
</head>
<body text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>" onLoad="preloadImages('<?php echo $install_directory; ?>./domains/<?php echo($domain_id); ?>/i18n/<?php echo(LANGUAGE_TYPE); ?>/pictures/send_hover.gif'); LoadMessages();" onUnload="windowLogout();" onBeforeUnload="windowLogout();" oncontextmenu="return true;"  bottommargin="0">
<div class="background">
<div style="POSITION: absolute; LEFT: 18px; TOP: 19px;">
    <table width="350" border="0" cellpadding="0" cellspacing="0" style="background-color: #f8f8f8; border:1px solid #d4d4d4; border-radius:0">
        <tr>      
            <td width="350" height="225">
                <iframe name="displayFrame" id="displayFrame" src="displayer.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?>&URL=<?php echo urlencode( $referer ); ?><?php echo('&DOMAINID='.$domain_id); ?>" frameborder="0" width="100%" height="100%" style="border-style:none">
                    <script language="JavaScript" type="text/JavaScript">top.location.href = 'offline.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?><?php echo('&DOMAINID='.$domain_id); ?>';</script>
                </iframe>
            </td>    
        </tr>
    </table>
</div>
<div style="POSITION: absolute; LEFT: 375px; TOP: 30px;">
    <p style="margin: 0 0 20px;"><a href="logout.php?client_domain_id=<?php echo($domain_id); ?>&URL=<?php echo($_REQUEST['URL']); ?>&LANGUAGE=<?php echo LANGUAGE_TYPE; ?><?php echo('&DOMAINID='.$domain_id); ?>" onClick="manualLogout();" target="_top" class="normlink" style="font-weight:700; text-decoration: underline;"><?php echo($logout_label); ?></a></p>
      
</div>

<?php 

       $agent_bannner =0;
       $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'disable_agent_bannner' and id_domain = $domain_id";
       $row = $SQL->selectquery($query);
       if (is_array($row)) {
         $agent_bannner = $row['value'];
         }
         
if ($agent_bannner == 0) {
 
?>
<div style="POSITION: absolute; LEFT: 373px; TOP: 50px;">
        <a href="<?php echo($campaign_link); ?>" target="_blank"><img id="ImageID" src="./domains/<?php echo($domain_id);?>/i18n/<?php echo LANGUAGE_TYPE; ?>/pictures/<?php echo($campaign_image); ?>" border="0"></a>
</div>

<?php
  }
?>

<!--div id="Layer2" style="position:absolute; left:302px; top:187px; width:150px; height:45px; z-index:2; visibility: hidden;">
        <div align="center">
                <a href="#" onClick="appendText(':-)');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_01.gif" name="22" width="20" height="20" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(';-P');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_04.gif" width="21" height="20" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(':)');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_08.gif" width="20" height="20" border="0" style="filter:alpha(opacity=75);-moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText('$-D');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_03.gif" width="20" height="21" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText('8-)');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_07.gif" width="21" height="20" border="0" style="filter:alpha(opacity=75);-moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(':-/');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_05.gif" width="20" height="20" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(':-O');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_12.gif" width="20" height="20" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(':(');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_06.gif" width="20" height="21" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(':-(');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_02.gif" width="20" height="20" border="0"  style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(':-|');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_09.gif" width="20" height="20" border="0" style="filter:alpha(opacity=75);-moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText(':--');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_10.gif" width="20" height="20" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
                <a href="#" onClick="appendText('/-|');toggle('Layer1');toggle('Layer2');return false;"><img src="pictures/smilie_11.gif" width="21" height="20" border="0" style="filter:alpha(opacity=75);moz-opacity:0.75" onMouseOver="high(this)" onMouseOut="low(this)"></a>
        </div>
</div-->

<iframe name="sendMessageFrame" id="sendMessageFrame" src="./blank.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?>&URL=<?php echo urlencode( $referer ); ?><?php echo('&DOMAINID='.$domain_id); ?>" frameborder="0" border="0" width="0" height="0" style="visibility: hidden"></iframe>
<form action="send.php<?php echo('?DOMAINID='.$domain_id); ?>" method="POST" name="message_form" target="sendMessageFrame" style="margin: 0px; position: relative; top: -20px;">
         <div style="POSITION: absolute; LEFT: 20px; TOP: 270px;">
                <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                <td valign="middle">
                <div align="right">
                <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                 <td style="color:#eb9339">
                 <p> 
                   <span style="background-repeat:no-repeat; padding-left:4px; float:left"><?php echo $typing_status_label; ?>:</span>
                   <strong id="messengerStatus" style="width: 125; height: 20;" name="messengerStatus"><?php echo $waiting_gif; ?></strong>
                 </p>
                </td>
                </tr>
                </table>
                </div>
                </td>
                </tr>
                </table>
        </div>
         <div style="POSITION: absolute; LEFT: 20px; TOP: 292px;">
            <textarea name="MESSAGE" cols="55" rows="3" onKeyPress="return checkEnter(event);" onBlur="typing(false)" style="width:350px; height:78px; font-family:<?php echo($chat_font_type); ?>; font-size:<?php echo($guest_chat_font_size); ?>; padding:8px" disabled="true"></textarea>
        </div>
        <div style="POSITION: absolute; LEFT: 375px; TOP: 295px;">
            <a href="#" onMouseOut="swapImgRestore()" onMouseOver="swapImage('Send','','./domains/<?php echo($domain_id); ?>/i18n/<?php echo(LANGUAGE_TYPE); ?>/pictures/<?php echo($chat_button_hover_img); ?>',1)" onClick="return processForm();"><img src="./domains/<?php echo($domain_id); ?>/i18n/<?php echo(LANGUAGE_TYPE); ?>/pictures/<?php echo($chat_button_img); ?>" alt="<?php echo($send_msg_label); ?>" name="Send" border="0"></a>
        </div>
      <?php
       
       $copyright = 1;
       $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'disable_copyright' and id_domain = $domain_id";
       $row = $SQL->selectquery($query);
       if (is_array($row)) {
         $copyright = $row['value'];
         }
 
       if ($copyright == 1) {
        
         $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'company_link' and id_domain = $domain_id";
         $row = $SQL->selectquery($query);
         if (is_array($row)) {
         $company_link = $row['value'];
          } 

         $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'company_slogan' and id_domain = $domain_id";
         $row = $SQL->selectquery($query);
         if (is_array($row)) {
         $company_slogan = $row['value'];
          }         
 
        ?>        

      <div style="POSITION: absolute; LEFT: 25px; TOP: 380px;">
      <span class="small"><a href=" <?php echo($company_link); ?> " target="_blank" class="normlink"><?php echo($company_slogan); ?></span>
      </div>

      <?php
       }
        ?>

      <input type="Hidden" name="URL" value="<?php echo $_REQUEST['URL']; ?>">

</form>
<script language="JavaScript" type="text/JavaScript" src="frames2.js.php?username=<?php echo $username; ?>&URL=<?php echo urlencode( $referer ); ?><?php echo('&DOMAINID='.$domain_id); ?>">
</script>
<span id="messSoundSpan"></span>

<?php
  
  $analytics='';
  
   $query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='analytics_account'";

    $row = $SQL->selectquery($query);
    if (is_array($row)) {
        $analytics = $row['value'];
        }  

?>

<?php if ($analytics !='') {  ?>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo($analytics); ?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<?php }  ?>
</div>
</body>
</html>
