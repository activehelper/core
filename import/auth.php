<?php
include_once('constants.php');

if (!isset($_REQUEST['USERNAME'])){ $_REQUEST['USERNAME'] = ''; } else $_REQUEST['USERNAME'] = htmlspecialchars( (string) $_REQUEST['USERNAME'], ENT_QUOTES );
if (!isset($_REQUEST['PASSWORD'])){ $_REQUEST['PASSWORD'] = ''; } else $_REQUEST['PASSWORD'] = htmlspecialchars( (string) $_REQUEST['PASSWORD'], ENT_QUOTES );

if (isset($_COOKIE['LiveHelpOperator'])) {
        $session = array();
        $session = unserialize($_COOKIE['LiveHelpOperator']);

        if (!isset($session['MESSAGE'])){ $session['MESSAGE'] = 0; }
        if (!isset($session['TIMEOUT'])){ $session['TIMEOUT'] = 0; }

      #  error_log("1. operator_login_id: ".$operator_login_id."\n", 3, "auth.log");

        $operator_login_id = $session['OPERATORID'];
        $operator_authentication = $session['AUTHENTICATION'];
        $guest_message = $session['MESSAGE'];
        $timeout = $session['TIMEOUT'];
        $language = $session['LANGUAGE'];
        $charset = $session['CHARSET'];

        $current_user_id = $operator_login_id;

       # error_log("1. current_user_id: ".$current_user_id."\n", 3, "auth.log");

        $md5_password = $operator_authentication;

        if ($current_user_id != '' && $md5_password != '')  {
                $query = "SELECT `username`, `department`, `privilege`, `datetime` FROM " . $table_prefix . "users WHERE `id` = '$current_user_id' AND `password` = '$md5_password'";
                $row = $SQL->selectquery($query);
                if (is_array($row)) {
                        $current_username = $row['username'];
                        $current_department = $row['department'];
                        $current_privilege = $row['privilege'];
                        $current_login_datetime = $row['datetime'];
                }
                else {
                        header('Location: ' . $install_directory . '/import/auth_error.php');
                        exit;
                }
        }
        else {
                header('Location: ' . $install_directory . '/import/auth_error.php');
                exit;
        }

}
// If loading the script with HTTP $_REQUEST Authentication
elseif ($_REQUEST['USERNAME'] != '' && $_REQUEST['PASSWORD'] != '') {

        $username = $_REQUEST['USERNAME'];
        $md5_password = $_REQUEST['PASSWORD'];

        $query = "SELECT `id`, `username`, `department`, `privilege` FROM " . $table_prefix . "users WHERE `username` REGEXP BINARY '^$username$' AND `password` = '$md5_password'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $operator_login_id = $row['id'];
                $current_username = $row['username'];
                $current_department = $row['department'];
                $current_privilege = $row['privilege'];

              #  error_log("3. $operator_login_id: ".$operator_login_id."\n", 3, "auth.log");

                $session = array();
                $session['OPERATORID'] = $operator_login_id;

             #  error_log("4. session:". print_r($session,true)."\n", 3, "auth.log");

                $session['AUTHENTICATION'] = $md5_password;
                $session['MESSAGE'] = 0;
                $session['LANGUAGE'] = $language;
                $session['CHARSET'] = $charset;
                $data = serialize($session);

                setCookie('LiveHelpOperator', $data, false, '/', $cookie_domain, $ssl);

        }
        else {
                if (strpos(php_sapi_name(), 'cgi') === false ) { header('HTTP/1.0 403 Forbidden'); } else { header('Status: 403 Forbidden'); }
                exit;
        }

}
else {
        header('Location: ' . $install_directory . '/import/auth_error.php');
        exit;
}

?>
