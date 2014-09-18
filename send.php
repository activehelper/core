<?php
include_once('import/constants.php');

include('./import/config_database.php');
include('./import/class.mysql.php');
include('./import/config.php');

ignore_user_abort(true);

if (!isset($_REQUEST['ID'])){ $_REQUEST['ID'] = ''; }
if (!isset($_REQUEST['STAFF'])){ $_REQUEST['STAFF'] = ''; }
if (!isset($_REQUEST['MESSAGE'])){ $_REQUEST['MESSAGE'] = ''; }
if (!isset($_REQUEST['RESPONSE'])){ $_REQUEST['RESPONSE'] = ''; }
if (!isset($_REQUEST['COMMAND'])){ $_REQUEST['COMMAND'] = ''; }

$id = $_REQUEST['ID'] != '' ? (int) $_REQUEST['ID'] : '';
$staff = $_REQUEST['STAFF'];
$message = htmlspecialchars( (string) $_REQUEST['MESSAGE'], ENT_QUOTES );
$response = htmlspecialchars( (string) $_REQUEST['RESPONSE'], ENT_QUOTES );
$command = $_REQUEST['COMMAND'] != '' ? (int) $_REQUEST['COMMAND'] : '';

// Check if the message contains any content else return headers
if ($message == '' && $response == '' && $command == '') { exit(); }


if (isset($_COOKIE['LiveHelpOperator']) && $id != '') {

        // Get id_domain for this message
        $query = "SELECT s.id_domain FROM " . $table_prefix . "sessions s, " . $table_prefix . "requests r WHERE s.id = " . $id . " And r.id = s.request";
        $rows = $SQL->selectall($query);
        if (is_array($rows)) {
                foreach ($rows as $key => $row) {
                        if (is_array($row)) {
                                $id_domain = $row['id_domain'];
                        }
                }
        }

        $session = array();
        $session = unserialize($_COOKIE['LiveHelpOperator']);

        $operator_login_id = $session['OPERATORID'];
        $operator_authentication = $session['AUTHENTICATION'];
        $language = $session['LANGUAGE'];
        $charset = $session['CHARSET'];

        if ($operator_login_id != '' && $operator_authentication != '') {

                $query = "SELECT `username` FROM " . $table_prefix . "users WHERE `id` = '$operator_login_id' AND `password` = '$operator_authentication'";
                $row = $SQL->selectquery($query);
                if (is_array($row)) {
                        $current_username = $row['username'];

                        if ($message != '') {
                                // Send messages from POSTed data
                                if ($staff) {
                                        $query = "INSERT INTO " . $table_prefix . "administration (`user`, `username`, `datetime`, `message`, `align`, `status`, `id_domain`) VALUES('$operator_login_id', '$current_username', NOW(), '$message', '1', '1', '$id_domain')";
                                        $SQL->insertquery($query);
                                }
                                else {
                                        $query = "INSERT INTO " . $table_prefix . "messages (`session`, `username`, `datetime`, `message`, `align`, `status`, `id_domain`) VALUES('$id', '$current_username', NOW(), '$message', '1', '1', '$id_domain')";
                                        $SQL->insertquery($query);
                                }
                        }

                        // Format the message string
                        $response = trim($response);

                        if ($response != '') {
                                // Send messages from POSTed response data
                                $query = "INSERT INTO " . $table_prefix . "messages ( `session`, `username`, `datetime`, `message`, `align`, `status`, `id_domain`) VALUES ( '$id', '$current_username', NOW(), '$response', '1', '1', '$id_domain')";
                                $SQL->insertquery($query);
                        }
                        if ($command != '') {
                                $query = "SELECT * FROM " . $table_prefix . "commands WHERE `id` = '$command'";
                                $row = $SQL->selectquery($query);
                                if (is_array($row)) {
                                        $type = $row['type'];
                                        $description = $row['description'];
                                        $content = addslashes($row['contents']);

                                        switch ($type) {
                                                case '1':
                                                        $status = 2;
                                                        $command = addslashes($description . " \r\n " . $content);
                                                        $operator = '';
                                                        break;
                                                case '2':
                                                        $status = 3;
                                                        $command = addslashes($description . " \r\n " . $content);
                                                        $operator = '';
                                                        break;
                                                case '3':
                                                        $status = 4;
                                                        $command = addslashes($content);
                                                        $operator = addslashes('The ' . $description . ' has been PUSHed to the visitor.');
                                                        break;
                                                case '4':
                                                        $status = 5;
                                                        $command = addslashes($content);
                                                        $operator = addslashes('The ' . $description . ' has been sent to the visitor.');
                                                        break;
                                        }

                                        if ($command != '') {
                                                $query = "INSERT INTO " . $table_prefix . "messages (`session`, `datetime`, `message`, `align`, `status`, `id_domain`) VALUES ('$id', NOW(), '$command', '2', '$status', '$id_domain')";
                                                if ($operator != '') {
                                                        $query .= ", ('', '$id', NOW(), '$operator', '2', '-1')";
                                                }
                                                $SQL->insertquery($query);
                                        }

                                }
                        }
                }
        }
}
else {

        // Get id_domain for this message
        $query = "SELECT s.id_domain FROM " . $table_prefix . "sessions s, " . $table_prefix . "requests r WHERE s.id = " . $guest_login_id . " And r.id = s.request";
        $rows = $SQL->selectall($query);
        if (is_array($rows)) {
                foreach ($rows as $key => $row) {
                        if (is_array($row)) {
                                $id_domain = $row['id_domain'];
                        }
                }
        }
  //error_log("send.php:query: ".$query." \n", 3, "error.log");
        $message = str_replace('<', '&lt;', $message);
        $message = str_replace('>', '&gt;', $message);
        $message = preg_replace("/(\r\n|\r|\n)/", '<br />', $message);

        $message = trim($message);

        if ($message != '') {
                // Send messages from POSTed data
                $query = "INSERT INTO " . $table_prefix . "messages (`session`, `username`, `datetime`, `message`, `align`, `id_domain`) VALUES ('$guest_login_id', '$guest_username', NOW(), '$message', '1', '$id_domain')";
                $SQL->insertquery($query);
                if($SQL->db_error != "") {
?>
<script>
alert("<?php echo $SQL->db_error; ?>")
</script>
<?php
                }
        }
}
header('Content-type: text/html; charset=' . CHARSET);

?>
<!DOCTYPE BLANK PUBLIC>
