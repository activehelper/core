<?php
include_once('constants.php');

$crawler = false;

$spiders[0] = "Googlebot";
$spiders[1] = "Slurp";
$spiders[2] = "Scooter";
$spiders[3] = "Openbot";
$spiders[4] = "Mercator";
$spiders[5] = "AltaVista";
$spiders[6] = "AnzwersCrawl";
$spiders[7] = "FAST-WebCrawler";
$spiders[8] = "Gulliver";
$spiders[9] = "WISEnut";
$spiders[10] = "InfoSeek";
$spiders[11] = "Lycos_Spider";
$spiders[12] = "HenrytheMiragoRobot";
$spiders[13] = "IncyWincy";
$spiders[14] = "MantraAgent";
$spiders[15] = "MegaSheep";
$spiders[16] = "Robozilla";
$spiders[17] = "Scrubby";
$spiders[18] = "Speedy_Spider";
$spiders[19] = "Sqworm";
$spiders[20] = "teoma";
$spiders[21] = "Ultraseek";
$spiders[22] = "whatUseek";
$spiders[23] = "Jeeves";
$spiders[24] = "AllTheWeb";
$spiders[25] = "Google";
$spiders[26] = "ia_archiver";
$spiders[27] = "grub-client";
$spiders[28] = "ZyBorg";
$spiders[29] = "Atomz";
$spiders[30] = "ArchitextSpider";
$spiders[31] = "Arachnoidea";
$spiders[32] = "UltraSeek";
$spiders[33] = "MSNBOT";
$spiders[34] = "YahooSeeker";

foreach($spiders as $key => $spider) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], $spider) !== false) {
                $crawler = true;
                break;
        }
}


if ($crawler == true) {
        header("HTTP/1.0 404 Not Found"); 
        exit();
}
?>
