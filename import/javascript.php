<?php

include_once('constants.php');
include_once('string_util.php');

if (!isset($_SERVER['DOCUMENT_ROOT'])){ $_SERVER['DOCUMENT_ROOT'] = ''; }
if (!isset($_REQUEST['DEPARTMENT'])){ $_REQUEST['DEPARTMENT'] = ''; } else $_REQUEST['DEPARTMENT'] = htmlspecialchars( (string) $_REQUEST['DEPARTMENT'], ENT_QUOTES );
if (!isset($_REQUEST['SERVER'])){ $_REQUEST['SERVER'] = ''; } else $_REQUEST['SERVER'] = htmlspecialchars( (string) $_REQUEST['SERVER'], ENT_QUOTES );
if (!isset($_REQUEST['TRACKER'])){ $_REQUEST['TRACKER'] = ''; } else $_REQUEST['TRACKER'] = (bool) $_REQUEST['TRACKER'];
if (!isset($_REQUEST['STATUS'])){ $_REQUEST['STATUS'] = ''; } else $_REQUEST['STATUS'] = htmlspecialchars( (string) $_REQUEST['STATUS'], ENT_QUOTES );
if (!isset($_REQUEST['TITLE'])){ $_REQUEST['TITLE'] = ''; } else $_REQUEST['TITLE'] = htmlspecialchars( (string) $_REQUEST['TITLE'], ENT_QUOTES );

$_REQUEST['USERID'] = ""; 

if (!isset($_REQUEST['services'])) {$_REQUEST['services'] = '';} else $_REQUEST['services'] = htmlspecialchars( (string) $_REQUEST['services'], ENT_QUOTES );

if (isset($_SERVER['PATH_TRANSLATED']) && $_SERVER['PATH_TRANSLATED'] != '') { $env_path = $_SERVER['PATH_TRANSLATED']; } else { $env_path = $_SERVER['SCRIPT_FILENAME']; }
$full_path = str_replace("\\\\", "\\", $env_path);
$livehelp_path = $_SERVER['PHP_SELF'];

if (strpos($full_path, '/') === false)
{ $livehelp_path = str_replace("/", "\\", $livehelp_path);
}

$pos = strpos($full_path, $livehelp_path);    

if ($pos === false) {
        $install_path = $full_path; 
}
else {

        $install_path = substr($full_path, 0, $pos);           
}

$installed = false;

$database = include($install_path . $install_directory . '/import/config_database.php');

if ($database) {
        include($install_path . $install_directory . '/import/block_spiders.php');

        include($install_path . $install_directory . '/import/class.mysql.php');

        $installed = include($install_path . $install_directory . '/import/config.php');

} else {
        $installed = false;
}

// HTTP/1.1
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);

// HTTP/1.0
header('Pragma: no-cache');

header('Content-type: text/html; charset=' . CHARSET);



