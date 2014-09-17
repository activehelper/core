<?php
include_once('import/constants.php');

include('./import/config_database.php');
include('./import/class.mysql.php');
include('./import/config.php');


ignore_user_abort(true);

if (isset($_REQUEST['DOMAINID'])){
  $domainId = (int) $_REQUEST['DOMAINID'];
}

$_REQUEST['RATING'] = !isset( $_REQUEST['RATING'] ) ? '' : (string) $_REQUEST['RATING'];
$_REQUEST['COMPLETE'] = !isset( $_REQUEST['COMPLETE'] ) ? '' : (string) $_REQUEST['COMPLETE'];
$_REQUEST['SEND_SESSION'] = !isset( $_REQUEST['SEND_SESSION'] ) ? '' : (string) $_REQUEST['SEND_SESSION'];
$_REQUEST['EMAIL'] = !isset( $_REQUEST['EMAIL'] ) ? '' : htmlspecialchars( (string) $_REQUEST['EMAIL'], ENT_QUOTES );

//Declaration variables
$complete = $_REQUEST['COMPLETE'];
$rating = $_REQUEST['RATING'];
$send_session = stripslashes($_REQUEST['SEND_SESSION']);
$email = $_REQUEST['EMAIL'];


// visitor default email

  $query = "SELECT `email` FROM " . $table_prefix . "sessions  WHERE `id` = '$guest_login_id'";
  $row = $SQL->selectquery($query);
  if (is_array($row)) {
     $visitor_email = $row['email'];
     }


  $query = "UPDATE " . $table_prefix . "sessions SET active = -1 WHERE `id` = '$guest_login_id'";
  $SQL->miscquery($query);

if ($rating != '') {

        $query = "UPDATE " . $table_prefix . "sessions SET `rating` = '$rating', active = -1 WHERE `id` = '$guest_login_id'";
        $SQL->miscquery($query);

        // chat session
  if ($send_session == true) {       
        $query = "Select `username` , `message` , TIME_FORMAT(datetime,'%l:%i:%s') `time` " .  ' from ' . $table_prefix . 'messages' . " where session =  '$guest_login_id'". ' order by id ';            
        $rows = $SQL->selectall($query);
                               
         
    if (is_array($rows)) {
        foreach ($rows as $key => $row) {
                if (is_array($row)) {

                  $msg .= '(' .$row['time']. ')' . ' ' . $row['username'] . ' : ' . $row['message']  ."\n";                                                                    
                
                   } 
                  } 
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
                
                     
        
        
        
        //$subject = str_ireplace("www.", "", $from_name) .' Chat Transcript (' . $guest_login_id . ' )';                        
        //mail($email, $subject, $msg, $headers);
        
     }   
        
       header('Location: ./logout.php?COMPLETE=1&LANGUAGE='.$_REQUEST['LANGUAGE'].'&DOMAINID='.$domainId.'&URL='. urlencode( $_REQUEST['URL'] ) );

}

else {
        $query = "SELECT `request`, `active` FROM " . $table_prefix . "sessions WHERE `id` = '$guest_login_id'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $operator_login_id = $row['active'];

                $request_id = $row['request'];

                $query = "UPDATE " . $table_prefix . "requests SET active = '-1' WHERE `id` = '$request_id'";
                $SQL->miscquery($query);

                if ($operator_login_id != '-1' || $operator_login_id != '-3') {
                        $query = "UPDATE " . $table_prefix . "sessions SET `active` = '-1' WHERE `id` = '$guest_login_id'";
                        $SQL->miscquery($query);
                        $query = "UPDATE " . $table_prefix . "requests SET `initiate` = '0' WHERE `id` = '$request_id'";
                        $SQL->miscquery($query);
                }
        }
}

include('import/settings_default.php');

header('Content-type: text/html; charset=' . CHARSET);

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';
if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}

// Send transcription

if ($send_session == true) {  
 
 $headers = "Mime-Version: 1.0\n";
 $headers .= "Content-Type: text/plain;charset=UTF-8\n";   
 $headers .= "From: " . str_ireplace("www.", "", $from_name). " <" . $from_email . ">\n";   
 $subject = str_ireplace("www.", "", $from_name). " " . $chat_transcript_label . ' (' . $guest_login_id . ' )'; 
 mail($email, '=?utf-8?B?'.base64_encode($subject).'?=' , $msg, $headers);
 
 }
 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo($livehelp_name); ?></title>
