<?php
include_once('import/constants.php');
include('import/string_util.php');

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
$domain_id = (int) $domain_id;
$installed = false;
$database = include('import/config_database.php');
if ($database) {
        include('import/block_spiders.php');
        include('import/class.mysql.php');
        $installed = include('import/config.php');

} else {
        $installed = false;
}

if ($installed == false) {
        include('import/settings_default.php');
}

if ((function_exists('imagepng') || function_exists('imagejpeg')) && function_exists('imagettftext')) {

        // Generate the random string
        $chars = array('a','A','b','B','c','C','d','D','e','E','f','F','g','G','h','H','i','j','J','k','K','L','m','M','n','N','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w','W','x','X','y','Y','z','Z','2','3','4','5','6','7','8','9');
        $ascii = array();

        $security = '';
        for ($i = 0; $i < 5; $i++) {
                $char = $chars[rand(0, count($chars) - 1)];
                $ascii[$i] = ord($char);
                $security .= $char;
        }

        $session = array();
        $session['REQUEST'] = $request_id;
        $session['DOMAINID'] = $domain_id;
        $session['SECURITY'] = md5(strtoupper($security));
        $session['LANGUAGE'] = LANGUAGE_TYPE;
        $session['CHARSET'] = CHARSET;
        $data = serialize($session);

        setCookie($cookieName, $data, false, '/', $cookie_domain, $ssl);

        function hex2rgb($hex) {
                $color = str_replace('#','',$hex);
                $rgb = array(hexdec(substr($color,0,2)), hexdec(substr($color,2,2)), hexdec(substr($color,4,2)));
                return $rgb;
        }


      //  $rgb = hex2rgb($background_color);
        $rgb = hex2rgb('#F9F9F9');
        $image = imagecreate(80, 30); /* Create a blank JPEG image */
        $bg = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($image, 0, 0, 80, 30, $bg);

        // Create random angle
        $size = 18;
        $angle = rand(-5, -3);
        $color = imagecolorallocate($image, 0, 0, 0);
        $font = $install_path . $install_directory . '/style/fonts/FrancophilSans.ttf';

        // Determine text size, and use dimensions to generate x & y coordinates
        $textsize = imagettfbbox($size, $angle, $font, $security);
        $twidth = abs($textsize[2] - $textsize[0]);
        $theight = abs($textsize[5] - $textsize[3]);
        $x = (imagesx($image) / 2) - ($twidth / 2);
        $y = (imagesy($image)) - ($theight / 2);

        // Add text to image
        imagettftext($image, $size, $angle, $x, $y, $color, $font, $security);

        if (function_exists('imagepng')) {
                // Output GIF Image
                header('Content-Type: image/png');
                imagepng($image);
        }
        elseif (function_exists('imagejpeg')) {
                // Output JPEG Image
                header('Content-Type: image/jpeg');
                imagejpeg($image, '', 100);
        }

        // Destroy the image to free memory
        imagedestroy($image);
        exit();

}
else {

        if (strpos(php_sapi_name(), 'cgi') === false ) { header('HTTP/1.0 404 Not Found'); } else { header('Status: 404 Not Found'); }
        exit;

}

?>