if ($installed == false) {
        include($install_path . $install_directory . '/import/settings_default.php');
?>
<!--


function statusClass (s_id) {
        this.s_id = s_id
// JavaScript Check Status Functions

        function openInfo(statusImage, e) {
                return false;
        }

        function infoImageLoad() {
                return false;
        }

        function displayInfo() {
                return false;
        }

        function closeInfo() {
                return false;
        }

        function cancelClosingInfo() {
                return false;
        }

        function openLiveHelp() {
                return false;
        }
}
//-->
<?php
        exit();
}
if ($installed == true) {

$department = mysql_real_escape_string($_REQUEST['DEPARTMENT']);
$tracker_enabled = $_REQUEST['TRACKER'];
$title = $_REQUEST['TITLE'];
$referer = mysql_real_escape_string($_SERVER['HTTP_REFERER']);

if ($tracker_enabled == '') { $tracker_enabled = true; }

// Get the current host from the referer (the page the JavaScript was called from)
$host = $_SERVER['HTTP_REFERER'];
$start = 0;
for ($i = 0; $i < 3; $i++) {
        $pos = strpos($host, '/');
        if ($pos === false) {
                break;
        }
        if ($i < 2) {
                $host = substr($host, $pos + 1);
                $start += $pos + 1;
        }
        elseif ($i >= 2) {
                $host = substr($_SERVER['HTTP_REFERER'], 0, $pos + $start);
        }
}


if ($request_id > 0) {

        $query = "SELECT `path` FROM " . $table_prefix . "requests WHERE `id` = '$request_id'";
        $row = $SQL->selectquery($query);
        if (is_array($row)) {
                // Get the current page from the referer (the page the JavaScript was called from)
                $page = $_SERVER['HTTP_REFERER'];
                for ($i = 0; $i < 3; $i++) {
                        $pos = strpos($page, '/');
                        if ($pos === false) {
                                $page = '';
                                break;
                        }
                        if ($i < 2) {
                                $page = substr($page, $pos + 1);
                        }
                        elseif ($i >= 2) {
                                $page = substr($page, $pos);
                        }
                }

                $page = mysql_real_escape_string(urldecode(trim($page)));
                $path = $row['path'];
                $previouspath = explode('; ', $path);

                if ($page != trim(end($previouspath))) {
                        $query = "UPDATE " . $table_prefix . "requests SET `request` = NOW(), `url` = '$referer', `path` = '$path;  $page', `status` = '0', number_pages = number_pages + 1 WHERE `id` = '$request_id'";
                        $SQL->miscquery($query);
                }
                else {
                        $query = "UPDATE " . $table_prefix . "requests SET `request` = NOW(), `url` = '$referer', `status` = '0' WHERE `id` = '$request_id'";
                        $SQL->miscquery($query);
                }
                $query = "UPDATE " . $table_prefix . "requests SET services = '<".str_replace(",", "><", mysql_real_escape_string($_REQUEST['services'])).">'  WHERE `id` = '$request_id'";
                $sql_rez = $SQL->miscquery($query);

        }
}

// Offline custom form link
$offline_custom_link ="";

// Offline
$offline =0;

if (($domain_id == 0) && (isset($_SERVER['HTTP_REFERER'])))
{
      $array = parse_url($_SERVER['HTTP_REFERER']);
      $domain_name = $array['host'];
      $domain_name = mysql_real_escape_string(str_ireplace("www.", "",$domain_name));
      
    if  ($domain_name != '') { 
        $query = "SELECT `id_domain` FROM " . $table_prefix . "domains WHERE `name` LIKE '%$domain_name%' LIMIT 1";

    $row = $SQL->selectquery($query);
    if (is_array($row)) {
        $domain_id = $row['id_domain'];
            }
      }
}


$query = "SELECT u.id FROM " . $table_prefix . "users u, " . $table_prefix . "domain_user du WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.refresh)) < '$connection_timeout' and u.status = '1' and u.answers= '1' and du.id_user = u.id And du.id_domain = ".$domain_id;
 if ($department != '' && $departments == true)   
 { $query .= " AND department LIKE '%$department%'"; }
 
$row = $SQL->selectquery($query);

if(!is_array($row))
{ 
    $offline =1; 
    
    // find the custom form link
    $query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='custom_offline_form_link'";

    $row = $SQL->selectquery($query);
    if (is_array($row)) {
        $offline_custom_link = $row['value'];
            }
                           
    // find the disable status indicator
    $query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='disable_tracking_offline'";

    $row = $SQL->selectquery($query);
    if (is_array($row)) {
        $disable_off_tracking = $row['value'];
            }            
}
    
    
// Geolocation 
  $disable_geolocation = 0;
   
 $query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='disable_geolocation'";

    $row = $SQL->selectquery($query);
    if (is_array($row)) {
        $disable_geolocation = $row['value'];
        }
            


?>
<!--
// <?php echo($velaio_livehelp_version_label . "\n"); ?>

// Geo Tracking Load

var region      = '';
var country     = '';
var city        = '';
var countrycode = '';
var latitude    = '';
var longitude   = '';

function initgeo(geoDict)
 { countrycode = geoDict['CountryCode'];
   country     = geoDict['CountryName'];
   city        = geoDict['City'];
   latitude    = geoDict['Latitude'];
   longitude   = geoDict['Longitude'];
   region      = geoDict['RegionName'];
  }

//--- BEGIN GEO LOCATION  ---

 var _vlGeolocationoff =<?php echo($disable_geolocation); ?>;

  if(_vlGeolocationoff !=1){
    document.writeln('<script src="http://s99.velaio.com/iplocation/ip_query.php?output=json&callback=initgeo" type="text/javascript"></script>');
  }
  
//--- END GEO LOCATION  ---

// JavaScript Check Status Functions
  var topMargin = 10;
  var leftMargin = 10;
  var itopMargin = topMargin;
  var ileftMargin = leftMargin;
  var ns6 = (!document.all && document.getElementById);
  var ie4 = (document.all);
  var ns4 = (document.layers);

  var initiateOpen = 0;
  var initiateLoaded = 0;
  var infoOpen = 0;
  var infoImageLoaded = 0;
  var countTracker = 0;
  var timeElapsed; var timerTracker;

  var trackingInitalized = 0;
  var trackerLoaded = 0;

  var windowWidth = 470
  var windowHeight = 369
  var hAlign = "<?php echo($initiate_chat_halign); ?>";
  var vAlign = "<?php echo($initiate_chat_valign); ?>";
  var layerHeight = 238;
  var layerWidth = 377;
  var slideTime = 1200;

  var windowLeft = (screen.width - windowWidth) / 2;
  var windowTop = (screen.height - windowHeight) / 2;
  var size = 'height=' + windowHeight + ',width=' + windowWidth + ',top=' + windowTop + ',left=' + windowLeft;

  //Images
  trackerStatus = new Image;
  openingTrackerStatus = new Image;
  acceptTrackerStatus = new Image;
  declineTrackerStatus = new Image;

  var title = <?php if ($title != '') { echo("'" . $title . "'"); } else { echo('document.title'); } ?>;

  var referrer;


  //Script tracking and status indicator
  var _vlDomain = 0;
  var _vlAgent = 0;      
  var _vlLanguage = 'en';
  var _vlService = 1;
  var _vlTracking = new Boolean(true);
  var _vlStatus_indicator = new Boolean(true);
  var _request_initiated = new Boolean(false);
  var _custom_offline_form ="<?php echo($offline_custom_link); ?>";
  var _vlofftracking ="<?php echo($disable_off_tracking); ?>";
  var _vloffline =<?php echo($offline); ?>;
  var _vlinitiated  = 0;
  var _vldisableinvitation = 0;
  
  
    
   
function none()
{

}

        function toggle(object) {
          if (document.getElementById) {
            if (document.getElementById(object).style.visibility == 'visible')
              document.getElementById(object).style.visibility = 'hidden';
            else
              document.getElementById(object).style.visibility = 'visible';
          }
          else if (document.layers && document.layers[object] != null) {
            if (document.layers[object].visibility == 'visible' || document.layers[object].visibility == 'show' )
              document.layers[object].visibility = 'hidden';
            else
              document.layers[object].visibility = 'visible';
          }
          else if (document.all) {
            if (document.all[object].style.visibility == 'visible')
              document.all[object].style.visibility = 'hidden';
            else
              document.all[object].style.visibility = 'visible';
          }
          return false;
        }

function statusClass (s_id, _vlDomain) {
        this.s_id = s_id;

        this.checkInitiate_json = checkInitiate_json;
        this.currentTime = currentTime;
        this.toggle = toggle;
        this.initiateFloatLayer = initiateFloatLayer;
//      this.floatRefresh = floatRefresh;
        this.resetTimer = resetTimer;
        this.floatObject = floatObject;
//      this.mainPositions = mainPositions;
        this.mainTrigger = mainTrigger;
//      this.floatStart = floatStart;
        this.animate = animate;
        this.resetLayerLocation = resetLayerLocation;
        this.stopLayer = stopLayer;
        this.acceptInitiateChat = acceptInitiateChat;
        this.declineInitiateChat = declineInitiateChat;
        this.onloadEvent = onloadEvent;
        this.checkInitiate = checkInitiate;
        this.openInfo = openInfo;
        this.infoImageLoad = infoImageLoad;
        this.displayInfo = displayInfo;
        this.closeInfo = closeInfo;
        this.cancelClosingInfo = cancelClosingInfo;
        this.openLiveHelp = openLiveHelp;
        this.onlineTracker = onlineTracker;

        var server = '<?php echo($server); ?>';
        var request = '<?php echo($request_id); ?>';
        var domain = '<?php echo($cookie_domain); ?>';
        var timerClosingLiveHelpInfo;
        var timerLiveHelpInfo;
        var timerInitiateLayer;
        var idleTimeout = 90 * 60 * 1000;

        var timeStart = currentTime();
        function currentTime() {
                var date = new Date();
                return date.getTime();
        }

        if (document.referrer.indexOf('<?php echo($host); ?>') >= 0) {
                referrer = '';
        } else {
                referrer = escape(document.referrer);
        }

<?php if ($tracker_enabled == true) { ?>

// JavaScript Initiate Chat Layer Functions

				var invitationIsOpen = false;
				var invitationCloseTimer = false;
				
        function initiateFloatLayer( has_message ) {
					if ( invitationCloseTimer !== false ) { window.clearTimeout( invitationCloseTimer ); }

					if ( has_message == true ) {
						invitationCloseTimer = window.setTimeout( 's1.declineInitiateChat( true );', 60 * 1000 );
					} else {
						invitationCloseTimer = window.setTimeout( 's1.declineInitiateChat( false );', 60 * 1000 );
					}

					invitationIsOpen = true;

          var trkUrl = "";
          //var openingTrackerStatus = new Image;

          var time = currentTime();

           // new parameters (screen.width,screen.height) in order to support safari third-party cookies restriction.

          trkUrl = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=' + s_id + '&TIME=' + time + '&INITIATE=Opened&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';
          openingTrackerStatus.src = trkUrl;

          if ( ie4 )
            document.all['initiateChatResponse_' + s_id].location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Opened&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';

          if ( ns4 )
            eval("document.initiateChatResponse_" + s_id + ".location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Opened&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + ''");

          if ( ns6 )
            document.getElementById('initiateChatResponse_' + s_id).location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Opened&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';

          floatRefresh();
        }

function get_vertical_scroll( )
{
  if( window.pageYOffset )
    return window.pageYOffset;
  else if ( ( document.documentElement ) && ( document.documentElement.scrollTop ) )
    return document.documentElement.scrollTop;
  else if ( ( document.body ) && ( document.body.scrollTop ) )
    return document.body.scrollTop;
  else
    return 0;
}
        window.floatRefresh = function() {
                var trkUrl = "";
                window.clearTimeout(timerInitiateLayer);
                // window.clearTimeout(timerTracker);
                if (countTracker == 10000) {
                    var time = currentTime();
                    trkUrl = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&TIME=' + time;
                    trackerStatus.onload = resetTimer;
                    trackerStatus.src = trkUrl;
                    countTracker = 0;
                }
                else {
                        countTracker = countTracker + 10;
                        //timerInitiateLayer = window.setTimeout('mainPositions();floatRefresh();', 10);
                        //timerInitiateLayer = window.setTimeout('floatRefresh();', 10);
// document.getElementById( 'floatLayer_1' ).style.top = ( get_vertical_scroll( ) + 10 ) + "px";
// setTimeout( function(){ glue( document.getElementById( 'floatLayer_1' )); }, 10 );

                }
        }

function glue( object )
{
object.style.top = ( get_vertical_scroll( ) + 10 ) + "px";
setTimeout( function(){ glue( object ); }, 1 );
}
        window.mainPositions=function() {

          if(_vlTracking == false){
            return false;
          }


/*
          if (ns4) {
                  eval("this.currentY = document.floatLayer_" + s_id + ".top");
                  eval("this.currentX = document.floatLayer_" + s_id + ".left");
                  this.scrollTop = window.pageYOffset;
                  this.scrollLeft = window.pageXOffset;
                  mainTrigger();
          } else if(ns6) {
                  this.currentY = parseInt(document.getElementById('floatLayer_' + s_id).style.top);
                  this.currentX = parseInt(document.getElementById('floatLayer_' + s_id).style.left);
                  this.scrollTop = scrollY;
                  this.scrollLeft = scrollX;
                  mainTrigger();
          } else if(ie4) {
                  this.currentY = document.all['floatLayer_' + s_id].style.pixelTop;
                  this.currentX = document.all['floatLayer_' + s_id].style.pixelLeft;
                  this.scrollTop = document.body.scrollTop;
                  this.scrollLeft = document.body.scrollLeft;
                  mainTrigger();
          }
*/
//glue( document.getElementById( 'floatLayer_1' ) );

        }


        function resetTimer() {
                mainPositions();
                floatRefresh();
        }

        function floatObject() {
                if (ns4 || ns6) {
                        findHeight = window.innerHeight;
                        findWidth = window.innerWidth;
                } else if(ie4) {
                        findHeight = document.body.clientHeight;
                        findWidth = document.body.clientWidth;
                }
        }


        function mainTrigger() {
                var newTargetY = this.scrollTop + this.topMargin;

if((this.scrollLeft + this.leftMargin) > 400)
{
var newTargetX = this.scrollLeft + this.leftMargin -300;
}else
{
var newTargetX = this.scrollLeft + this.leftMargin;
}
                if ( this.currentY != newTargetY || this.currentX != newTargetX) {
                        if ( newTargetY != this.targetY || newTargetX != this.targetX ) {
                                this.targetY = newTargetY; this.targetX = newTargetX;
                                floatStart();
                        }
                        animate();
                }
        }

        function floatStart() {
                var now = new Date();
                this.Y = this.targetY - this.currentY;
                this.X = this.targetX - this.currentX;

                this.C = Math.PI / ( 2 * this.slideTime )
                this.D = now.getTime()
                if (Math.abs(this.Y) > this.findHeight) {
                        this.E = this.Y > 0 ? this.targetY - this.findHeight : this.targetY + this.findHeight;
                        this.Y = this.Y > 0 ? this.findHeight : -this.findHeight;
                } else {
                        this.E = this.currentY;
                }
                if (Math.abs(this.X) > this.findWidth) {
                        this.F = this.X > 0 ? this.targetX - this.findWidth : this.targetX + this.findWidth;
                        this.X = this.X > 0 ? this.findWidth : -this.findWidth;
                } else {
                        this.F = this.currentX;
                }
        }

        function animate() {
                var now = new Date() ;
                var newY = this.Y * Math.sin( this.C * ( now.getTime() - this.D ) ) + this.E;
                var newX = this.X * Math.sin( this.C * ( now.getTime() - this.D ) ) + this.F;
                newY = Math.round(newY);
                newX = Math.round(newX);
                if (( this.Y > 0 && newY > this.currentY ) || ( this.Y < 0 && newY < this.currentY )) {
                        if ( ie4 ) { document.all['floatLayer_' + s_id].style.pixelTop = newY }
                        else if ( ns4 ) { eval("document.floatLayer_" + s_id + ".top = newY"); }
                        else if ( ns6 ) { document.getElementById('floatLayer_' + s_id).style.top = newY; }
                }
                if (( this.X > 0 && newX > this.currentX ) || ( this.X < 0 && newX < this.currentX )) {
                        if ( ie4 ) { document.all['floatLayer_' + s_id].style.pixelLeft = newX; }
                        else if ( ns4 ) { eval("document.floatLayer_" + s_id + ".left = newX"); }
                        else if ( ns6 ) { document.getElementById('floatLayer_' + s_id).style.left = newX; }
                }
        }

        function resetLayerLocation() {

                var browserWidth = 0, browserHeight = 0;
                if( typeof( window.innerWidth ) == 'number' ) {
                        browserWidth = window.innerWidth;
                        browserHeight = window.innerHeight;
                } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
                        browserWidth = document.documentElement.clientWidth;
                        browserHeight = document.documentElement.clientHeight;
                } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
                        browserWidth = document.body.clientWidth;
                        browserHeight = document.body.clientHeight;
                }

                if ((ns4) || (ns6)) {
                        if (hAlign == "left") { leftMargin = ileftMargin; }
                        if (hAlign == "right") { leftMargin = window.innerWidth-ileftMargin-layerWidth-20; }
                        if (hAlign == "center") { leftMargin = Math.round((window.innerWidth-20)/2)-Math.round(layerWidth/2); }
                        if (vAlign == "top") { topMargin = itopMargin; }
                        if (vAlign == "bottom") { topMargin = window.innerHeight-itopMargin-layerHeight; }
                        if (vAlign == "center") { topMargin = Math.round((window.innerHeight-20)/2)-Math.round(layerHeight/2); }
                }
                if (ie4) {
                        if (hAlign == "left") { leftMargin = ileftMargin; }
                        if (hAlign == "right") { leftMargin = document.body.offsetWidth-ileftMargin-layerWidth-20; }
                        if (hAlign == "center") { leftMargin = Math.round((document.body.offsetWidth-20)/2)-Math.round(layerWidth/2); }
                        if (vAlign == "top") { topMargin = itopMargin; }
                        if (vAlign == "bottom") { topMargin = browserHeight-itopMargin-layerHeight; }
                        if (vAlign == "center") { topMargin = Math.round((browserHeight-20)/2)-Math.round(layerHeight/2); }
                }

        }

        function stopLayer() {
                window.clearTimeout(timerInitiateLayer);
                if ( ns4 ) {
                        eval("document.floatLayer_" + s_id + ".top = '10'");
                        eval("document.floatLayer_" + s_id + ".left = '10'");
                } else if ( ns6 ) {
                        document.getElementById('floatLayer_' + s_id).style.top = "10"; document.getElementById('floatLayer_' + s_id).style.left = "10";
                } else if ( ie4 ) {
                        document.all['floatLayer_' + s_id].style.pixelTop = "10px"; document.all['floatLayer_' + s_id].style.pixelLeft = "10px";
                }
        }

        function acceptInitiateChat( has_message ) {
						if ( invitationCloseTimer !== false ) { window.clearTimeout( invitationCloseTimer ); }

                var trkUrl = "";
                //var acceptTrackerStatus = new Image;
                var time = currentTime();
                trkUrl = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Accepted&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';
                acceptTrackerStatus.src = trkUrl;

                if ( ie4 )document.all['initiateChatResponse_' + s_id].location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Accepted&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';
                if ( ns4 )eval("document.initiateChatResponse_" + s_id + ".location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Accepted&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + ''");
                if ( ns6 )document.getElementById('initiateChatResponse_' + s_id).location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Accepted&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';

                if (initiateOpen == 1) {
									if  ( has_message == true ) {
											toggle('floatLayer_message_' + s_id);
									} else {
											toggle('floatLayer_' + s_id);
									}
                }
                initiateLoaded = 0;
                stopLayer();
                onlineTracker();
        }

        function declineInitiateChat( has_message ) {
					if ( invitationCloseTimer !== false ) { window.clearTimeout( invitationCloseTimer ); }

					var trkUrl = "";
          //var declineTrackerStatus = new Image;
          var time = currentTime();
                                 

           trkUrl = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Declined&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';
          declineTrackerStatus.src = trkUrl;

          if ( ie4 )document.all['initiateChatResponse_' + s_id].location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Declined&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';
          if ( ns4 )eval("document.initiateChatResponse_" + s_id + ".location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Declined&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + ''");
          if ( ns6 )document.getElementById('initiateChatResponse_' + s_id).location = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&INITIATE=Declined&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '';

          if (initiateOpen == 1) {
							if ( has_message == true )
                  toggle('floatLayer_message_' + s_id);
								else
								 toggle('floatLayer_' + s_id);
               initiateOpen = 0;
          }
          initiateLoaded = 0;
          stopLayer();
          // onlineTracker();
					
					invitationIsOpen = false;
        }

        window.onload = onloadEvent;
        window.onresize = resetLayerLocation;

        function onloadEvent() {
                resetLayerLocation();
                mainPositions();
        }

<?php } ?>

