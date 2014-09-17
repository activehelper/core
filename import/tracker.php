<?php
include_once('constants.php');


if (!isset($_SERVER['DOCUMENT_ROOT'])){ $_SERVER['DOCUMENT_ROOT'] = ''; }
if (!isset($_REQUEST['TITLE'])){ $_REQUEST['TITLE'] = ''; } else $_REQUEST['TITLE'] = htmlspecialchars( (string) $_REQUEST['TITLE'], ENT_QUOTES );
if (!isset($_REQUEST['URL'])){ $_REQUEST['URL'] = ''; } else $_REQUEST['URL'] = (string) $_REQUEST['URL'];
if (!isset($_REQUEST['INITIATE'])){ $_REQUEST['INITIATE'] = ''; } else $_REQUEST['INITIATE'] = htmlspecialchars( (string) $_REQUEST['INITIATE'], ENT_QUOTES );
if (!isset($_REQUEST['REFERRER'])){ $_REQUEST['REFERRER'] = ''; } else $_REQUEST['REFERRER'] = (string) $_REQUEST['REFERRER'];
if (!isset($_REQUEST['WIDTH'])){ $_REQUEST['WIDTH'] = ''; } else $_REQUEST['WIDTH'] = (int) $_REQUEST['WIDTH'];
if (!isset($_REQUEST['HEIGHT'])){ $_REQUEST['HEIGHT'] = ''; } else $_REQUEST['HEIGHT'] = (int) $_REQUEST['HEIGHT'];
if (!isset($_REQUEST['COOKIE'])){ $_REQUEST['COOKIE'] = ''; } else $_REQUEST['COOKIE'] = htmlspecialchars( (string) $_REQUEST['COOKIE'], ENT_QUOTES );
$domain_id = isset( $domain_id ) ? (int) $domain_id : null;
$agent_id = isset( $agent_id ) ? (int) $agent_id : null;

$command = 'tracker';
if (isset($_SERVER['PATH_TRANSLATED']) && $_SERVER['PATH_TRANSLATED'] != '')
{
  $env_path = $_SERVER['PATH_TRANSLATED'];
}
else
{
  $env_path = $_SERVER['SCRIPT_FILENAME'];
}

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



include_once('config_database.php');
include_once('class.mysql.php');
include_once('config.php');
                

//$domainIsValid = true;
//$title = $_REQUEST['TITLE'];
//$url = $_REQUEST['URL'];
$title = mysql_real_escape_string($_REQUEST['TITLE']);
$url = mysql_real_escape_string(urldecode(trim($_REQUEST['URL'])));

$initiate = $_REQUEST['INITIATE'];
$referrer = $_REQUEST['REFERRER'];
$width = $_REQUEST['WIDTH'];
$height = $_REQUEST['HEIGHT'];

$userid = $_REQUEST['USERID'] = (int) $_REQUEST['USERID'];
$service_id = $_REQUEST['service_id'] = (int) $_REQUEST['service_id'];
// Google geo
$region      = $_REQUEST['region'] = htmlspecialchars( (string) $_REQUEST['region'], ENT_QUOTES );
$city        = $_REQUEST['city'] = htmlspecialchars( (string) $_REQUEST['city'], ENT_QUOTES );
$country     = $_REQUEST['country'] = htmlspecialchars( (string) $_REQUEST['country'], ENT_QUOTES );
$countrycode = $_REQUEST['countrycode'] = htmlspecialchars( (string) $_REQUEST['countrycode'], ENT_QUOTES );
$latitude    = $_REQUEST['latitude'] = htmlspecialchars( (string) $_REQUEST['latitude'], ENT_QUOTES );
$longitude   = $_REQUEST['longitude'] = htmlspecialchars( (string) $_REQUEST['longitude'], ENT_QUOTES );

if ($cookie_domain != '') {
        $cookie_domain = $_REQUEST['COOKIE'];
}

$ipaddress = $_SERVER['REMOTE_ADDR'];
$useragent = $_SERVER['HTTP_USER_AGENT'];

$request_initiated = false;
ignore_user_abort(true);

