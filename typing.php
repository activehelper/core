<?php  
include_once('import/constants.php');

include('./import/config_database.php');
include('./import/class.mysql.php');
include('./import/config.php');

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

ignore_user_abort(true);

$id = (int) $_REQUEST['ID'];
$status = (bool) $_REQUEST['STATUS'];

$query = "SELECT `typing` FROM " . $table_prefix . "sessions WHERE `id` = '$id'";
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
        $query = "UPDATE " . $table_prefix . "sessions SET `typing` = '$result' WHERE `id` = '$id'";
        $SQL->miscquery($query);
        
}

header("Location: " . $install_directory . "/import/tracker.gif");
?>