//-->
<?php
        $num_support_available_users = 0;
        $num_support_hidden_users = 0;
        $num_support_online_users = 0;
        $num_support_away_users = 0;
        $num_support_brb_users = 0;

        // Counts the total number of support users within each Online/Offline/BRB/Away status mode
        $query = "SELECT DISTINCT `status`, count(`id`) FROM " . $table_prefix . "users WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`refresh`)) < '$connection_timeout' and answers= '1' ";
        if($department != '' && $departments) 
        { $query .= " and `department` LIKE '%$department%'"; }
        
        
        $query .= " GROUP BY `status`";
        $rows = $SQL->selectall($query);
        if(is_array($rows)) {
                foreach ($rows as $key => $row) {
                        if (is_array($row)) {
                                switch ($row['status']) {
                                        case 0: // Offline - Hidden
                                           $num_support_hidden_users = $row['count(`id`)'];
                                           break;
                                        case 1: // Online
                                           $num_support_online_users = $row['count(`id`)'];
                                           break;
                                        case 2: // Be Right Back
                                           $num_support_brb_users = $row['count(`id`)'];
                                           break;
                                        case 3: // Away
                                           $num_support_away_users = $row['count(`id`)'];
                                           break;
                                }
                        }
                }
        }

$num_support_available_users = $num_support_online_users + $num_support_away_users + $num_support_brb_users;
$initiate_request_flag = 0;

