<?php
include_once('import/constants.php');

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

$URL = !isset( $_REQUEST['URL'] ) ? '' : (string) $_REQUEST['URL'];

$installed = false;
$database = include($install_path . $install_directory . '/import/config_database.php');
if ($database) {
        include($install_path . $install_directory . '/import/block_spiders.php');
        include($install_path . $install_directory . '/import/class.mysql.php');
        $installed = include($install_path . $install_directory . '/import/config.php');
} else {
        $installed = false;
}

$language_file = './i18n/' . LANGUAGE_TYPE . '/lang_guest_' . LANGUAGE_TYPE . '.php';
if (file_exists($language_file)) {
        include($language_file);
}
else {
        include('./i18n/en/lang_guest_en.php');
}
?>
<!--
//Check Status Functions

var lastMessageID = 0;
var loggingOut = false;
var chatEnded = false;
var currentlyTyping = false;
var initalisedChat = 0;

function windowLogout() {
        if (loggingOut == false) {
                top.location.href = './logout.php';
                loggingOut = true;
        }
}

function currentTime() {
        var date = new Date();
        return date.getTime();
}

function manualLogout() {
        loggingOut = true;
}

function toggle(object) {

  if (document.getElementById) {
        var obj = document.getElementById(object);
    if (obj.style.visibility == 'visible') {
      obj.style.visibility = 'hidden';
        } else {
      obj.style.visibility = 'visible';
        }
  }
  else if (document.layers && document.layers[object] != null) {
        var obj = document.layers[object];
    if (obj.visibility == 'visible' || obj.visibility == 'show' )  {
      obj.visibility = 'hidden';
    } else {
      obj.visibility = 'visible';
        }
  }
  else if (document.all) {
        var obj = document.all[object];
    if (obj.style.visibility == 'visible') {
      obj.style.visibility = 'hidden';
    } else {
      obj.style.visibility = 'visible';
        }
  }

  return false;
}

function high(object) {
  if (object.style.MozOpacity) {
    object.style.MozOpacity = 1
  } else if (object.filters) {
   object.filters.alpha.opacity = 100
  }
}

function low(which2) {
  if (which2.style.MozOpacity) {
    which2.style.MozOpacity = 0.75
  } else if (which2.filters) {
    which2.filters.alpha.opacity = 75
  }
}

