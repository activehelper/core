<?php
include('import/constants.php');

if (isset($_REQUEST['DOMAINID'])){ $domain_id = (int) $_REQUEST['DOMAINID']; }

header('Content-type: text/html; charset=utf-8');

if (isset($_SERVER['PATH_TRANSLATED']) && $_SERVER['PATH_TRANSLATED'] != '') { $env_path = $_SERVER['PATH_TRANSLATED']; } else { $env_path = $_SERVER['SCRIPT_FILENAME']; }
$full_path = str_replace("\\\\", "\\", $env_path);
$livehelp_path = $_SERVER['PHP_SELF'];
if (strpos($full_path, '/') === false) { $livehelp_path = str_replace("/", "\\", $livehelp_path); }
$pos = strpos($full_path, $livehelp_path);
if ($pos === false) {
        $install_path = $full_path;
}
else {
        $install_path = substr($full_path, 0, $pos);
}

$installed = false;
$database = include('import/config_database.php');
if ($database) {
        include('import/block_spiders.php');
        include('import/class.mysql.php');
        include('import/config.php');
} else {
        $installed = false;
}

if ($installed == false) {
        include('import/settings_default.php');
}

$_REQUEST['SERVER'] = !isset( $_REQUEST['SERVER'] ) ? '' : htmlspecialchars( (string) $_REQUEST['SERVER'],ENT_QUOTES );
$_REQUEST['URL'] = !isset( $_REQUEST['URL'] ) ? '' : (string) $_REQUEST['URL'];
$_REQUEST['TITLE'] = !isset( $_REQUEST['TITLE'] ) ? '' : htmlspecialchars( (string) $_REQUEST['TITLE'], ENT_QUOTES );
$_REQUEST['COMPLETE'] = !isset( $_REQUEST['COMPLETE'] ) ? '' : (string) $_REQUEST['COMPLETE'];
$_REQUEST['SECURITY'] = !isset( $_REQUEST['SECURITY'] ) ? '' : (string) $_REQUEST['SECURITY'];
$_REQUEST['BCC'] = !isset( $_REQUEST['BCC'] ) ? '' : (string) $_REQUEST['BCC'];


$error = '';
$invalid_email = '';
$invalid_security = '';
$captcha =1;
$email = '';
$name = '';
$message = '';
$phone = '';
$company = '';
$code = '';
$status = '';
$setting = $row['name'];
$form_high = 430;


        // Settings 
          $query = "SELECT name, value FROM " . $table_prefix . "settings WHERE name in ('captcha','phone','company') and id_domain = $domain_id";
                   $rows = $SQL->selectall($query);
                            if(is_array($rows)) {
                             foreach ($rows as $key => $row) {                                                                                                       
                              if (is_array($row)) {                                                               
                                 $setting = $row['name'];     
                                                                                             
                                if($setting == "captcha") {
                                     $captcha = $row['value'];                                    
                                      $form_high = $form_high + ( $captcha * 50);}                                                                                                                         
                                   elseif($setting == "phone") {
                                      $use_phone = $row['value'];
                                       $form_high = $form_high + ( $use_phone * 60);}
                                   else { 
                                       $use_company = $row['value'];
                                       $form_high = $form_high + ( $use_company * 60);}    
                                }
                              }
                             } 
                             
    
                                       
