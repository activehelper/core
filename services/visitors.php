<?php
ob_start("ob_gzhandler");

include('../import/config_database.php');
include('../import/class.mysql.php');
include('../import/functions.php');
/*
    foreach ($_REQUEST as $key => $value) {
      error_log($key.":".$value."\n", 3, "../error.log");
    }

    foreach ($_SESSION as $key => $value) {
      error_log($key.":".$value."\n", 3, "../error.log");
    }
 */
checkSession();

// Open MySQL Connection
$SQL = new MySQL();
$SQL->connect();

if (!isset($_REQUEST['ACTION']))
{
   $_REQUEST['ACTION'] = '';
} else $_REQUEST['ACTION'] = htmlspecialchars( (string) $_REQUEST['ACTION'], ENT_QUOTES );
if (!isset($_REQUEST['REQUEST']))
{
   $_REQUEST['REQUEST'] = '';
} else $_REQUEST['REQUEST'] = (int) $_REQUEST['REQUEST'];
if (!isset($_REQUEST['RECORD']))
{
   $_REQUEST['RECORD'] = '';
} else $_REQUEST['RECORD'] = htmlspecialchars( (string) $_REQUEST['RECORD'], ENT_QUOTES );
if (!isset($_REQUEST['DATETIME']))
{
   $_REQUEST['DATETIME'] = '';
} else $_REQUEST['DATETIME'] = htmlspecialchars( (string) $_REQUEST['DATETIME'], ENT_QUOTES );
if (!isset($_REQUEST['LANGUAGE']))
{
   $_REQUEST['LANGUAGE'] = '';
} else $_REQUEST['LANGUAGE'] = htmlspecialchars( (string) $_REQUEST['LANGUAGE'], ENT_QUOTES );

if (!isset($_REQUEST['OPERATORID']))
{
   $operator_login_id = (int) $_REQUEST['OPERATORID'];
}

if (!isset($_REQUEST['PMESSAGE']))
{
   $_REQUEST['PMESSAGE'] = '';
} else $_REQUEST['PMESSAGE'] = (string) $_REQUEST['PMESSAGE'];


$language = $_REQUEST['LANGUAGE'];
$action = $_REQUEST['ACTION'];
$request = $_REQUEST['REQUEST'];
$record = $_REQUEST['RECORD'];
$date = $_REQUEST['DATETIME'];
$responceType = !isset($_REQUEST['DATA']) ? "full": htmlspecialchars( (string) $_REQUEST['DATA'], ENT_QUOTES );
$visitorId = !isset($_REQUEST['visitorId']) ? "": (int) $_REQUEST['visitorId'];
$popmessage = $_REQUEST['PMESSAGE'];

define('LANGUAGE_TYPE', $language);

$language_file = '../i18n/' . LANGUAGE_TYPE . '/lang_service_' . LANGUAGE_TYPE . '.php';
if (LANGUAGE_TYPE != '') {
        include($language_file);
}
else {
        include('../i18n/en/lang_service_en.php');
}

//error_log("Visitors:language_file:".$language_file."\n", 3, "error.log");
//error_log("Visitors:current_request_referrer_result:".$current_request_referrer_result."\n", 3, "error.log");

$charset = 'utf-8';
header('Content-type: text/xml; charset=' . $charset);
echo('<?xml version="1.0" encoding="' . $charset . '"?>' . "\n");

if ($action == 'pmessage' && $request != '')
{
   if ($popmessage != '')
   {
      // Update active field of user to the ID of the operator that initiated support
      $query = "UPDATE " . $table_prefix . "requests SET `initiate` = 2 , `init_message` = '$popmessage'  WHERE `id` = '$request'";
      $SQL->miscquery($query);
   }  
    
}


if ($action == 'Initiate' && $current_privilege < 3)
{
   if ($request != '')
   {
      // Update active field of user to the ID of the operator that initiated support
      $query = "UPDATE " . $table_prefix . "requests SET `initiate` = '$operator_login_id' WHERE `id` = '$request'";
      $SQL->miscquery($query);
   }
   else
   {
      // Initiate chat request with all visitors
      $query = "UPDATE " . $table_prefix . "requests SET `initiate` = '$operator_login_id' And id_domain in (" . $domains_set . ")";
      $SQL->miscquery($query);

   }
?>
<SiteVisitors TotalVisitors=""/>
<?php
   exit();
}
elseif ($action == 'Remove' && $current_privilege < 3)
{

   if ($request != '')
   {
      // Update active field of user to the ID of the operator that initiated support
      $query = "UPDATE " . $table_prefix . "requests SET `status` = '1' WHERE `id` = '$request'";
      $SQL->miscquery($query);

   }


?>
<SiteVisitors TotalVisitors=""/>
<?php
   exit();
}

