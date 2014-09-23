<?php
include_once('import/constants.php');

$_REQUEST['URL'] = !isset( $_REQUEST['URL'] ) ? '' : (string) $_REQUEST['URL'];
$_REQUEST['TITLE'] = !isset( $_REQUEST['TITLE'] ) ? '' : htmlspecialchars( (string) $_REQUEST['TITLE'], ENT_QUOTES );
$_REQUEST['DEPARTMENT'] = !isset( $_REQUEST['DEPARTMENT'] ) ? '' : htmlspecialchars( (string) $_REQUEST['DEPARTMENT'], ENT_QUOTES );
$_REQUEST['ERROR'] = !isset( $_REQUEST['ERROR'] ) ? '' : htmlspecialchars( (string) $_REQUEST['ERROR'], ENT_QUOTES );
$_REQUEST['COOKIE'] = !isset( $_REQUEST['COOKIE'] ) ? '' : htmlspecialchars( (string) $_REQUEST['COOKIE'], ENT_QUOTES );
$_REQUEST['SERVER'] = !isset( $_REQUEST['SERVER'] ) ? '' : htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES );

$use_phone   =1;
$use_company =1;

if (isset($_REQUEST['DOMAINID'])){
  $domain_id = (int) $_REQUEST['DOMAINID'];
}

// Agent ID

if (isset($_REQUEST['AGENTID'])){
   $agent_id = (int) $_REQUEST['AGENTID'];
}

 if ($agent_id =='') {
       $agent_id ='0';
     }


if (!isset($_REQUEST['URL'])) {
        header('Location: offline.php?'.'DOMAINID='.$domain_id.'&LANGUAGE='.LANGUAGE_TYPE);
        exit();
}

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

include('./import/constants.php');
$database = include($install_path . $install_directory . '/import/config_database.php');
if ($database) {
        include($install_path . $install_directory . '/import/block_spiders.php');
        include($install_path . $install_directory . '/import/class.mysql.php');
        $installed = include($install_path . $install_directory . '/import/config.php');
} else {
        $installed = false;
}


//$domain_Id = $domain_id;

if ($installed == false) {      
        header('Location: ' . $install_directory . '/offline.php?URL=' . urlencode( $_REQUEST['URL'] ) . '&DOMAINID='.$domain_id.'&LANGUAGE='.LANGUAGE_TYPE);
        exit();
}

$error = $_REQUEST['ERROR'];