$query = "SELECT `initiate` FROM " . $table_prefix . "requests WHERE `id` = '$request_id'";
$row = $SQL->selectquery($query);
if (is_array($row)) {
        // Only allow to show the invitation one time
        $initiate_request_flag = $row['initiate'];
        
    
}

// settings new


$invitation_name ="initiate_dialog.gif";

$query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='chat_invitation_img'";

$row = $SQL->selectquery($query);
if (is_array($row)) {
        $invitation_name = $row['value'];
}


// If Initiate Chat Request has occured the open the Live Help chat window and auto-login
if ($initiate_request_flag > 0 || $initiate_request_flag == -1) {

        // Update request flag to show that the guest uesr OPENED the Online Chat Request
        $query = "UPDATE " . $table_prefix . "requests SET `initiate` = '-1' WHERE `id` = '$request_id'";
        $SQL->miscquery($query);


?>
<!--

        initiateLoaded = 1;

//-->

<?php
}
?>

<!--

function checkInitiate_json(data) {
      var text = data.text;
      if ( typeof( text ) == "undefined" ) {
        text = '';
      }
      
      document.getElementById('floatLayer_message_text_' + s_id).innerHTML = text;
      
      initiateFloatLayer( true );
      toggle('floatLayer_message_' + s_id);
      initiateOpen = 1;
  }

  function checkInitiate() {
    // Check if site visitor has an Initiate Chat Request Pending for display...
    var imageWidth = this.width; var imageHeight = this.height;
	
	// Proactive message.
    if ( ( ( ( imageWidth == 2 && imageHeight == 2 ) || ( imageWidth == 3 && imageHeight == 3 ) ) && initiateOpen == 0) || initiateLoaded == 1 ) {
		if ( ! invitationIsOpen ) {
				if ( imageWidth == 3 && imageHeight == 3 ) {
				
			 if ( typeof( jQuery ) == "undefined" ) {

				  var time = currentTime();
				  var ajax_request_url = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?json&status_id=' + s_id + '&TIME=' + time + '&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&USERID=<?php echo $_REQUEST['USERID']; ?>&services=<?php echo $_REQUEST['services']; ?>'+ '&DOMAINID=' + _vlDomain + '&LANGUAGE=' + _vlLanguage + '&SERVICE=' + _vlService + '&GET_INVITATION_MESSAGE=1&callback=?';
			  
				  var head= document.getElementsByTagName('head')[0];
				  var script= document.createElement('script');
				  script.type= 'text/javascript';
				  script.src= ajax_request_url;
				  head.appendChild(script);
			  
				/*
				  var ajax_request = new XMLHttpRequest();
				  ajax_request.onreadystatechange = function() {
				if ( ajax_request.readyState == 4 && ajax_request.status == 200 ) {
				  var text = ajax_request.responseText;
				  if ( typeof( text ) == "undefined" ) {
						text = '';
					  }
					  
					  document.getElementById('floatLayer_message_text_' + s_id).innerHTML = text;
					  
					  initiateFloatLayer( true );
					  toggle('floatLayer_message_' + s_id);
					  initiateOpen = 1;
					}
				  };
						var time = currentTime();
						var ajax_request_url = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?json&status_id=' + s_id + '&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&TIME=' + time + '&USERID=<?php echo $_REQUEST['USERID']; ?>&services=<?php echo $_REQUEST['services']; ?>'+ '&DOMAINID=' + _vlDomain + '&LANGUAGE=' + _vlLanguage + '&SERVICE=' + _vlService + '&GET_INVITATION_MESSAGE=1&callback=?';					

						ajax_request.open( "GET", ajax_request_url, true );
						ajax_request.send( null );
					*/
			
			 }
			  else {
			  
				  var time = currentTime();
				  var ajax_request_url = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?json&status_id=' + s_id + '&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&TIME=' + time + '&USERID=<?php echo $_REQUEST['USERID']; ?>&services=<?php echo $_REQUEST['services']; ?>'+ '&DOMAINID=' + _vlDomain + '&LANGUAGE=' + _vlLanguage + '&SERVICE=' + _vlService + '&GET_INVITATION_MESSAGE=1&callback=?';
				  
				  jQuery.getJSON(ajax_request_url); // the result is going to call checkInitiate_json(data)
				
			   }     
				
			   
			}
			else
			{
				initiateFloatLayer( false );
				toggle('floatLayer_' + s_id);
				initiateOpen = 1;
			}
			    
			   
			  // We play the sound in proactive message and invitation.
			  if ("<?php echo $sound_alert_new_pro_msg; ?>" != 0 && typeof( Audio ) != "undefined" )
			  {

				 var snd = new Audio();

				 if(!!(snd.canPlayType && snd.canPlayType('audio/ogg; codecs="vorbis"').replace(/no/, '')))
				   snd.src = "<?php echo($server); ?><?php echo $install_directory; ?>/sounds/alert.ogg";
				 else if(!!(snd.canPlayType && snd.canPlayType('audio/mpeg;').replace(/no/, '')))
				   snd.src = "<?php echo($server); ?><?php echo $install_directory; ?>/sounds/alert.mp3";
				 else if(!!(snd.canPlayType && snd.canPlayType('audio/mp4; codecs="mp4a.40.2"').replace(/no/, '')))
				   snd.src = "<?php echo($server); ?><?php echo $install_directory; ?>/sounds/alert.m4a";
				 else
				   snd.src = "<?php echo($server); ?><?php echo $install_directory; ?>/sounds/alert.wav";
					
				 snd.play();
			  }

		}
    }
  }