function swapImgRestore() {
        var i,x,a=document.sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function preloadImages() {
        var d=document; if(d.images){ if(!d.p) d.p=new Array();
                var i,j=d.p.length,a=preloadImages.arguments; for(i=0; i<a.length; i++)
                if (a[i].indexOf("#")!=0){ d.p[j]=new Image; d.p[j++].src=a[i];}}
}

function findObj(n, d) {
        var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
                d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
                if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
                for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=findObj(n,d.layers[i].document);
                if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function swapImage() {
        var i,j=0,x,a=swapImage.arguments; document.sr=new Array; for(i=0;i<(a.length-2);i+=3)
                if ((x=findObj(a[i]))!=null){document.sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function stripslashes(str) {
str=str.replace(/\\'/g,'\'');
str=str.replace(/\\"/g,'"');
str=str.replace(/\\\\/g,'\\');
str=str.replace(/\\0/g,'\0');
return str;
}

function display(username, message, align, status) {

        if (top.displayFrame) {
                var output;
                var alignment;

                if (align == '1') { alignment = 'left'; }
                else if (align == '2') { alignment = 'center'; }
                else if (align == '3') { alignment = 'right'; }

                if (status == '0') {
                        color = '<?php echo($sent_font_color); ?>'
                } else {
                        color = '<?php echo($received_font_color); ?>';
                }

                while(message.indexOf(":-)") >= 0 || message.indexOf(";-P") >= 0 || message.indexOf(":)") >= 0 || message.indexOf("$-D") >= 0 || message.indexOf("8-)") >= 0 || message.indexOf(":-/") >= 0 || message.indexOf(":-O") >= 0 || message.indexOf(":(") >= 0 || message.indexOf(":-(") >= 0 || message.indexOf(":-|") >= 0 || message.indexOf(":--") >= 0 || message.indexOf("/-|") >= 0){
                        message = message.replace(/:-\)/, '<img src="pictures/smilie_01.gif">');
                        message = message.replace(/;-P/, '<img src="pictures/smilie_04.gif">');
                        message = message.replace(/:\)/, '<img src="pictures/smilie_08.gif">');
                        message = message.replace(/\$-D/, '<img src="pictures/smilie_03.gif">');
                        message = message.replace(/8-\)/, '<img src="pictures/smilie_07.gif">');
                        message = message.replace(/:-\//, '<img src="pictures/smilie_05.gif">');
                        message = message.replace(/:-O/, '<img src="pictures/smilie_12.gif">');
                        message = message.replace(/:\(/, '<img src="pictures/smilie_06.gif">');
                        message = message.replace(/:-\(/, '<img src="pictures/smilie_02.gif">');
                        message = message.replace(/:-\|/, '<img src="pictures/smilie_09.gif">');
                        message = message.replace(/:--/, '<img src="pictures/smilie_10.gif">');
                        message = message.replace(/\/-\|/, '<img src="pictures/smilie_11.gif">');

                }

                message = stripslashes(message);
                output = '<table width="100%" border="0" align="center"><tr><td><div align="' + alignment + '"><font color="' + color + '" class="message">';

                if (status == '0' || status == '1' || status == '2') { // General Message or Link
                  if (username != '') { output += '<strong>' + username + '</strong>: '; }
                  message = message.replace(/((?:(?:http(?:s?))|(?:ftp)):\/\/[^\s|<|>|'|\"]*)/g, '<a href="javascript:location.reload(true)" onclick="top.window.opener.location.href=\'$1\';top.window.opener.focus();" class="message">$1</a>');
                  output += message;
                } else if (status == '3') { // Image
                  message = message.replace(/((?:(?:http(?:s?))):\/\/[^\s|<|>|'|\"]*)/g, '<img src="$1" alt="Received Image">');
                  output += message;
                } else if (status == '4') { // PUSH
                  //output += '<script language="JavaScript" type="text/JavaScript">window.open("' + message + '","mywindow","width=400,height=200,toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes,scrollbars=yes,copyhistory=yes,resizable=yes");</script>';
                  output += '<script language="JavaScript" type="text/JavaScript">if (top.window.opener) { top.window.opener.location.href = "' + message + '"; top.window.opener.focus(); }</script>';

                } else if (status == '5') { // JavaScript
                  //alert('JavaScript');
                  output += '<script language="JavaScript" type="text/JavaScript">' + message + '</script>';
                }else if (status == '6') { // HTML
                  if (username != '') { output += '<strong>' + username + '</strong>: '; }
                  output += message;
                  //alert(message);
                }
                output += '</font></div></td></tr></table>';
				
				// Detect browser:
				isAndroid = (/android/gi).test(navigator.appVersion);
				isIDevice = (/iphone|ipad/gi).test(navigator.appVersion);
				isTouchPad = (/hp-tablet/gi).test(navigator.appVersion);

				if ( isAndroid || isIDevice || isTouchPad) {
					var $b = jQuery(
						jQuery(
							jQuery("#displayFrame")[0].contentWindow.document
						)
						.find("frame[name=displayContentsFrame]")[0].contentWindow.document
					)
					.find("body");
					var w = '<div id="wrapper"><div id="scroller"></div></div>';
					
					if (!jQuery("#wrapper", $b).length) {
						$b.removeClass("background");
						jQuery($b).html(w);
					
						top.displayFrame.displayContentsFrame.load_myScroll();
					}

					jQuery("#scroller", $b).append(output);
					top.displayFrame.displayContentsFrame.myScroll.refresh();

					var wH = jQuery("#wrapper", $b).height();
					var sH = jQuery("#scroller", $b).height();
					if (sH > wH) {
						top.displayFrame.displayContentsFrame.myScroll.scrollTo(0, (sH - wH) * -1, 100);
					}
				}
				else {
					top.displayFrame.displayContentsFrame.document.write(output);
					top.bottom();
				}
        }
         //alert(message);
               
         if("<?php echo $guest_username; ?>" != username) {
            
          if ("<?php echo $sound_alert_new_message; ?>" != 0 && typeof( Audio ) != "undefined" )
            {
                 var snd = new Audio();
                         
             if(!!(snd.canPlayType && snd.canPlayType('audio/ogg; codecs="vorbis"').replace(/no/, '')))
               snd.src = "sounds/receive.ogg";
             else if(!!(snd.canPlayType && snd.canPlayType('audio/mpeg;').replace(/no/, '')))
               snd.src = "sounds/receive.mp3";
             else if(!!(snd.canPlayType && snd.canPlayType('audio/mp4; codecs="mp4a.40.2"').replace(/no/, '')))
               snd.src = "sounds/receive.m4a";
             else
               snd.src = "sounds/receive.wav";
                
               snd.play();
              }
              
            window.focus()
        }
}

function afterDisplayNew(){
}

function setTyping() {
        try { top.document.getElementById('messengerStatus').innerHTML = '<?php echo $user_typing_gif; ?>'; } catch (e) {}
}

function setWaiting() {
        try { top.document.getElementById('messengerStatus').innerHTML = '<?php echo $waiting_gif; ?>'; } catch (e) {}
}

function refreshDisplayer() {
        window.setTimeout('updateMessages();',<?php echo((int)$chat_refresh_rate * 1000); ?>);
}

function updateMessages() {
        if (top.displayFrame && chatEnded == false) { top.displayFrame.displayRefreshFrame.location.reload(true); }
}

function bottom() {
        if (top.displayFrame) {              
          top.displayFrame.displayContentsFrame.window.scrollTo(0,9999999);         
        }
}

var MessageTimer;
var LiveHelpXMLHTTP;

function checkXMLHTTP() {
  var obj = null;
  try {
    obj = new ActiveXObject("Msxml2.XMLHTTP");
  } catch(e) {
    try {
      obj = new ActiveXObject("Microsoft.XMLHTTP");
    } catch(oc){
      obj = null;
    }
  }
  if(!obj && typeof XMLHttpRequest != "undefined") {
    obj = new XMLHttpRequest();
  }
  return obj;
}

function LoadMessagesFrame() {
        if (top.displayFrame && chatEnded == false) {
                //
                top.displayFrame.displayRefreshFrame.document.onload = window.setTimeout('LoadMessagesFrame();', 3000);
//              alert('<?php echo $install_directory; ?>/refresher.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?>&DOMAINID=<?php echo( (int) $domain_id ); ?>&URL=<?php echo urlencode( $URL ); ?>&lastMessageID=' + lastMessageID);
                top.displayFrame.displayRefreshFrame.location.href = '<?php echo $install_directory; ?>/refresher.php?DOMAINID=<?php echo( (int) $domain_id ); ?>&LANGUAGE=<?php echo LANGUAGE_TYPE; ?>&URL=<?php echo urlencode( $URL ); ?>&lastMessageID=' + lastMessageID;
        }
}

LiveHelpXMLHTTP = checkXMLHTTP();

function LoadMessages() {

        if (LiveHelpXMLHTTP) {


                // Run the XML query
                if (LiveHelpXMLHTTP.readyState != 0) {
                        LiveHelpXMLHTTP.abort();
                }

                var time = currentTime();
                var URL = '<?php echo $install_directory; ?>/refresher.php?LANGUAGE=<?php echo LANGUAGE_TYPE; ?>&DOMAINID=<?php echo( (int) $domain_id ); ?>&JS=1&TYPING=' + currentlyTyping + '&INIT=' + initalisedChat + '&COOKIE=<?php echo($cookie_domain); ?>&TIME=' + time + '&URL=<?php echo urlencode( $URL ); ?>&lastMessageID=' + lastMessageID;
//              alert(URL);
                LiveHelpXMLHTTP.open('GET', URL, true);

                LiveHelpXMLHTTP.onreadystatechange = function() {
                        if (LiveHelpXMLHTTP.readyState == 4) {
                          // Process response as JavaScript
                          if (LiveHelpXMLHTTP.status == 200) {
                                eval(LiveHelpXMLHTTP.responseText);
                          } else {
//                              alert("There was a problem retrieving the Live Help chat data:\n");
                          }
                        }
                };

                LiveHelpXMLHTTP.send(null);

                // Load the messages again
                MessageTimer = window.setTimeout('LoadMessages();', 3000);

        } else {
                LoadMessagesFrame();
        }

}

function typing(status) {
        var updateIsTypingStatus = new Image
        var time = currentTime()

        if (LiveHelpXMLHTTP) {
                if (status == true) {
                        status = 1
                } else {
                        status = 0
                }
                currentlyTyping = status;
        } else {
                if (status == true) {
                        var message = document.message_form.MESSAGE.value;
                        var intLength = message.length;
                        if (intLength == 0) {
                                typing(false);
                        } else {
                                updateTypingStatus.src = '<?php echo $install_directory; ?>/typing.php?ID=<?php echo($login_id); ?>&STATUS=1&TIME=' + time;
                        }
                } else {
                        updateTypingStatus.src = '<?php echo $install_directory; ?>/typing.php?ID=<?php echo($login_id); ?>&STATUS=0&TIME=' + time;
                }
        }
}



//-->