if ($request_id > 0) {
        // Select the Initiate flag to check if an Administrator has initiated the user with a Support request
        $query = "SELECT `initiate`, `status`, `services` FROM " . $table_prefix . "requests WHERE `id` = '$request_id'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                $request_initiate_flag = $row['initiate'];
                $request_status = $row['status'];
                $request_services = $row['services'];
                if ($request_initiate_flag > 0)
                {
                  $request_initiated = true;
                }

                // Update Initiate status fields to display the status of the floating popup.
                if ($initiate == 'Opened') {
                        // Update request flag to show that the guest user OPENED the Online Chat Request
                        $query = "UPDATE " . $table_prefix . "requests SET `refresh` = NOW(), `initiate` = '-1' WHERE `id` = '$request_id'";
                        $SQL->miscquery($query);
                }
                elseif ($initiate == 'Accepted') {
                        // Update request flag to show that the guest user ACCEPTED the Online Chat Request
                        $query = "UPDATE " . $table_prefix . "requests SET `refresh` = NOW(), `initiate` = '-2' WHERE `id` = '$request_id'";
                        $SQL->miscquery($query);
                }
                elseif ($initiate == 'Declined') {
                        // Update request flag to show that the guest user DENIED the Online Chat Request
                        $query = "UPDATE " . $table_prefix . "requests SET `refresh` = NOW(), `initiate` = '-3' WHERE `id` = '$request_id'";
                        $SQL->miscquery($query);
                }
                else {
                        if ($url == '' && $title == '') {  // Update current page time
                                $query = "UPDATE " . $table_prefix . "requests SET `refresh` = NOW(), `status` = '$request_status', services = '<".str_replace(",", "><", $_REQUEST['services']).">' WHERE `id` = '$request_id'";
                                $SQL->miscquery($query);
                        }
                        else {  // Update current page details
                                $query = "UPDATE " . $table_prefix . "requests SET `refresh` = NOW(), `request` = NOW(), `url` = '$url', `title` = '$title', `resolution` = '$width x $height', `status` = '0' WHERE `id` = '$request_id'";
                                $SQL->miscquery($query);
                        }
                }
        }
} else {

        //if ($width != '' && $height != '' && $title != '' && $url != '') {
        if ($width != '' && $height != ''&& $url != '') {

                //$page = $_REQUEST['URL'];
                $page = mysql_real_escape_string(urldecode(trim($_REQUEST['URL'])));

                for ($i = 0; $i < 3; $i++) {
                        $pos = strpos($page, '/');
                        if ($pos === false) {
                                $page = '';
                                break;
                        }
                        if ($i < 2) {
                                $page = substr($page, $pos + 1);
                        } elseif ($i >= 2) {
                                $page = substr($page, $pos);
                        }

                }
                if ($page == '') { $page = '/'; }
                $page = urldecode(trim($page));

                // Update the current URL statistics within the requests tables
                if ($referrer == '')
                {
                  $referrer = 'Direct Visit / Bookmark';
                }
            

                $query = "SELECT name FROM " . $table_prefix . "domains WHERE id_domain = '".$domain_id."'";
                $row = $SQL->selectquery($query);

                $domainName = $row['name'];

                if ((isset($domain_id)) && (!(strripos($domainName, $refDomain) === false)))
                {

                    
                    // increase the refresh value from 5 to 30 in order to support safari third-party cookies restriction.
                    
                  $query = "SELECT 1 FROM " . $table_prefix . "requests WHERE ipaddress = '$ipaddress' And useragent = '$useragent' And resolution = '$width x $height' And (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`datetime`)) < 30";
                  $row = $SQL->selectquery($query);
                  if (!is_array($row))
                  {
                    $query = "INSERT INTO " . $table_prefix . "requests(`ipaddress`, `useragent`, `resolution`, `datetime`, `request`, `refresh`, `url`, `title`, `referrer`, `path`, `initiate`, `status`, `id_domain`, number_pages, city, region, country_code, country, latitude, longitude) VALUES('$ipaddress', '$useragent', '$width x $height', NOW(), NOW(), NOW(), '$url', '$title', '$referrer', '$page', '0', '0', '" . $domain_id . "', 1, '$city' , '$region', '$countrycode', '$country', '$latitude', '$longitude')";
                    $request_id = $SQL->insertquery($query);

                    $session = array();
                    $session['REQUEST']  = $request_id;
                    $session['CHARSET']  = CHARSET;
                    $session['USERID']   = mysql_real_escape_string($_REQUEST['USERID']);
                    $session['DOMAINID'] = $domain_id;
                    $session['AGENTID']  = $agent_id;
                         
                    if (isset($_REQUEST['LANGUAGE'])){
                      $session['LANGUAGE'] = $_REQUEST['LANGUAGE'];
                    }else{
                      $session['LANGUAGE'] = LANGUAGE_TYPE;
                    }

                    $session['SERVICE'] = mysql_real_escape_string($_REQUEST['SERVICE']);

                    //$session['ACCOUNT'] = $account;
                    //$session['TRACKING'] = $tracking;
                    //$session['STATUS_INDICATOR'] = $status_indicator;

                    $data = serialize($session);

                    setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);
                    header("P3P: CP='$p3p'");
                  }
              }else{

                $session = array();

                $domainIsValid = false;
                $session['VALID'] = $domainIsValid;
                $data = serialize($session);

                setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);
                header("P3P: CP='$p3p'");

              }
        }
}

if ( isset( $_GET[ 'GET_INVITATION_MESSAGE' ] ) ) {
	$query = "SELECT init_message FROM " . $table_prefix . "requests WHERE id = '". (int) $request_id ."'";
	$row = $SQL->selectquery($query);

	if ( !empty( $row['init_message'] ) ) {
	 if ( isset( $_GET['json'] ) ) {
      $json = array( 'text' => $row['init_message'] );
      echo 's1.checkInitiate_json(' . json_encode($json) . ')';
    }
    else {
      echo $row['init_message'];
    }
	}

	die( '' );
}


header('Content-type: image/gif');
if ($request_initiated == true) {    

	if ( $request_initiate_flag == 2 ) {
        readfile($install_path . $install_directory . '/import/initiate-message.gif');
	} else {
        readfile($install_path . $install_directory . '/import/initiate.gif');
	}
}
else {
        readfile($install_path . $install_directory . '/import/tracker.gif');
}


?>