<?php

//  If popup help is not disabled then
if ($disable_popup_help == false) {

?>
        function openInfo(statusImage, e) {

          return false;

          if(_vlTracking == false){
            return false;
          }
                window.clearTimeout(timerLiveHelpInfo);
                cancelClosingInfo();

                var iLayerX = document['LiveHelpInfoContent_' + s_id].width;
                var iLayerY = document['LiveHelpInfoContent_' + s_id].height;
                var iPosX = statusImage.offsetLeft;
                var iPosY = statusImage.offsetTop;

                var iWidth = statusImage.clientWidth;
                var iHeight = statusImage.clientHeight;
                if (!iWidth) {
                        iWidth = statusImage.offsetWidth;
                }
                obj = statusImage.offsetParent;
                while(obj != null){
                        iPosX += obj.offsetLeft;
                        iPosY += obj.offsetTop;
                        obj = obj.offsetParent;
                }

                var iCurrentY; var iCurrentX; var iScrollTop; var iScrollLeft; var iFindHeight; var iFindWidth;

                if (ns4) {
                        iScrollTop = window.pageYOffset;
                        iScrollLeft = window.pageXOffset;
                } else if(ns6) {
                        iScrollTop = scrollY;
                        iScrollLeft = scrollX;
                } else if(ie4) {
                        iScrollTop = document.body.scrollTop;
                        iScrollLeft = document.body.scrollLeft;
                        iFindHeight = document.body.clientHeight;
                        iFindWidth = document.body.clientWidth;
                }

                if (ns4 || ns6) {
                        iFindHeight = window.innerHeight; iFindWidth = window.innerWidth;
                }

                infoImage = new Image();
                infoImage.onload = infoImageLoad;

                var iMarginHeight = iFindHeight - (iHeight + iPosY - iScrollTop);
                var iMarginWidth = iFindWidth - (iWidth + iPosX - iScrollLeft);

                if (iMarginHeight < iLayerY && iPosY > iLayerY) {

                        if (ie4) {
                                document.all['LiveHelpInfo_' + s_id].style.background = 'url(' + infoImage.src + ')';
                        } else if (ns4) {
                                eval("document.LiveHelpInfo_" + s_id + ".background = 'url(' + infoImage.src + ')'");
                        } else if(ns6) {
                                document.getElementById('LiveHelpInfo_' + s_id).style.background = 'url(' + infoImage.src + ')';
                        }
                        iNewX = iPosX - 15;
                        iNewY = iPosY + 20 - iLayerY - 20;
                }
                else {

                        if (ie4) {
                                document.all['LiveHelpInfo_' + s_id].style.background = 'url(' + infoImage.src + ')';
                        } else if (ns4) {
                                eval("document.LiveHelpInfo_" + s_id + ".background = 'url(' + infoImage.src + ')'");
                        } else if(ns6) {
                                document.getElementById('LiveHelpInfo_' + s_id).style.background = 'url(' + infoImage.src + ')';
                        }
                        iNewX = iPosX + 15;
                        iNewY = iPosY + 20 + statusImage.height - 20;
                }

                if (iMarginWidth < iLayerX && iPosX > iLayerX) {
                        iNewX = iPosX - iLayerX + 175;
                }
                else if (iMarginWidth > iLayerX && iPosX < iLayerX) {
                        iNewX = iPosX + 25;
                }

                if (ie4) {
                        document.all['LiveHelpInfo_' + s_id].style.pixelTop = iNewY;
                        document.all['LiveHelpInfo_' + s_id].style.pixelLeft = iNewX;
                } else if (ns4) {
                        eval("document.LiveHelpInfo_" + s_id + ".top = iNewY");
                        eval("document.LiveHelpInfo_" + s_id + ".left = iNewX");
                } else if(ns6) {
                        document.getElementById('LiveHelpInfo_' + s_id).style.top = iNewY + "px";
                        document.getElementById('LiveHelpInfo_' + s_id).style.left = iNewX + "px";
                }
                if (infoImageLoaded == 1) {
                        displayInfo();
                }
        }

        function infoImageLoad() {
                infoImageLoaded = 1;
        }

        //TODO - Improve the method for displayInfo and closeInfo because this method is not using s_id directly
        function displayInfo() {
                if (infoOpen == 0) {
                        timerLiveHelpInfo = window.setTimeout("toggle('LiveHelpInfo_1'); infoOpen = 1;", 2000);
                }
        }

        function closeInfo() {
          return false;
                window.clearTimeout(timerLiveHelpInfo);
                if (infoOpen == 1) {
                        timerClosingLiveHelpInfo = window.setTimeout("toggle('LiveHelpInfo_1'); infoOpen = 0;", 500);
                }
        }

        function cancelClosingInfo() {
                window.clearTimeout(timerClosingLiveHelpInfo);
        }
<?php
}
else {
?>
        function openInfo(statusImage, e) {
                return false;
        }

        function infoImageLoad() {
                return false;
        }

        function displayInfo() {
                return false;
        }

        function closeInfo() {
                return false;
        }

        function cancelClosingInfo() {
                return false;
        }
<?php
}
?>

        function openLiveHelp() {
<?php

 
// If Admin Users are Offline/Hidden and Offline Email is disabled
if (($num_support_available_users == $num_support_hidden_users || $num_support_available_users == 0) && $disable_offline_email == true) {
       
?>
                return false;
<?php
}
?>
 
   
                switch(s_id) {
                        case 1 : {
                                var winLiveHelp = window.open('<?php echo($server); ?><?php echo $install_directory; ?>/index.php?' + 'DOMAINID=' + _vlDomain + '&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&AGENTID=' + _vlAgent + '&URL=' + document.location<?php if ($department != '') { echo(" + '&DEPARTMENT=" . $department . "' "); } ?> + '&TITLE=' + title + '&SERVER=<?php echo($server); ?>&COOKIE=<?php echo($cookie_domain); ?>&LANGUAGE=' + _vlLanguage + '&CHARSET=<?php echo(CHARSET); ?>', 'SUPPORTER' + s_id + '_<?php echo $domain_id; ?>', size)
                                break
                        }
                        case 4 : {
                                var winLiveHelp = window.open('<?php echo($server); ?><?php echo $install_directory; ?>/webc_form.php?URL=' + document.location<?php if ($department != '') { echo(" + '&DEPARTMENT=" . $department . "' "); } ?> + '&TITLE=' + title + '&SERVER=<?php echo($server); ?>&COOKIE=<?php echo($cookie_domain); ?>&LANGUAGE=<?php echo(LANGUAGE_TYPE); ?>&CHARSET=<?php echo(CHARSET); ?>', 'SUPPORTER' + s_id + '_<?php echo $domain_id; ?>', size)
                                break
                        }
                }
                if (winLiveHelp) { winLiveHelp.opener = self; }
        }

        function onlineTracker() {
                var trkUrl = "";
                var time = currentTime();
<?php
// If the Online Tracker is Enabled and there is Admin Users Online/Hidden/BRB then... start JavaScript timer
//if ($tracker_enabled == true && ($num_support_available_users > 0 || $num_support_hidden_users > 0) ) {
if ($tracker_enabled == true) {
?>

                if ( ie4 ) {
                        if (document.all['initiateChatResponse_' + s_id]) { trackerLoaded = 1; }
                } else if ( ns4 ) {
                        if(eval("document.initiateChatResponse_" + s_id)) { trackerLoaded = 1; }
                } else if ( ns6 ) {
                        if(document.getElementById('initiateChatResponse_' + s_id)) { trackerLoaded = 1; }
                }
                if (trackingInitalized == 0)
                {
                        //-- Geo Location data
                        trkUrl = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=' + s_id + '&TIME='+ time + '&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&TITLE=' + title + '&URL=' + document.location + '&REFERRER=' + referrer + '&COOKIE=<?php echo($cookie_domain); ?>&USERID=<?php echo $_REQUEST['USERID']; ?>&services=<?php echo $_REQUEST['services']; ?>' + '&LANGUAGE=' + _vlLanguage + '&DOMAINID=' + _vlDomain + '&AGENTID=' + _vlAgent + '&SERVICE=' + _vlService + '&region=' + region + '&country=' + country + '&city=' + city + '&countrycode=' + countrycode + '&latitude=' + latitude + '&longitude=' + longitude;
                        trackerStatus.src = trkUrl;
                        trackingInitalized = 1;
                }
                else {
                  if (trackerLoaded == 1) {
                    trkUrl = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=' + s_id + '&TIME=' + time + '&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&USERID=<?php echo $_REQUEST['USERID']; ?>&services=<?php echo $_REQUEST['services']; ?>'+ '&DOMAINID=' + _vlDomain + '&AGENTID=' + _vlAgent + '&LANGUAGE=' + _vlLanguage + '&SERVICE=' + _vlService;
                    trackerStatus.onload = checkInitiate;
                    trackerStatus.src = trkUrl;
                  }
              }

                // If the Site Visitor has been Idle for under the given idleTimeout then run the tracker
                timeElapsed = time - timeStart;
                if ((timeElapsed < idleTimeout) && (_vlTracking == true)) {
                        window.clearTimeout(timerTracker);
                        timerTracker = window.setTimeout('s' + s_id + '.onlineTracker();', 10000);
                }
<?php
}
else {
?>
        trkUrl = '<?php echo($server); ?><?php echo $install_directory; ?>/import/tracker.php?status_id=s_id&TIME=' + time + '&TITLE=' + title + '&URL=' + document.location + '&REFERRER=' + referrer + '&WIDTH=' + screen.width + '&HEIGHT=' + screen.height + '&COOKIE=<?php echo($cookie_domain); ?>&USERID=<?php echo $_REQUEST['USERID']; ?>&DOMAINID=<?php echo $_REQUEST['DOMAINID']; ?>&services=<?php echo $_REQUEST['services']; ?>';
        trackerStatus.src = trkUrl;
<?php
}
?>
        }

        onlineTracker();
}