if($_REQUEST['COMPLETE'] == true) {
    

        $name    = stripslashes($_REQUEST['NAME']);
        $email   = stripslashes($_REQUEST['EMAIL']);
        $phone   = stripslashes($_REQUEST['PHONE']);
        $company = stripslashes($_REQUEST['COMPANY']);
        $message = stripslashes($_REQUEST['MESSAGE']);
        $code    = stripslashes($_REQUEST['SECURITY']);
        $bcc     = stripslashes($_REQUEST['BCC']);

        

        if ($email == '' || $name == '' || $message == '') {
                $error = true;
        }
        else {

                $url = stripslashes($_REQUEST['URL']);
                $title = stripslashes($_REQUEST['TITLE']);

                if (!ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.
                                          '@'.
                                          '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
                                          '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email)) {
                                          $invalid_email = true;
                }
                else {

                        $md5code = md5(strtoupper($code));
                        if ($security != $md5code && ((function_exists('imagepng') || function_exists('imagejpeg')) && function_exists('imagettftext')) && ($captcha ==1) ) {
                                $invalid_security = true;

                                // Generate a NEW random string
                                $chars = array('a','A','b','B','c','C','d','D','e','E','f','F','g','G','h','H','i','I','j','J','k','K','l','L','m','M','n','N','o','O','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w','W','x','X','y','Y','z','Z','1','2','3','4','5','6','7','8','9');
                                $security = '';
                                for ($i = 0; $i < 5; $i++) {
                                   $security .= $chars[rand(0, count($chars)-1)];
                                }

                                $session = array();
                                $session['REQUEST'] = $request_id;
                                $session['DOMAINID'] = $domain_id;
                                $session['SECURITY'] = md5(strtoupper($security));
                                $session['LANGUAGE'] = LANGUAGE_TYPE;
                                $session['CHARSET'] = CHARSET;
                                $data = serialize($session);

                                setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);

                        }
                        else {
                                $current_page = 'Unavailable';
                                $title = 'Unavailable';
                                $referrer = 'Unavailable';

                                $query = "SELECT `url`, `title`, `referrer`, `id_domain` FROM " . $table_prefix . "requests WHERE `id` = '$request_id'";
                                $row = $SQL->selectquery($query);
                                if (is_array($row)) {
                                        $current_page = $row['url'];
                                        $title = $row['title'];
                                        $referrer = $row['referrer'];
                                        if ($current_page == '') { $current_page = 'Unavailable'; }
                                        if ($title == '') { $title = 'Unavailable'; }
                                        if ($referrer == '') { $referrer = 'Unavailable'; } elseif ($referrer == 'false') { $referrer = 'Direct Link / Bookmark'; }
                                }


                                if ($configure_smtp == true) {
                                        ini_set('SMTP', $smtp_server);
                                        ini_set('smtp_port', $smtp_port);
                                        ini_set('sendmail_from', $from_email);
                                }

                             
  
                                $from_name = "$name";
                                $from_email = "$email";
                                $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'offline_email' And id_domain = $domain_id";
                                $row = $SQL->selectquery($query);
                                if (is_array($row)) {
                                        $offline_email = $row['value'];
                                }
                                $to_email = $offline_email;
                                $subject = "Livehelp Offline Message";
                                
                                $headers = "Mime-Version: 1.0\n";
                                $headers .= "Content-Type: text/plain;charset=UTF-8\n";
                                $headers = "From: " . $from_name . " <" . $from_email . ">\n";
                                $headers .= "Reply-To: " . $from_name . " <" . $from_email . ">\n";
                                $headers .= "Return-Path: " . $from_name . " <" . $from_email . ">\n";
                                
                                
                                $msg      = mysql_real_escape_string($message);
                                
                                $message .= "\n\n--------------------------\n";
                                $message .= "IP Logged:  " . $_SERVER['REMOTE_ADDR'] . "\n";
                                if ($ip2country_installed == true) { $message .= "Country:  $country\n"; }
                                $message .= "URL:  $current_page\n";
                                $message .= "URL Title:  $title\n";
                                $message .= "Referrer:  $referrer\n";

                                $sendmail_path = ini_get('sendmail_path');
                                if ($sendmail_path == '') {
                                        $headers = str_replace("\n", "\r\n", $headers);
                                        $message = str_replace("\n", "\r\n", $message);
                                }
                                //mail($to_email, $subject, $message, $headers);
                                mail($to_email, '=?utf-8?B?'.base64_encode($subject).'?=' , $message, $headers);
                                
                                // save the offline email in the database
                                
                                $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'log_offline_email' And id_domain = $domain_id";
                                $row = $SQL->selectquery($query);
                                if (is_array($row)) {
                                        $log_offline_email = $row['value'];
                                }
                                
                                if ($log_offline_email == 1) {
                                    
                                $name      = mysql_real_escape_string($name);
                                $email     = mysql_real_escape_string($email);
                                $phone     = mysql_real_escape_string($phone);
                                $company   = mysql_real_escape_string($company);
                                    
                                $query = "INSERT INTO " . $table_prefix . "offline_messages (`name`, `email`,  `phone`,  `company`, `message`, `id_domain` , `datetime`) VALUES ('$name', '$email', '$phone','$company','$msg', $domain_id, NOW())";
                                $SQL->insertquery($query);
                                 }
                                
                                // send email copy
                                
                                if ($bcc == true) {
                                        $to_email = "$email";
                                        $headers = "From: " . $from_name . " <" . $from_email . ">\n";
                                        $headers .= "Reply-To: " . $from_name . " <" . $from_email . ">\n";
                                        $headers .= "Return-Path: " . $from_name . " <" . $from_email . ">\n";
                                        $message = stripslashes($_REQUEST['MESSAGE']);

                                        if ($sendmail_path == '') { $headers = str_replace("\n", "\r\n", $headers); $message = str_replace("\n", "\r\n", $message); }
                                        mail($to_email, $subject, $message , $headers);
                                }
                        }
                }
        }

        $message = stripslashes($_REQUEST['MESSAGE']);

}