<link href="<?=$install_directory?>/style/styles.php?<?echo('DOMAINID='.$domainId);?>" rel="stylesheet" type="text/css">

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="JavaScript" type="text/JavaScript">
  jQuery.noConflict();
  jQuery(document).ready(function(){
      w = jQuery('.frm_logout').width() + 40;//500;
      h = jQuery('.frm_logout').height() + 100;//395;
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
.background { background-color:#f2f2f2; font: 12px/16px arial;}
-->
.frm_logout { background:#e1e1e1 url(./pictures/skins/<?php echo($chat_background_img); ?>/bg.png) repeat-x 0 50%; width:460px; margin:0 auto; border:1px solid #d4d4d4; border-radius:5px}
.frm_logout .top { border-bottom: 1px dotted #ccc; padding-bottom: 20px;}
.frm_logout .label { color:#0095e1; text-transform:uppercase; font-size: 11px; float:left; width:185px; float:left; line-height: 25px;}
.frm_logout td { padding:5px 0}

.bt_rate { background: #222 url(./pictures/skins/<?php echo($chat_background_img); ?>/overlayy.png) repeat-x; display: inline-block; padding: 2px 10px 3px; color: #fff; text-decoration: none; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius:5px; -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5); -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5); text-shadow: 0 -1px 1px rgba(0,0,0,0.25);  position: relative; font-family:Calibri, Arial, sans-serif;}

.frm_logout .inputbox { background-color: #f8f8f8; border:1px solid #d4d4d4; height: 26px; line-height:26px; width:232px; padding-left:5px; float:right; border-radius:5px}
.frm_logout .title {  color:#0095e1; font-size: 12px; margin: 15px;}

select.f_styled { height:27px}
span.select { background: url(./pictures/skins/<?php echo($chat_background_img); ?>/select2.png) no-repeat; padding: 0 0 0 5px; width:142px; position: absolute; height:27px; line-height:27px; overflow: hidden; float:left}
</style>
<script language="JavaScript" type="text/JavaScript">

var selectWidth = "142";

/* No need to change anything after this */


document.write('<style type="text/css">input.f_styled { display: none; } select.f_styled { position: relative; width: ' + selectWidth + 'px; opacity: 0; filter: alpha(opacity=0); z-index: 5; } .disabled { opacity: 0.5; filter: alpha(opacity=50); }</style>');

var Custom = {
	init: function() {
		var inputs = document.getElementsByTagName("input"), span = Array(), textnode, option, active;
		for(a = 0; a < inputs.length; a++) {
			if((inputs[a].type == "checkbox" || inputs[a].type == "radio") && inputs[a].className == "f_styled") {
				span[a] = document.createElement("span");
				span[a].className = inputs[a].type;

				if(inputs[a].checked == true) {
					if(inputs[a].type == "checkbox") {
						position = "0 -" + (checkboxHeight*2) + "px";
						span[a].style.backgroundPosition = position;
					} else {
						position = "0 -" + (radioHeight*2) + "px";
						span[a].style.backgroundPosition = position;
					}
				}
				inputs[a].parentNode.insertBefore(span[a], inputs[a]);
				inputs[a].onchange = Custom.clear;
				if(!inputs[a].getAttribute("disabled")) {
					span[a].onmousedown = Custom.pushed;
					span[a].onmouseup = Custom.check;
				} else {
					span[a].className = span[a].className += " disabled";
				}
			}
		}
		inputs = document.getElementsByTagName("select");
		for(a = 0; a < inputs.length; a++) {
			if(inputs[a].className == "f_styled") {
				option = inputs[a].getElementsByTagName("option");
				active = option[0].childNodes[0].nodeValue;
				textnode = document.createTextNode(active);
				for(b = 0; b < option.length; b++) {
					if(option[b].selected == true) {
						textnode = document.createTextNode(option[b].childNodes[0].nodeValue);
					}
				}
				span[a] = document.createElement("span");
				span[a].className = "select";
				span[a].id = "select" + inputs[a].name;
				span[a].appendChild(textnode);
				inputs[a].parentNode.insertBefore(span[a], inputs[a]);
				if(!inputs[a].getAttribute("disabled")) {
					inputs[a].onchange = Custom.choose;
				} else {
					inputs[a].previousSibling.className = inputs[a].previousSibling.className += " disabled";
				}
			}
		}
		document.onmouseup = Custom.clear;
	},
	pushed: function() {
		element = this.nextSibling;
		if(element.checked == true && element.type == "checkbox") {
			this.style.backgroundPosition = "0 -" + checkboxHeight*3 + "px";
		} else if(element.checked == true && element.type == "radio") {
			this.style.backgroundPosition = "0 -" + radioHeight*3 + "px";
		} else if(element.checked != true && element.type == "checkbox") {
			this.style.backgroundPosition = "0 -" + checkboxHeight + "px";
		} else {
			this.style.backgroundPosition = "0 -" + radioHeight + "px";
		}
	},
	check: function() {
		element = this.nextSibling;
		if(element.checked == true && element.type == "checkbox") {
			this.style.backgroundPosition = "0 0";
			element.checked = false;
		} else {
			if(element.type == "checkbox") {
				this.style.backgroundPosition = "0 -" + checkboxHeight*2 + "px";
			} else {
				this.style.backgroundPosition = "0 -" + radioHeight*2 + "px";
				group = this.nextSibling.name;
				inputs = document.getElementsByTagName("input");
				for(a = 0; a < inputs.length; a++) {
					if(inputs[a].name == group && inputs[a] != this.nextSibling) {
						inputs[a].previousSibling.style.backgroundPosition = "0 0";
					}
				}
			}
			element.checked = true;
		}
	},
	clear: function() {
		inputs = document.getElementsByTagName("input");
		for(var b = 0; b < inputs.length; b++) {
			if(inputs[b].type == "checkbox" && inputs[b].checked == true && inputs[b].className == "styled") {
				inputs[b].previousSibling.style.backgroundPosition = "0 -" + checkboxHeight*2 + "px";
			} else if(inputs[b].type == "checkbox" && inputs[b].className == "f_styled") {
				inputs[b].previousSibling.style.backgroundPosition = "0 0";
			} else if(inputs[b].type == "radio" && inputs[b].checked == true && inputs[b].className == "styled") {
				inputs[b].previousSibling.style.backgroundPosition = "0 -" + radioHeight*2 + "px";
			} else if(inputs[b].type == "radio" && inputs[b].className == "f_styled") {
				inputs[b].previousSibling.style.backgroundPosition = "0 0";
			}
		}
	},
	choose: function() {
		option = this.getElementsByTagName("option");
		for(d = 0; d < option.length; d++) {
			if(option[d].selected == true) {
				document.getElementById("select" + this.name).childNodes[0].nodeValue = option[d].childNodes[0].nodeValue;
			}
		}
	}
}
window.onload = Custom.init;

</script>
</head>
<body bgcolor="<?php echo($background_color); ?>" text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>" class="background">
<div align="center" class="frm_logout">
<div>
  <iframe name="printFrame" id="printFrame" src="blank.php?<?echo('DOMAINID='.$domainId);?>" frameborder="0" border="0" width="0" height="0" style="visibility: hidden"></iframe>
  <table border="0" align="right" cellpadding="0" cellspacing="0">
    <tr>
      <td>
        <!--
          <a href="#" onClick="parent.close();" class="normlink"><?php echo($close_window_label); ?></a>
        -->
      </td>
    </tr>
 </table>                
  <p align="left" class="title"><b><?php echo($logout_message_label); ?></b></p>
                           
<?
if ($complete != '') {
?>                       
     <p align="left"><strong><?php echo($rating_thank_you_label); ?></strong></p>
<?
}
else {
?>
        
  <form name="rateSession" method="post" action="logout.php?client_domain_id=<?php echo($domain_id);?><?echo('&DOMAINID='.$domainId);?>&URL=<?php echo urlencode($_REQUEST['URL']); ?>">
    <div style="padding:0 20px 20px">
    <table border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td colspan="2" align="left" class="top">
               <p><?php echo($please_rate_service_label); ?>:</p>
           </td>
      </tr>
      <tr>
     
        <td align="left" width="0">
            <p class="label"><span><?php echo($rate_service_label); ?>:</span></p>
            <div style="float:left">
            <select name="RATING" id="RATING" class="f_styled"

            <?php
              echo("<option value='5'>".$excellent_label."</option>");
              echo("<option value='4'>".$very_good_label."</option>");
              echo("<option value='3'>".$good_label."</option>");
              echo("<option value='2'>".$fair_label."</option>");
              echo("<option value='1'>".$poor_label."</option>");
           ?>
          </div>
            <input type="submit" name="Submit" value="<?php echo($rate_label); ?>" class="bt_rate"/>
            <!--<input type="submit" name="Submit" value="" class="bt_rate"/>-->
        </td>
      </tr>
            
      <tr>
         <td colspan="2" align="left">
        <p class="label"><span><?php echo($your_email_label); ?>:</span></p>
        <input name="EMAIL"  type="text" id="EMAIL" value="<?php echo($visitor_email); ?>" size="40" class="inputbox"/>      
        </td>
      </tr>          
      <tr>      
        <td align="left">
          <input name="SEND_SESSION" type="checkbox" value="1">
          <?php echo($send_copy_session); ?>
      </td>
      </tr>            
      
      <tr>
        <td>
            <p><?php echo($further_assistance_label); ?></p>
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
   </div> 
    </table>
         <input type="Hidden" name="LANGUAGE" value="<?=LANGUAGE_TYPE?>">
  </form>
  </p>
  <?php
}
?>
</div>
</div>
</body>
</html>