// Disable invitation

<?php
  
   $query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='disable_invitation'";

    $row = $SQL->selectquery($query);
    if (is_array($row)) {
        $disable_invitation = $row['value'];
        }  

?>

// Invitation Refresh

<?php
  
  $time_refresh =0;
  
   $query = "SELECT `value` FROM " . $table_prefix . "settings WHERE `id_domain`= '$domain_id' and name ='invitation_refresh'";

    $row = $SQL->selectquery($query);
    if (is_array($row)) {
        $time_refresh = $row['value'];
        }  

 $time_refresh  = $time_refresh  * 1000 ;

?>

<?php 

// Not allowed Conuntries
  $not_allowed_country = 0;
   
  $query = "SELECT jlnac.`id_domain`  FROM " . $table_prefix . "requests jlr , " . $table_prefix ."not_allowed_countries jlnac WHERE jlr.`id` = '$request_id' and jlr.id_domain = '$domain_id' and jlr.id_domain = jlnac.id_domain and  jlr.country_code =  jlnac.code"; 
  $row = $SQL->selectquery($query);
if (is_array($row)) {
   $not_allowed_country = 1;
   }
                   
                         
?>            

<?php if (($time_refresh !=0) and ($disable_invitation !=1) and ($initiate_request_flag !=-3)) {  ?>

if ( _vloffline !=1 && _vlinitiated !=1) {
document.writeln('<script type="text/javascript">');
document.writeln('function showInvitation() {');
document.writeln('var isOffline = document.getElementById("LiveHelpStatus_1").height;');
document.writeln('if(isOffline != 89){');
document.writeln('_vlinitiated = 1;');
document.writeln('imageWidth = 2;');
document.writeln('imageHeight = 2;');
document.writeln('initiateOpen = 0;');
document.writeln('initiateLoaded = 1;  }  }');
document.writeln('setTimeout( function(){ showInvitation(); }, <?php echo($time_refresh); ?> );');
document.writeln('</script>');
}

<?php }  ?>

