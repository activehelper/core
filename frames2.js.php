<!--

//supportTimer();

function supportTimer() {
        var timer = setTimeout('supportTimer()', 1000);

        var currentTime = document.message_form.TIMER.value.split(":");
        currentMinutes = currentTime[0];
        currentSeconds = currentTime[1];

        if (currentMinutes.charAt(0) == '0') {
                currentMinutes = currentMinutes.charAt(1);
        }

        if (currentSeconds.charAt(0) == '0') {
                currentSeconds = currentSeconds.charAt(1);
        }

        if (currentTime[1] < 59) {
                var minutes = parseInt(currentMinutes);
                var seconds = parseInt(currentSeconds) + 1;
        }
        else {
                var minutes = parseInt(currentMinutes) + 1;
                var seconds = 0;
        }

        if (minutes < 10) {
                minutes = '0' + minutes;
        }

        if (seconds < 10) {
                seconds = '0' + seconds;
        }

        newTime = minutes + ":" + seconds;
        document.message_form.TIMER.value = newTime;
}

    function replaceCharacters(value) {
     value = value.replace(/</g, "&lt;");
     value = value.replace(/>/g, "&gt;");
     value = value.replace(/\r/g, "<br/>");
     value = value.replace(/\n/g, "<br/>");
     return value;
    }

function processForm() {
        var message = document.message_form.MESSAGE.value;
        message = replaceCharacters(message);
        typing(false);
        top.display('<?php echo( htmlspecialchars( (string) $_REQUEST["username"], ENT_QUOTES ) ); ?>', message, '1', '0');
        void(document.message_form.submit());
        document.message_form.MESSAGE.value = '';

        if (top.document.message_form.MESSAGE) {
                if (top.document.message_form.MESSAGE.disabled == false) {
                        top.document.message_form.MESSAGE.focus();
                }
        }
        return false;
}

function appendText(text) {
        var current = document.message_form.MESSAGE.value;
        document.message_form.MESSAGE.value = current + text;

        if (top.document.message_form.MESSAGE) {
                if (top.document.message_form.MESSAGE.disabled == false) {
                        top.document.message_form.MESSAGE.focus();
                }
        }
}

function checkEnter(e) {
        var characterCode;
        typing(true);

        if (e.keyCode == 13 || e.charCode == 13) {
                processForm();
                return false;
        } else {
                return true;
        }
}

function mouseDown(e) {
        if(navigator.userAgent.indexOf("Opera") > 0) {
                return false;
        }
        if (parseInt(navigator.appVersion)>3) {
                var clickType=1
                if (navigator.appName=="Netscape") {
                        clickType=e.which
                } else {
                        clickType=event.button
                }
        if (clickType!=1) {
                        alert ('ACTIVEHELPER Platform All Rights Reserved.')
                        return false;
                }
        }
        return true;
}
//document.onmousedown = mouseDown
//if (navigator.appName=="Netscape") {
  //      document.captureEvents(Event.MOUSEDOWN)
//}
//-->