if ($installed == true) {

        $department = $_REQUEST['DEPARTMENT'];
        $current_page = $_REQUEST['URL'];
        $title = $_REQUEST['TITLE'];

        // Update the Current URL, URL Title and Referer in the requests table.
        $current_page = $_REQUEST['URL'];
        for ($i = 0; $i < 3; $i++) {
                $substr_pos = strpos($current_page, '/');
                if ($substr_pos === false) {
                        $current_page = '';
                        break;
                }
                if ($i < 2) {
                        $current_page = substr($current_page, $substr_pos + 1);
                }
                elseif ($i >= 2) {
                        $current_page = substr($current_page, $substr_pos);
                }
        }

        // Get the current host from the request data
        $current_host = $_REQUEST['URL'];
        $str_start = 0;
        for ($i = 0; $i < 3; $i++) {
                $substr_pos = strpos($current_host, '/');
                if ($substr_pos === false) {
                        break;
                }
                if ($i < 2) {
                        $current_host = substr($current_host, $substr_pos + 1);
                        $str_start += $substr_pos + 1;
                }
                elseif ($i >= 2) {
                        $current_host = substr($_REQUEST['URL'], 0, $substr_pos + $str_start);
                }
        }

       
         // Deparment disable
         $disable_department = $departments;

        // Joomla Auto Login
        $query = "SELECT visitor_name , visitor_email FROM " . $table_prefix . "requests WHERE id = " . ( (int) $request_id );
        $row = $SQL->selectquery($query);
        
         if (is_array($row)) {
          
          if ($row['visitor_name'] != '') 
          {  
            $username = $row['visitor_name'];
            $email = $row['visitor_email'];
            $autologin =TRUE;
            
            // Count available departments
            $query = "SELECT DISTINCT u.department FROM " . $table_prefix . "users u, " . $table_prefix . "domain_user du WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.refresh)) < '$connection_timeout' and u.status = '1' and u.id = du.id_user And du.id_domain = " . $domain_id;
            $rows = $SQL->selectall($query);
                                       
             if (is_array($rows)) {
               $dep_num = count($rows);
             }
           }
         }        
                   
 //  User Recognition Auto Start
 if ($disable_login_details == false && $autologin == true && $dep_num ==1 ) {       
    
      header('Location: ' . $install_directory . '/frames.php?URL=' . urlencode( $_REQUEST['URL'] ) . '&SERVER=' . $_REQUEST['SERVER'].'&DOMAINID='.$domain_id .'&USER='.$username .'&EMAIL='.$email .'&AGENTID='.$agent_id.'&LANGUAGE='.LANGUAGE_TYPE);
    exit();
    }
  
        // Update the current URL statistics within the requests tables
        if ($current_page == '') { $current_page = '/'; }

        $query = "SELECT `path` FROM " . $table_prefix . "requests WHERE `id` = '" . ( (int) $request_id ) . "'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $current_page = urldecode($current_page);
                $prev_path = explode(';  ', $row['path']);
                $current_path = $row['path'];

                end($prev_path);
                $index = key($prev_path);


                if ($current_page != $prev_path[$index]) {
                        $query = "UPDATE " . $table_prefix . "requests SET `url` = '$current_page', `title` = '$title', `path` = '$current_path;  $current_page', number_pages = number_pages + 1 WHERE `id` = '$request_id'";

                        $SQL->insertquery($query);
                }
        }
                // new condition in order to support safari third-party cookies restriction.
          if (empty($request_id) && !isset($_COOKIE[$cookieName])) {
                //TODO - Revisar este codigo debe mejorarse cuando el usuario va directamente al archivo index.php              
                    header('Location: ' . $install_directory . '/cookies.php?SERVER=' . $_REQUEST['SERVER'] . '&COOKIE=true'.'&DOMAINID='.$domain_id);
                exit();              
           }

        // Checks if any users in user table are online
        if ($error == '') {
                $query = "SELECT u.id FROM " . $table_prefix . "users u, " . $table_prefix . "domain_user du WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.refresh)) < '$connection_timeout' AND u.status = '1' And du.id_user = u.id And du.id_domain = ".$domain_id;
                if ($department != '' && $departments == true && $agent_id ==0) 
                    { $query .= " and `answers`=1 and department LIKE '%$department%'"; }
                  else
                if ($agent_id ==0) 
                    { $query .= " and `answers`=1 "; }
                  else                                    
                if ( $agent_id !=0)                
                { $query .= " and `answers`=2 and u.id=" .$agent_id; }
                
                $row = $SQL->selectquery($query);

                
 
                if(!is_array($row))
                   {
              
                      header('Location: ' . $install_directory . '/offline.php?DOMAINID='.$domain_id.'&SERVER='.$_REQUEST['SERVER'].'&URL='. urlencode( $_REQUEST['URL'] ) .'&LANGUAGE='.LANGUAGE_TYPE);                        
                      
                        exit();
                }
        }

        if ($disable_login_details == true) {
                header('Location: ' . $install_directory . '/frames.php?URL=' . urlencode( $_REQUEST['URL'] ) . '&SERVER=' . $_REQUEST['SERVER'].'&DOMAINID='.$domain_id. '&AGENTID='.$agent_id. '&LANGUAGE='.LANGUAGE_TYPE);
                
                exit();
        }

        //invalidating old 'GUEST_LOGIN_ID' to create a new session for the chat
        if (isset($_COOKIE[$cookieName])) {
            $session = array();
            $session = unserialize($_COOKIE[$cookieName]);

            $session['GUEST_LOGIN_ID'] = "0";
            $data = serialize($session);

            setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);

            header("P3P: CP='$p3p'");

            /*
            foreach ($session as $key => $value) {

            }
            */

            unset($session);

        }


}

header('Content-type: text/html; charset=' . CHARSET);

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';
if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}