function startLivehelp()
{

//  loadGeoLocation();
  document.writeln('<script>');
  document.writeln('var s1 = new statusClass(' + _vlService  + ',' + _vlDomain + ');');
  document.writeln('</script>');

 _vlnot_allowed_country="<?php echo($not_allowed_country); ?>";

 // Insert invitation script
 
  _vldisableinvitation =<?php echo($disable_invitation); ?>;

   if( _vlTracking &&  _vldisableinvitation !=1 ){
	  
		// 377 - 238
		// 75,146,155,169
 	    // 170,146,245,169

    html = '<map name="LiveHelpInitiateChatMap_1">';
    html += '<area id="areaAccept" shape="rect" coords="52,130,132,153" href="javascript:none();" onClick="s1.openLiveHelp();s1.acceptInitiateChat( false );" alt="Accept"/>';
    html += '<area id="areaDecline"  shape="rect" coords="147,130,222,153" href="javascript:none();" onClick="s1.declineInitiateChat( false );" alt="Decline"/>';
    html += '</map>';

	<?php if ( $invitation_position == 'left' ) : ?>
		html += '<div id="floatLayer_1" style="position:fixed; left: 0 !important; top: auto !important; visibility: hidden; right: auto !important; bottom: 0 !important; z-index:2147483647;">'; // Max z-index value.
	<?php elseif ( $invitation_position == 'right' ) : ?>
		html += '<div id="floatLayer_1" style="position:fixed; left: auto !important; top: auto !important; visibility: hidden; right: 0 !important; bottom: 0 !important; z-index:2147483647;">'; // Max z-index value.
	<?php elseif ( $invitation_position == 'center' ) : ?>
		html += '<div id="floatLayer_1" style="position:fixed; left: 0 !important; top: auto !important; visibility: hidden; right: 0 !important; bottom: 0 !important; z-index:2147483647;">'; // Max z-index value.
		html += '<div style="position: relative; left: 50%; float: left;"><div style="position: relative; left: -50%; float: left;">';
	<?php endif; ?>
	
	
	
		html += '<div align="center"><img id="initiateDialog" src="<?php echo($server); echo($server_directory); ?>/<?php echo($eserverName); ?>/domains/'+_vlDomain +'/i18n/'+ _vlLanguage + '/pictures/<?php echo($invitation_name); ?>?v2" alt="<?php echo($server); ?> Platform" width="277" height="164" border="0" usemap="#LiveHelpInitiateChatMap_1"/></div>';
		
		
		
		<?php if ( $invitation_position == 'center' ) : ?>
			html += '</div></div>';
		<?php endif; ?>
		
    html += '</div>';

	<?php if ( $invitation_position == 'left' ) : ?>
		html += '<div id="floatLayer_message_1" style="position: fixed; left: 0 !important; top: auto !important; visibility: hidden; right: auto !important; bottom: 0 !important; z-index: 2147483647;">'; // Max z-index value.
	<?php elseif ( $invitation_position == 'right' ) : ?>
		html += '<div id="floatLayer_message_1" style="position: fixed; left: auto !important; top: auto !important; visibility: hidden; right: 0 !important; bottom: 0 !important; z-index: 2147483647;">'; // Max z-index value.
	<?php elseif ( $invitation_position == 'center' ) : /* center */ ?>
		html += '<div id="floatLayer_message_1" style="position: fixed; left: 0 !important; top: auto !important; visibility: hidden; right: 0 !important; bottom: 0 !important; z-index: 2147483647;">'; // Max z-index value.
		
 		html += '<div style="position: relative; left: 50%; float: left;"><div style="position: relative; left: -50%; float: left;">'; 
		
	<?php endif; ?> 
	
	
		html += '<div align="center" style="background: url(\'<?php echo($server); echo($server_directory); ?>/<?php echo($eserverName); ?>/pictures/message-box.gif\') no-repeat top left; width: 230px; height: 101px; overflow: hidden;"><div style="height: 23px;"><a href="javascript:;" onclick="s1.declineInitiateChat( true );" style="display: block; float: right; height: 23px; width: 23px;"></a><a href="javascript:;" onclick="s1.openLiveHelp();s1.acceptInitiateChat( true );" style="display: block; float: right; height: 23px; width: 23px;"></a></div><div onclick="s1.openLiveHelp();s1.acceptInitiateChat( true );" id="floatLayer_message_text_1" style="font-size: 11px !important; line-height: normal !important; font-family: Verdana !important; cursor: pointer; text-align: center; padding: 18px 10px 5px 10px;"></div></div>';
			
			
	<?php if ( $invitation_position == 'center' ) : ?>
		 html += '</div></div>'; 
	<?php endif; ?>
			
		html += '</div>';

		var div = document.createElement( 'div' );
		div.innerHTML = html;
        

	document.body.insertBefore( div, document.body.firstChild );
   
    document.writeln('<div iframe name="initiateChatResponse_1" id="initiateChatResponse_1" src="<?php echo($server); echo($server_directory); ?>/<?php echo($eserverName); ?>/blank.php?&LANGUAGE=' + _vlLanguage + ' frameborder="0" width="1" height="1" style="visibility: hidden; border-style:none"></iframe>');           
    document.writeln('</div>');
                                                        
   }

// Custom offline form
 var _vlExternalLink = 0;

  
// not allowed conuntries restriction   
if (_vlnot_allowed_country ==0) { 
     
  // Offline for custm form 
  if ( _vlStatus_indicator  &&  _vloffline ==1 &&_custom_offline_form !='') {
      document.writeln('<a href="<?php echo($offline_custom_link);?>" id="livechatLink">');
      document.writeln('<img src="<?php echo($server); echo($server_directory); ?>/<?php echo($eserverName); ?>/import/status.php?service_id=' + _vlService + '&LANGUAGE=' + _vlLanguage + '&DOMAINID=' + _vlDomain + '" id="LiveHelpStatus_1" name="LiveHelpStatus_1" border="0" onmouseover="s1.openInfo(this, event);" onmouseout="s1.closeInfo();"/></a>');
      _vlExternalLink =1;
   }

// Regular chat option for domain
   if(_vlStatus_indicator  && _vlExternalLink ==0 && _vlofftracking ==0 && _vlAgent ==0){
     document.writeln('<a href="<?php echo($server);  echo($server_directory); ?>/<?php echo($eserverName); ?>/index.php" id="livechatLink" target="_blank" onclick="s1.openLiveHelp(); s1.closeInfo(); return false">');
     document.writeln('<img src="<?php echo($server); echo($server_directory); ?>/<?php echo($eserverName); ?>/import/status.php?service_id=' + _vlService + '&LANGUAGE=' + _vlLanguage + '&DOMAINID=' + _vlDomain + '" id="LiveHelpStatus_1" name="LiveHelpStatus_1" border="0" onmouseover="s1.openInfo(this, event);" onmouseout="s1.closeInfo();"/></a>');
   }
 else
    if(_vlStatus_indicator  && _vlExternalLink ==0 && _vlofftracking ==0 && _vlAgent !=0){     
      document.writeln('<a href="<?php echo($server);  echo($server_directory); ?>/<?php echo($eserverName); ?>/index.php" id="livechatLink" target="_blank" onclick="s1.openLiveHelp(); s1.closeInfo(); return false">');
      document.writeln('<img src="<?php echo($server); echo($server_directory); ?>/<?php echo($eserverName); ?>/import/status.php?service_id=' + _vlService + '&LANGUAGE=' + _vlLanguage + '&DOMAINID=' + _vlDomain + '&AGENTID=' + _vlAgent +'" id="LiveHelpStatus_1" name="LiveHelpStatus_1" border="0" onmouseover="s1.openInfo(this, event);" onmouseout="s1.closeInfo();"/></a>');     
   } 
  }
 }

//-->
<?php
}

?>