header('Content-type: text/html; charset=' . CHARSET);

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';

if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo($livehelp_name); ?></title>
<link href="<?php echo $install_directory; ?>/style/styles.php?<?php echo('DOMAINID='.$domain_id);?>" rel="stylesheet" type="text/css">
<script>
window.resizeTo(490, <?php echo($form_high); ?>);
</script>
<style type="text/css">
body { background-color: #f2f2f2;}

.background {margin: 5px auto 0; background:#e1e1e1 url(./pictures/skins/<?php echo($chat_background_img); ?>/bg.png) repeat-x; border:1px solid #d4d4d4; border-radius:5px; position: relative;width:410px;}
.offline_form td{ padding:3px 0}
.offline_form .security { border-top:1px dotted #ccc; padding:5px 0px 0; margin-top: 5px;}
.offline_form textarea { height:165px;}

.confirm_form textarea { height: 120px;}
.confirm_form div.left { width: 120px; float:left}
.confirm_form td { padding:5px 0}

.tbl_form .inputbox, .tbl_form textarea { background-color: #f8f8f8; border:1px solid #d4d4d4; border-radius:5px; padding-left: 5px;}
.tbl_form .inputbox { height: 25px; line-height:25px;}
.tbl_form textarea { padding-top: 15px;}

.label { text-transform: uppercase; color:#0095e1; width:145px; float:left; margin-bottom:0; font-size: 11px;}
.top{border-bottom:1px dotted #ccc; padding-bottom:25px;}

.bt_submit { background: url(./pictures/skins/<?php echo($chat_background_img); ?>/bg-submit.png) no-repeat 50% 50%; padding:4px 0 0; position: absolute; height: 30px; width:290px; bottom:-20px; left:45px}
.bt_submit .bt_send, .bt_submit .bt_cancel { background: #222 url(./pictures/skins/<?php echo($chat_background_img); ?>/overlayy.png) repeat-x; display: inline-block; padding: 5px 10px 6px; color: #fff; text-decoration: none; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius:5px; -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5); -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5); text-shadow: 0 -1px 1px rgba(0,0,0,0.25);  position: relative; font-family:Calibri, Arial, sans-serif;}
.bt_submit .bt_send { background-position: 0 0;}
.bt_submit .bt_cancel { background-position: 0 -26px;}

.confirm_form .bt_submit { background:#f2f2f2 url(./pictures/skins/<?php echo($chat_background_img); ?>/bg-close-form.png) no-repeat; width:135px; left:265px; padding:0}
.confirm_form .bt_submit td { padding:0}
.confirm_form .bt_submit input { margin:0 0 0 7px;}
.confirm_form em { line-height:25px}

</style>

</head>
<body bgcolor="<?php echo($background_color); ?>" text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>">
 <!--img src="./i18n/<?php echo LANGUAGE_TYPE; ?>/pictures/background_offline.gif" alt="<?php echo($offline_message_label); ?>" width="309" height="49" style="position: relative; right: -150px; top: 10px;"-->

<?php

if($_REQUEST['COMPLETE'] == '' || $error != '' || $invalid_email != '' || $invalid_security != '') {
?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="JavaScript" type="text/JavaScript">
  jQuery.noConflict();
  jQuery(document).ready(function(){
      w = jQuery('.offline_message_form').width() + 60;//483;
      h = jQuery('.offline_message_form').height() + 135;//620;
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
<!--div align="center"-->
<div class="offline_message_form background">
  <form action="offline.php" method="post" name="offline_message_form" id="offline_message_form">
    <div style="padding:15px">
    <table border="0" align="center" cellspacing="0" cellpadding="0" class="tbl_form offline_form">
     <tr>
     <td valign="top" class="top">
     <p class="title"><?php  echo($unfortunately_offline_label); ?> <?php echo($fill_details_below_label); ?> </p>        
     </td>
     </tr>
<?php
        if ($invalid_email != '' || $error == true) {
?>
      <tr>
         <td valign="top"><span><?php echo($invalid_email_error_label); ?></span></td>
      </tr>
<?php
        } else if ($invalid_security != '') {
?>
      <tr>        
        <td valign="top" ><span><?php echo($invalid_security_error_label); ?></span></td>
      </tr>
<?php
        }
?>
      <tr>
        <td align="left" >
            <p class="label"><span><?php echo($your_name_label); ?>:</span></p>
            <input name="NAME" type="text" id="NAME" class="inputbox" value="<?php echo(htmlspecialchars($name)); ?>" size="40" style="width:210px" />
        </td>
      </tr>
      <tr>
        <td align="left" >
            <p class="label"><span><?php echo($your_email_label); ?>:</span></p>
            <input name="EMAIL" type="text" id="EMAIL" class="inputbox" value="<?php echo(htmlspecialchars($email)); ?>" size="40" style="width:210px" />
        </td>
      </tr>
      
      <?php if  ($use_phone ==1) { ?>         
      <tr>
         <td align="left" >  
            <p class="label"><span><?php echo($your_phone_label); ?>:</span></p>   
             <input name="PHONE" type="text" id="PHONE" class="inputbox" value="<?php echo(htmlspecialchars($phone)); ?>" size="40" style="width:210px" />
         </td>
      </tr>
      
      <?php
        } 
      if  ($use_company ==1) {    
       ?>
      
      <tr>
        <td align="left" >
            <p class="label"><span><?php echo($your_company_label); ?>:</span></p>                        
            <input name="COMPANY" type="text" id="COMPANY" class="inputbox" value="<?php echo(htmlspecialchars($company)); ?>" size="40" style="width:210px" />
        </td>
      </tr>
     
       <?php }  ?>
        
      <tr>
        <td align="left" >
          <p class="label" style="width:100%"><span><?php echo($message_label); ?>:</span></p>
          <textarea name="MESSAGE" cols="30" rows="4" id="MESSAGE" style="width:360px; height: 72px; vertical-align: middle; font-family:<?php echo($chat_font_type); ?>; font-size:<?php echo($guest_chat_font_size); ?>;"><?php echo(htmlspecialchars($message)); ?></textarea>
        </td>
      </tr>
      
     
<?php
    if  ($captcha ==1) {
    
        if ((function_exists('imagepng') || function_exists('imagejpeg')) && function_exists('imagettftext')) {
//      if (true) {
?>
      <tr>
        <td align="left" valign="middle" class="security">
            <p><strong><?php echo($security_code_label); ?>:</strong></p>
            <span style="height: 25px; vertical-align: middle; float:left; margin-right:10px"><input name="SECURITY" type="text" id="SECURITY" value="" size="6" style="width:120px;"/></span>
            <img src="security.php?URL=<?php echo urlencode($_REQUEST['URL']); ?>" style="float:left"/>
        </td>
      </tr>
<?php
        } 
     }   
?>

      <tr>
            <td align="left" style="padding-top:0">
            <input name="BCC" type="checkbox" value="1"/>
            <?php echo($send_copy_label); ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" align="left" valign="top" style="padding:0">
            <input name="COMPLETE" type="hidden" id="COMPLETE" value="1" />
            <input name="SERVER" type="hidden" id="SERVER" value="<?php echo htmlspecialchars($_REQUEST['SERVER'], ENT_QUOTES ); ?>" />

            <table border="0" cellpadding="2" cellspacing="2" class="bt_submit">
              <tr>
                <td align="right">
                    <input type="Submit" name="Submit" value="<?php echo($send_msg_label); ?>" class="bt_send" />
                  <!--  <input type="Submit" name="Submit" value="" class="bt_send" />-->
                </td>
                <td>
                   <input type="Button" name="Close" onClick="self.close();" value="<?php echo($close_window_label); ?>" class="bt_cancel" />
                    <!-- <input type="Button" name="Close" onClick="self.close();" value="" class="bt_cancel" />-->
                </td>
              </tr> 
            </table>

</td>
      </tr>

<?php

 $copyright = 1;
 $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'disable_copyright' and id_domain = $domain_id";
 $row = $SQL->selectquery($query);
 if (is_array($row)) {
 $copyright = $row['value'];
 }
                                

 if ($copyright ==1) {

 
 $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'company_logo' and id_domain = $domain_id";
 $row = $SQL->selectquery($query);
 if (is_array($row)) {
 $logo = $row['value'];
 }
   
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
 
 $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'copyright_image' and id_domain = $domain_id";
 $row = $SQL->selectquery($query);
 if (is_array($row)) {
 $banner_enable = $row['value'];
 }
  
 $livehelp_logo_path = $install_directory . '/domains/' . $domain_id . '/i18n/en/pictures/';
            
    ?>
     <tr>
        <td colspan="2" align="left" valign="top">
        <?php
          if ($banner_enable ==1) { 
          ?>  
         <a href=" <?php echo($company_link); ?> " target="_blank"><img src="<?php echo($livehelp_logo_path . $logo); ?> " border="0" ></a>
         <?php
           } else {
          ?>      
          <span class="small"><a href=" <?php echo($company_link); ?> " target="_blank" class="normlink"><?php echo($company_slogan); ?></span>  
           <?php
           } 
          ?>    
        </td>
      </tr>
<?php
 } 
  ?>    
      
  </table>

	 <input name="URL" type="hidden" id="URL" value="<?php echo htmlspecialchars($_REQUEST['URL'], ENT_QUOTES); ?>">
  </form>
<!--/div-->
<?php
} else {
?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="JavaScript" type="text/JavaScript">
  jQuery.noConflict();
  jQuery(document).ready(function(){
      w = jQuery('.confirm_form').width() + 40;//483;
      h = jQuery('.confirm_form').height() + 110;//620;
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

<div class="confirm_form background">
<div style="padding:20px">
    <div class="top">
        <p class="title"><strong><?php echo($thank_you_enquiry_label); ?></strong></p>
        <p><?php echo($contacted_soon_label); ?></p>
    </div>
    <table border="0" align="center" cellspacing="0" class="tbl_form">
    <?php
    if ($status != '') {
    ?>
    <?php
    }
    ?>
        <tr>
            <td valign="bottom">
                <p class="label"><span><?php echo($your_email_label); ?>:</span></p>
                <em><?php echo(htmlspecialchars($email)); ?></em>
            </td>
        </tr>
        <tr>
            <td valign="bottom">
                <p class="label"><span><?php echo($your_name_label); ?>:</span></p>
                <em><?php echo(htmlspecialchars($name)); ?></em>
            </td>
        </tr>
        <tr>
            <td valign="top"><p class="label" style="width: 100%;"><span><?php echo($message_label); ?>:</span></p></td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <div align="center">
                <textarea name="MESSAGE" cols="20" rows="6" id="MESSAGE" style="width:340px; font-family:<?php echo($chat_font_type); ?>; font-size:<?php echo($guest_chat_font_size); ?>;"><?php echo(htmlspecialchars($message)); ?></textarea>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="right" valign="top">
            <div align="right">
            <table border="0" cellpadding="2" cellspacing="2" class="bt_submit">
                <tr>
                    <td>
                       <input type="Button" name="Close" onClick="window.close();" value="<?php echo($close_window_label); ?>" class="bt_cancel"/>
                        <!-- <input type="Button" name="Close" onClick="window.close();" value="" class="bt_cancel"/>-->
                    </td>
                </tr>
            </table>
            </div>
            </td>      
        </tr>
    </table>
</div>
</div>
<?php
}
?>
</body>
</html>