include_once('import/settings_default.php');
  
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo($livehelp_name); ?></title>
<link href="<?php echo $install_directory; ?>/style/styles.php?<?php echo('DOMAINID='.$domain_id); ?>" rel="stylesheet" type="text/css">

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script language="JavaScript" type="text/JavaScript">
  jQuery.noConflict();
  
  // Para cuando termine de cargar todo, incluso las imágenes.
  jQuery(window).load(function () {
      w = jQuery(".frm_login").width() + 50; //480;
      h = jQuery(".frm_login").height() + 40; // 40 por el botón flotante
	  
	  // Case 1
	  // window.alert("h 1: " + h + ", w 1: " +  jQuery(window).height() + ", b 1: " +  jQuery(document).height());

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
	  
	  // Case 2
	  // window.alert("h 2: " + h + ", w 2: " + jQuery(window).height() + ", b 2: " +  jQuery(document).height());

	  window.setTimeout(function() { window.resizeTo(w, h + (h - jQuery(window).height())); }, 25);

      window.moveTo(wleft, wtop);
	  
	  // Case 3
	  // window.alert("h 3: " + h + ", w 3: " + jQuery(window).height() + ", b 3: " +  jQuery(document).height());
  })
  
</script>


<script language="JavaScript" type="text/JavaScript">

var selectWidth = "200";

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

<script language="JavaScript" type="text/JavaScript">
<!--
function disableForm() {
        document.login.Submit.disabled = true;
        return true;
}

function chForm() {
<?php
if($require_guest_details == 1) {
?>
        if(document.getElementById("USER").value == "") {
                alert("<?php echo($empty_user_details_label); ?>")
                return false
        }
        if(document.getElementById("EMAIL").value == "") {
                alert("<?php echo($empty_email_details_label); ?>")
                return false
        }
        if(document.getElementById("EMAIL").value.indexOf("@") == -1) {
                alert("<?php echo($empty_valid_email_details_label); ?>")
                return false
        }
<?php
}
?>
        disableForm()
        document.getElementById("login").submit()
}

//-->
 
</script>

</head>

<body bgcolor="<?php echo($background_color); ?>" text="<?php echo($font_color); ?>" link="<?php echo($font_link_color); ?>" vlink="<?php echo($font_link_color); ?>" alink="<?php echo($font_link_color); ?>">

<!--<?php echo CHARSET; ?>-->

<!--img src="./i18n/<?php echo LANGUAGE_TYPE; ?>/pictures/background_online.gif" width="265" height="49" style="position: relative; right: -200px; top: 10px;"-->
<?php
if ($error == 'email') {
?>
<strong><?php echo($invalid_email_error_label); ?></strong>
<?php
}
if ($error == 'empty') {
?>
<strong><?php echo($empty_user_details_label); ?></strong>
<?php
}
?>

<?php 
// Settings 
          $query = "SELECT name, value FROM " . $table_prefix . "settings WHERE name in ('disable_language','phone','company') and id_domain = $domain_id";
                   $rows = $SQL->selectall($query);
                            if(is_array($rows)) {
                             foreach ($rows as $key => $row) {                                                                                                       
                              if (is_array($row)) {                                                               
                                 $setting = $row['name'];     
                                                                                             
                                if($setting == "disable_language") {
                                     $disable_language = $row['value'];                                    
                                      }                                                                                                                         
                                   elseif($setting == "phone") {
                                      $use_phone = $row['value'];
                                       }
                                   else { 
                                       $use_company = $row['value'];
                                       }    
                                }
                              }
                             }                                        
  ?>