if ($date != '')
{
   $query = "SELECT count(requests.id) FROM " . $table_prefix . "requests AS requests, " .
            $table_prefix . "sessions AS sessions WHERE requests.id = sessions.request ".
            "AND DATE_FORMAT(requests.datetime, '%Y-%m-%d') = '$date' AND `status` = '0' AND ".
            "(`active` = '-1' OR `active` = '-3') and id_domain in (" . $domains_set . ") group by ".
            $table_prefix . "requests.id";

   //error_log("visitor.php:query ------------>>:".$query."\n", 3, "../error.log");
}
else
{
   $query = "SELECT count(`id`) FROM " . $table_prefix . "requests WHERE (UNIX_TIMESTAMP(NOW()) ".
            " - UNIX_TIMESTAMP(`refresh`)) < '45' AND `status` = '0' and id_domain in (" . $domains_set . ")";
            
  // error_log("visitor.php:query ------------>>:".$query."\n", 3, "error.log");            
}
$row = $SQL->selectquery($query);
if (is_array($row))
{

   if ($date != '')
   {
      $totalvisitors = $row['count(requests.id)'];
   }
   else
   {
      $totalvisitors = $row['count(`id`)'];
   }

   if ($totalvisitors > 0 && $record != '')
   {

      //$query = "SELECT requests.*, ((UNIX_TIMESTAMP(requests.refresh) - UNIX_TIMESTAMP(requests.datetime))) AS `sitetime`, ((UNIX_TIMESTAMP(requests.refresh) - UNIX_TIMESTAMP(requests.request))) AS `pagetime` FROM " . $table_prefix . "requests AS requests, " . $table_prefix . "sessions AS sessions WHERE requests.id = sessions.request AND DATE_FORMAT(requests.datetime, '%Y-%m-%d') = '$date' AND `status` = '0' AND (`active` = '-1' OR `active` = '-3') and id_domain in (". $domains_set .") ORDER BY requests.request LIMIT " . ( (int) $record ) . ", 6";
      //error_log("visitor.php:query ------------>>:".$query."\n", 3, "../error.log");
      if ($date != '')
      {
         switch($responceType)
         {
            case "full":
               {
                  $query = "SELECT r.id As rid, s.id As sid, s.active, s.username, r.ipaddress, r.useragent,".
                           " r.resolution, r.url, r.title, r.referrer, r.initiate, r.path, r.services, s.department, s.rating, ".
                           "(UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP(r.datetime)) AS `sitetime`, (UNIX_TIMESTAMP(r.refresh)".
                           " - UNIX_TIMESTAMP(r.request)) AS `pagetime` , r.city ,r.region, r.country_code,r.country,r.latitude,r.longitude FROM " . $table_prefix . "requests AS r LEFT JOIN " .
                           "(select id, request, active, username, department, rating from ".$table_prefix."sessions where request = ".$visitorId." order by id desc LIMIT 1) AS s on r.id = s.request WHERE DATE_FORMAT(r.datetime, '%Y-%m-%d') = '$date' ".
                           "AND `status` = '0' AND `active` in (-1, -3) and id_domain in (" . $domains_set . ") " .
                            ($visitorId == "" ? "": "And r.id=" . $visitorId) . " group by r.id ORDER BY r.request LIMIT " . ( (int) $record ) . ", 6 ";

                  break;
               }
            case "standard":
               {
                  $query = "SELECT r.id As rid, s.id As sid, s.active, s.username, r.ipaddress, r.url, r.title, r.number_pages, ".
                           "(UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP(r.datetime)) AS `sitetime`, (UNIX_TIMESTAMP(r.refresh) - ".
                           "UNIX_TIMESTAMP(r.request)) AS `pagetime` FROM " . $table_prefix . "requests AS r LEFT JOIN ".
                           "(select id, request, active, username, department, rating from ".$table_prefix."sessions order by id desc) AS s on r.id = s.request WHERE DATE_FORMAT(r.datetime, '%Y-%m-%d') = '$date' AND `status` = '0' AND".
                           " `active` in (-1, -3) and id_domain in (" . $domains_set . ") " . ($visitorId == "" ? "": "And r.id=" .
                           $visitorId) . " group by r.id ORDER BY r.request LIMIT " . ( (int) $record ) . ", 100";



                  break;
               }
            case "lite":
               {
                  $query = "SELECT r.id As rid, r.title, (UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP".
                           "(r.datetime)) AS `sitetime`, (UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP(r.request)) AS `pagetime` FROM " .
                           $table_prefix . "requests AS r WHERE DATE_FORMAT".
                           "(r.datetime, '%Y-%m-%d') = '$date' AND `status` = '0' AND `active` in (-1, -3) and id_domain in (".$domains_set.
                           ") " . ($visitorId == "" ? "": "And r.id=" . $visitorId) . "  group by r.id ORDER BY r.request LIMIT " . ( (int) $record ) . ", 100";



                  break;
               }
         }
      }
      else
      {
         switch($responceType)
         {
            case "full":
               {
                  $query = "SELECT r.id As rid, s.id As sid, s.active, s.username, r.ipaddress, r.useragent, r.resolution, r.url, r.title, ".
                  "r.referrer, r.path, r.services, r.initiate, s.department, s.rating, (UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP(r.datetime)) ".
                  "AS `sitetime`, (UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP(r.request)) AS `pagetime` , r.city ,r.region, r.country_code,r.country,r.latitude,r.longitude FROM " . $table_prefix .
                  "requests AS r LEFT JOIN " . $table_prefix . "sessions  AS s on r.id = s.request  WHERE r.refresh > SUBTIME(NOW(), '45') ".
                  " AND r.status = '0' and r.id_domain in (" . $domains_set . ") " .
                  ($visitorId == "" ? "": "And r.id=" . $visitorId) . " group by r.id ORDER BY r.request LIMIT " . ( (int) $record ) . ", 6";

                  break;
               }
               // new standard tracking SQL
            case "standard":
               {
                  $query = "SELECT r.id As rid, s.id As sid, s.active, s.username, r.ipaddress, r.url, r.title, r.number_pages, ".
                  "(UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP(r.datetime)) AS `sitetime`, (UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP".
                  "(r.request)) AS `pagetime` FROM " . $table_prefix . "requests AS r LEFT JOIN " .$table_prefix."sessions AS s on r.id = s.request".
                  " WHERE  r.refresh > SUBTIME(NOW(), '45')  AND r.status = '0' and ".
                  "r.id_domain in (".$domains_set . ") " . ($visitorId == "" ? "": "And r.id=" . $visitorId) .
                  " ORDER BY r.request LIMIT " . ( (int) $record ) . ", 100";

                  break;
               }
            case "lite":
               {
                  $query = "SELECT r.id As rid, r.title, (UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP".
                  "(r.datetime)) AS `sitetime`, (UNIX_TIMESTAMP(r.refresh) - UNIX_TIMESTAMP(r.request)) AS `pagetime` FROM " .
                  $table_prefix . "requests AS r WHERE ".
                  " r.refresh > SUBTIME(NOW(), '45') AND r.status = '0' and r.id_domain in (" .
                  $domains_set . ") " . ($visitorId == "" ? "": "And r.id=" . $visitorId) .
                  " group by r.id ORDER BY r.request LIMIT " . ( (int) $record ) . ", 100";

                  break;
                  
                  
               }
         }
      }

   
   
      $rows = $SQL->selectall($query);
      if (is_array($rows))
      {
?><SiteVisitors TotalVisitors="<?php echo($totalvisitors);
?>">
<?php    /*
         $initiated_default_label = 'Live Help Request has not been Initiated';
         $initiated_sending_label = 'Sending the Initiate Live Help Request...';
         $initiated_waiting_label = 'Waiting on the Initiate Live Help Reply...';
         $initiated_accepted_label = 'Initiate Live Help Request was ACCEPTED';
         $initiated_declined_label = 'Initiate Live Help Request was DECLINED';
         $initiated_chatting_label = 'Currently chatting to Operator';
         $initiated_chatted_label = 'Already chatted to an Operator';
         $initiated_pending_label = 'Currently Pending for Live Help';
         $unavailable_label = 'Unavailable';
         */

         foreach ($rows as $key => $row)
         {

            if (is_array($row))
            {



               $current_request_id = $row['rid'];
               $current_request_ip_address = $row['ipaddress'];
               $current_request_user_agent = $row['useragent'];
               $current_request_resolution = $row['resolution'];
               $current_request_current_page = $row['url'];
               $current_request_current_page_title = $row['title'];
               $current_request_referrer = $row['referrer'];



                if($current_request_referrer == 'Direct Visit / Bookmark'){
                  $current_request_referrer = '';
                }

               $current_request_pagetime = $row['pagetime'];
               $current_request_page_path = $row['path'];
               $current_request_sitetime = $row['sitetime'];
               $current_request_request_flag = $row['initiate'];

                // Google geomap
                $current_request_region        =  $row['region'];
                $current_request_city          =  $row['city'];
                $current_request_country       =  $row['country'];
                $current_request_country_code  =  $row['country_code'];
                $current_request_latitude      =  $row['latitude'];
                $current_request_longitude     =  $row['longitude'];


//error_log("Visitor:current_request_request_flag:".$current_request_request_flag."\n", 3, "../error.log");

               $current_request_id_domain = $row['id_domain'];
               $current_request_services = $row['services'];
               $current_request_number_pages = $row['number_pages'];

               $current_session_id = $row['sid'];
               $current_session_username = $row['username'];
               $current_session_department = $row['department'];
               $current_session_rating = $row['rating'];
               $current_session_active = $row['active'];




               //                   ////error_log("\n current_request_current_page_title: ".$current_request_current_page_title."\n", 3, "/var/www/html/error.log");

               if (strlen($current_request_current_page_title) > 80)
               {
                  $current_request_current_page_title = substr($current_request_current_page_title, 0, 90) . '...';
               }
               if($responceType == "full")
               {
//error_log("Visitor:current_session_id:".$current_session_id."\n", 3, "../error.log");
                  if ($current_session_id != "")
                  {
                     if ($current_session_active == '-1' || $current_session_active == '-3')
                     {
                        // Display the rating of the ended chat request
                        if ($current_session_rating > 0)
                        {
                           $current_request_initiate_status = $initiated_chatted_label . ' - ' . $rating_label . ' (' . $current_session_rating . '/5)';
                        }
                        else
                        {
                           $current_request_initiate_status = $initiated_chatted_label;
                        }
                     }
                     else
                     {
                        if ($current_session_active > 0)
                        {
                           // Get the supporters name of the chat request if currently chatting.
                           $query = "SELECT `firstname`, `lastname` FROM " . $table_prefix . "users WHERE `id` = '$current_session_active'";
                           //error_log("visitor.php:query ------------>>:".$query."\n", 3, "../error.log");
                           $row = $SQL->selectquery($query);
                           if (is_array($row))
                           {
                              $current_session_support_name = $row['firstname'] . ' ' . $row['lastname'];
                              $current_request_initiate_status = $initiated_chatting_label . ' (' . $current_session_support_name . ')';
                           }
                           else
                           {
                              $current_request_initiate_status = $initiated_chatting_label . ' (' . $unavailable_label . ')';
                           }
                        }
                        else
                        {
                           if ($current_session_department != '')
                           {
                              $current_request_initiate_status = $initiated_pending_label . ' (' . $current_session_department . ')';
                           }
                           else
                           {
                              $current_request_initiate_status = $initiated_pending_label;
                           }
                        }
                     }
                  }
                  else
                  {
                     // The Site Visitor has not been sent an Initiate Chat request..
                     if ($current_request_request_flag == '0')
                     {
                        $current_request_initiate_status = $initiated_default_label;
                     }
                     elseif ($current_request_request_flag == '-1')
                     {// displayed the request..
                        $current_request_initiate_status = $initiated_waiting_label;
                     }
                     elseif ($current_request_request_flag == '-2')
                     {// accepted the request..
                        $current_request_initiate_status = $initiated_accepted_label;
                     }
                     elseif ($current_request_request_flag == '-3')
                     {// declined the request..
                        $current_request_initiate_status = $initiated_declined_label;
                     }
                     else
                     {// sent a request and waiting to open on screen..
                        $current_request_initiate_status = $initiated_sending_label;
                     }
                  }
                  $current_request_services = substr($current_request_services, 1);
                  $current_request_services = substr($current_request_services, 0, - 1);
                  $current_request_services = str_replace("><", ";", $current_request_services);
               }


               if ($current_request_referrer != '' && $current_request_referrer != 'false')
               {
                  $current_request_referrer_result = $current_request_referrer;
                  $current_request_referrer_type = 1;
               }
               elseif ($current_request_referrer == false)
               {
                  $current_request_referrer_type = 0;
                  //$current_request_referrer_result = 'Direct Visit / Bookmark';
               }
               else
               {
                  $current_request_referrer_result = $unavailable_label;
               }

//error_log(current_session_username.":".$current_session_username."\n", 3, "../error.log");

?><Visitor ID="<?php echo($current_request_id);?>" Session="<?php echo($current_session_id);?>" Active="<?php echo($current_session_active);?>" Username="<?php echo(xmlinvalidchars($current_session_username));?>" DATA="<?php echo $responceType; ?>">
<?php



//error_log("Visitors:".print_r($current_request_id,true). "SQL : " . $query . "\n", 3, "visitors.log");

               if($responceType == "full")
               {
?>
<Hostname><?php echo(xmlinvalidchars(gethostbyaddr($current_request_ip_address)));?></Hostname>
<Country><?php echo($current_request_country);?></Country>
<UserAgent><?php echo(xmlinvalidchars($current_request_user_agent));?></UserAgent>
<Resolution><?php echo(xmlinvalidchars($current_request_resolution));?></Resolution>
<CurrentPage><?php echo(xmlinvalidchars($current_request_current_page));?></CurrentPage>
<CurrentPageTitle><?php echo(xmlinvalidchars($current_request_current_page_title));?></CurrentPageTitle>
<Referrer><?php echo(xmlinvalidchars($current_request_referrer_result));?></Referrer>
<ReferrerType><?php echo($current_request_referrer_type);?></ReferrerType>
<TimeOnPage><?php echo($current_request_pagetime);?></TimeOnPage>
<ChatStatus><?php echo(xmlinvalidchars($current_request_initiate_status));?></ChatStatus>
<PagePath><?php echo(xmlinvalidchars($current_request_page_path));?></PagePath>
<TimeOnSite><?php echo($current_request_sitetime);?></TimeOnSite>
<services><?php echo($current_request_services);?></services>
<region><?php echo($current_request_region);?></region>
<city><?php echo($current_request_city);?></city>
<country_code><?php echo($current_request_country_code);?></country_code>
<latitude><?php echo($current_request_latitude);?></latitude>
<longitude><?php echo($current_request_longitude);?></longitude>

<?php
               }
               elseif($responceType == "standard")
               {
?>
<Hostname><?php echo(xmlinvalidchars(gethostbyaddr($current_request_ip_address)));?></Hostname>
<CurrentPage><?php echo(xmlinvalidchars($current_request_current_page));?></CurrentPage>
<CurrentPageTitle><?php echo(xmlinvalidchars($current_request_current_page_title));?></CurrentPageTitle>
<NumberPages><?php echo($current_request_number_pages);?></NumberPages>
<TimeOnSite><?php echo($current_request_sitetime);?></TimeOnSite>
<TimeOnPage><?php echo($current_request_pagetime);?></TimeOnPage>
<?php
               }
               elseif($responceType == "lite")
               {
?>
<TimeOnPage><?php echo($current_request_pagetime);?></TimeOnPage>
<TimeOnSite><?php echo($current_request_sitetime);?></TimeOnSite>
<CurrentPageTitle><?php echo(xmlinvalidchars($current_request_current_page_title));?></CurrentPageTitle>
<?php
               }
?>
</Visitor>
<?php
            }
         }
?>
</SiteVisitors>
<?php
      }
   }
   else
   {
?>
<SiteVisitors TotalVisitors="" />
<?php
   }
}
?>
