<?php
include_once('import/constants.php');

include('import/config_database.php');
include('import/class.mysql.php');
include('import/config.php');
$domain_id = (int) $domain_id;
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

if (!isset($_COOKIE['LiveHelpSession_'.$domain_id])) {
        header('Location: ' . $install_directory . '/cookies.php?SERVER=' . ( urlencode( htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES ) ) ) . '&COOKIE=true');
        exit();
}

$isOffline = false;
$query = "SELECT u.id FROM " . $table_prefix . "users u, " . $table_prefix . "domain_user du, " . $table_prefix . "sa_domain_user_role dur, " . $table_prefix . "sa_role_services rs WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.refresh)) < '$connection_timeout' AND u.status = '1' And du.id_user = u.id And du.id_domain = " . $domain_id . " And dur.id_domain_user = du.id_domain_user And rs.id_role = dur.id_role and rs.id_service = 4";
$row = $SQL->selectquery($query);
if(!is_array($row)) {
        $isOffline = true;
}

if($_REQUEST["kw"] == "cancel") {
        $query = "UPDATE " . $table_prefix . "webcall SET status = -1 id_webcall = '".$webCall_id."' WHERE `id` = '$request_id'";
        $SQL->miscquery($query);
        echo "Canceled";
        exit();
}
if(isset($_REQUEST["sbmt"])) {
        if($isOffline) {

                if ($configure_smtp == true) {
                        ini_set('SMTP', $smtp_server);
                        ini_set('smtp_port', $smtp_port);
                        ini_set('sendmail_from', $from_email);
                }

                $from_name = htmlspecialchars( (string) $_REQUEST["name"], ENT_QUOTES );
                $from_email = htmlspecialchars( (string) $_REQUEST["email"], ENT_QUOTES );
                $query = "SELECT value FROM " . $table_prefix . "settings WHERE name = 'offline_email' And id_domain = $domain_id";
                $row = $SQL->selectquery($query);
                if (is_array($row)) {
                        $offline_email = $row['value'];
                }
                $to_email = $offline_email;
                $subject = "Offline CallBack request";
                $headers = "From: " . $from_name . " <" . $from_email . ">\n";
                $headers .= "Reply-To: " . $from_name . " <" . $from_email . ">\n";
                $headers .= "Return-Path: " . $from_name . " <" . $from_email . ">\n";
                $message .= "\n\n--------------------------\n";
                $message .= "IP Logged:  " . $_SERVER['REMOTE_ADDR'] . "\n";
                $message .= "Name:  " . htmlspecialchars( (string) $_REQUEST['name'], ENT_QUOTES ) . "\n";
                $message .= "Question:  " . htmlspecialchars( (string) $_REQUEST['question'], ENT_QUOTES ) . "\n";
                $message .= "Email:  " . htmlspecialchars( (string) $_REQUEST['email'], ENT_QUOTES ) . "\n";
                $message .= "Language:  " . htmlspecialchars( (string) $_REQUEST['language'], ENT_QUOTES ) . "\n";
                $message .= "Phone code:  " . htmlspecialchars( (string) $_REQUEST['phone_code'], ENT_QUOTES ) . "\n";
                $message .= "Code area:  " . htmlspecialchars( (string) $_REQUEST['code'], ENT_QUOTES ) . "\n";
                $message .= "Phone:  " . htmlspecialchars( (string) $_REQUEST['phone'], ENT_QUOTES ) . "\n";
                $message .= "Message:  " . htmlspecialchars( (string) $_REQUEST['offlineMessage'], ENT_QUOTES ) . "\n";

                $sendmail_path = ini_get('sendmail_path');
                if ($sendmail_path == '') {
                        $headers = str_replace("\n", "\r\n", $headers);
                        $message = str_replace("\n", "\r\n", $message);
                }
                mail($to_email, $subject, $message, $headers);
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>---!---</title>
</head>
<body>
Offline message was sent!
<?php
        } else {
                $query = "Insert into " . $table_prefix . "webcall (id_webcall, request, Name, Question, Email, Language, Department, country, code, phone, status, request_time) Values ('', '".$request_id."', '".htmlspecialchars( (string) $_REQUEST["name"], ENT_QUOTES )."', '".htmlspecialchars( (string) $_REQUEST["question"], ENT_QUOTES )."', '".htmlspecialchars( (string) $_REQUEST["email"], ENT_QUOTES )."', '".htmlspecialchars( (string) $_REQUEST["language"], ENT_QUOTES )."', '".htmlspecialchars( (string) $_REQUEST["department"], ENT_QUOTES )."', '".htmlspecialchars( (string) $_REQUEST["country"], ENT_QUOTES )."', '".htmlspecialchars( (string) $_REQUEST["code"], ENT_QUOTES )."', '".htmlspecialchars( (string) $_REQUEST["phone"], ENT_QUOTES )."', 0, now())";
                $webCall_id = $SQL->insertquery($query);

                $session = array();
                $session = unserialize($_COOKIE['LiveHelpSession_'.$domain_id]);
                $session['WEBCALLID'] = $webCall_id;
                $data = serialize($session);
                setCookie('LiveHelpSession_'.$domain_id, $data, false, '/', $cookie_domain, $ssl);
                header("P3P: CP='$p3p'");
                unset($session);
?>
<html>
<head>
<title>---!---</title>
</head>

<body>
Current status is - <span id="serviceStatus">Waiting for Call Back</span>
<br/>
<a href="webc_form.php?kw=cancel">Cancel</a>
<iframe src="service_refresher.php" name="serviceRefresher" id="serviceRefresher" width="0" height="0" style="display: none;"></iframe>
<script>
function update(state) {
        document.getElementById("serviceStatus").innerHTML = state
}
</script>
<?php
        }
} else {
?>

<html>
<head>
<title>---!---</title>
</head>

<body>
<?php
if($isOffline) {
?>
We are OFFLINE!
<?php
}
?>
Please enter your information below.
<table>
<form method="post" action='webc_form.php'>
        <tr>
                <td>Name</td>
                <td><input type="text" name="name" size="20"/></td>
        </tr>
        <tr>
                <td>Question</td>
                <td><input type="text" name="question" size="20"/></td>
        </tr>
        <tr>
                <td>Email</td>
                <td><input type="text" name="email" size="20"/></td>
        </tr>
        <tr>
                <td>Language</td>
                <td>
                        <select name="language" style="width:175px;filter:alpha(opacity=75);moz-opacity:0.75">
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
<?php
        if(!$isOffline) {
?>
        <tr>
                <td>Department</td>
                <td>
                        <select name="department" style="width:175px;filter:alpha(opacity=75);moz-opacity:0.75">
<?php
                $query = "SELECT DISTINCT u.department FROM " . $table_prefix . "users u, " . $table_prefix . "domain_user du WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.refresh)) < '$connection_timeout' AND u.status = '1' And u.id = du.id_user And du.id_domain = " . $domain_id;
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
        }
?>
        <tr>
                <td>Country</td>
                <td>
                        <select name="country" id="country">
<?php
        $query = "SELECT phone_code, country FROM " . $table_prefix . "countries";
        $rows = $SQL->selectall($query);
        foreach ($rows as $key => $row) {
?>
                                <option value="<?php echo $row["phone_code"]; ?>"><?php echo $row["country"]; ?> [<?php echo $row["phone_code"]; ?>]</option>
<?php
        }
?>
                        </select>
                </td>
        </tr>
        <tr>
                <td>Code area</td>
                <td><input type="text" name="code" size="20"/></td>
        </tr>
        <tr>
                <td>Phone</td>
                <td><input type="text" name="phone" size="20"/></td>
        </tr>
        <tr>
                <td>Message</td>
                <td><textarea name="offlineMessage"></textarea></td>
        </tr>
        <tr>
                <td colspan="2">
                        <input type="Submit" name="sbmt" value="Call me" size="20"/>
                </td>
        </tr>
</form>
</table>
<?php
}
?>
</body>
</html>