<style type="text/css">
<!--
body { background-color: #f2f2f2; font-family: arial;}

.background {margin: 5px auto 0; background:#e1e1e1 url(./pictures/skins/<?php echo($chat_background_img); ?>/bg.png) repeat-x; border:1px solid #d4d4d4; border-radius:5px; position: relative;width:380px;}

#login a { text-decoration: underline; color:#2794c7}

.frm_login td{ padding:2px 0}
.frm_login .inputbox { border:1px solid #d4d4d4; background-color: #f8f8f8; height:25px; line-height: 25px; padding-left:5px; width:200px; border-radius:5px}
.frm_login .label { color:#0095e1; text-transform:uppercase; font-size: 11px; float:left; width:145px;}
.frm_login .subheader { border-bottom:1px dotted #ccc; padding:0 0 15px}


.frm_login .bt_submit { background: url(./pictures/skins/<?php echo($chat_background_img); ?>/bg-submit-chat.png) no-repeat; width: 146px; height: 40px; position: absolute; right:55px; bottom: -20px; padding:6px 0 0}
.bt_chat { background: #222 url(./pictures/skins/<?php echo($chat_background_img); ?>/overlayy.png) repeat-x; display: inline-block; padding: 5px 10px 6px; color: #fff; text-decoration: none; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius:5px; -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5); -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5); text-shadow: 0 -1px 1px rgba(0,0,0,0.25);  position: relative; font-family:Calibri, Arial, sans-serif;}

select.f_styled { height:27px}
span.select { background: url(./pictures/skins/<?php echo($chat_background_img); ?>/select2.png) no-repeat; padding: 0 0 0 5px; width:150px; position: absolute; height:27px; line-height:27px; overflow: hidden;}
/* .bt_chat { margin-left: 32px; } */
-->
</style>
<div class="frm_login background">  

<form name="login" id="login" method="POST" action="frames.php?SERVER=<?php echo($_REQUEST['SERVER']); ?>&URL=<?php echo($_REQUEST['URL']); ?><?php echo('&DOMAINID='.$domain_id.'&AGENTID='.$agent_id.'&LANGUAGE='.LANGUAGE_TYPE); ?>">

    <div style="padding:10px">
    <input type="hidden" name="DOMAINID" value="<?php echo($domain_id); ?>"/>
    <table border="0" align="center" cellspacing="0" class="tbl_login">
            <tr>
                <td><p class="title"><b><?php echo($welcome_to_label); ?> <?php echo($site_name);?> <?php echo($our_live_help_label); ?><b><br></p></td>
            </tr>
            <tr>
                <td><?php echo($enter_guest_details_label); ?></td>
            </tr>
            <tr>
                <td class="subheader"><?php echo($else_send_message_label); ?> <a href="offline.php?SERVER=<?php echo($_REQUEST['SERVER']); ?>&URL=<?php echo urlencode( $_REQUEST['URL']); ?><?php echo('&DOMAINID='.$domain_id.'&LANGUAGE='.LANGUAGE_TYPE);?>" class="normlink"><?php echo($offline_message_label); ?></a></td>
            </tr>
            <tr>
                <td>
                    <p class="label"><span><?php echo($name_label); ?>:</span></p>
                    <?php if ($username !='') { ?> 
                      <font face="arial" size="2"><input name="USER" id="USER" type="text" value ="<?php echo($username); ?>"  READONLY ="TRUE"  style="filter:alpha(opacity=75);moz-opacity:0.75" maxlength="20" class="inputbox"/></font>
                    <?php } else { ?>                        
                      <font face="arial" size="2"><input name="USER" id="USER" type="text" style="filter:alpha(opacity=75);moz-opacity:0.75" maxlength="20" class="inputbox"/></font>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="label"><span><?php echo($email_label); ?>:</span></p>
                    
                    <?php if ($email !='') { ?>
                        <font face="arial" size="2"><input type="text"  value ="<?php echo($email); ?>" name="EMAIL" id="EMAIL"  READONLY ="TRUE"  style="filter:alpha(opacity=75);moz-opacity:0.75" class="inputbox/></font>
                    <?php } else { ?>
                        <font face="arial" size="2"><input type="text"  name="EMAIL" id="EMAIL" style="filter:alpha(opacity=75);moz-opacity:0.75" class="inputbox"/></font>
                    <?php } ?>
                </td>
            </tr> 
             <?php if  ($use_phone ==1) { ?>  
             <tr>
                <td>
                    <p class="label"><span><?php echo($your_phone_label); ?>:</span></p>                        
                    <?php if ($phone !='') { ?>
                    <font face="arial" size="2"><input type="text" name="PHONE" id="PHONE" style="filter:alpha(opacity=75);moz-opacity:0.75" class="inputbox"/></font>
                    <?php } ?>
                </td>
            </tr> 
              <?php
                    } 
               if  ($use_company ==1) {    
               ?>
            
             <tr>
                <td>
                    <p class="label"><span><?php echo($your_company_label); ?>:</span></p>             
                    <?php if ($company !='') { ?>
                    <font face="arial" size="2"><input type="text" name="COMPANY" id="COMPANY" style="filter:alpha(opacity=75);moz-opacity:0.75" class="inputbox"/></font>
                    <?php } ?>
                </td>
            </tr>  
           <?php }  ?>                    
         <?php
         // Languague display option 
         
          $query = "SELECT code, name FROM " . $table_prefix . "languages_domain Where Id_domain = " . $domain_id . " Order By name";
          $lang_count = $SQL->selcount($query);  
          
        /*  $disable_language =0; 
          // find the custom form link
          $query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='disable_language'";
          
         $row = $SQL->selectquery($query);
         if (is_array($row)) {
           $disable_language = $row['value'];                                  
           }
        */
         ?>        
        
        <?php if ($lang_count > 1 && $disable_language ==0) { ?>
            
       <tr>
                    <td><p class="label"><span><?php echo($select_language_label); ?>:</span></p>                                                                        
                    <select name="LANGUAGE" class="f_styled">
<?php
    $query = "SELECT code, name FROM " . $table_prefix . "languages_domain Where Id_domain = " . $domain_id . " Order By name";
     
    $rows = $SQL->selectall($query);
    
    foreach ($rows as $key => $row) {
?>
                                    <option value="<?php echo strtolower($row["code"]); ?>"<?php echo ($row["code"] == $language ? " selected" : ""); ?>><?php echo $row["name"]; ?>
<?php
    }
?>
                            </select>
                    </td>
            </tr>
<?php } ?>
   
         
<?php

if ($disable_department == true && $department == '' && $installed == true || $error == 'empty')  {
?>
                  <tr>
                <td>
                <p class="label"><span><?php echo($department_label); ?>:</span></p>                          
                <select name="DEPARTMENT"  class="f_styled">
                <?php
                $query = "SELECT DISTINCT u.department FROM " . $table_prefix . "users u, " . $table_prefix . "domain_user du WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.refresh)) < '$connection_timeout' AND u.status = '1' And u.id = du.id_user And du.id_domain = " . $domain_id;
               
               if($agent_id ==0) 
                 { $query .= " and `answers`='1' "; }
                else 
              if($agent_id !=0) 
                { $query .= " and `answers`='2' and `id`= $agent_id"; }
        
               
                $rows = $SQL->selectall($query);
                                                 
                if (is_array($rows)) {
                $distinct_departments = array();
                foreach ($rows as $key => $row) {
                if (is_array($row)) {
                $department = $row['department'];
                $departments = split ('[;]',  $row['department']);
                if (is_array($departments)) {
                foreach ($departments as $key => $department) {
                $department = trim($department);
                if (!in_array($department, $distinct_departments)) {
                $distinct_departments[] = $department;
                ?>
                <option value="<?php echo($department); ?>"><?php echo($department); ?></option>
                <?php
                }
                }
                } else {
                $department = trim($department);
                if (!in_array($department, $distinct_departments)) {
                $distinct_departments[] = $department;
                ?>
                <option value="<?php echo($department); ?>"><?php echo($department); ?></option>
                <?php
                }
                }
                }
                }
                }
                ?>
                </select>
                </td>
            </tr>                                
<?php

} else if (($departments == true) || ($department != '')) {
?>
 <input name="DEPARTMENT" type="hidden" value="<?php echo($department); ?>">
<?php
}
?>

<?php
if ($_REQUEST['COOKIE'] != '') {
        $cookie_domain = $_REQUEST['COOKIE'];
?>
                                <input name="COOKIE" type="hidden" value="<?php echo($cookie_domain); ?>">
<?php 
}
    
?>
<tr>
<td colspan="2" class="bt_submit">
<div><div>	<div style="position: relative; left: 50%; float: left;">		<div style="position: relative; left: -50%; float: left;">
			<input name="Submit" type="button" id="Submit" value="<?php echo($continue_label); ?>" onClick="return chForm()" class="bt_chat"/>
			<!--<input name="Submit" type="button" id="Submit" value="<?php echo($continue_label); ?>" onClick="return chForm()" class="bt_chat"/>-->
		</div>	</div></div></div>
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
</div>
</form>
<div style="clear: both;"></div>
</div>

<div style="clear: both;"></div>
</body>
</html>


