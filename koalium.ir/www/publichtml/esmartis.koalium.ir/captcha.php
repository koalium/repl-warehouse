<?php
// URL: http://smartis.koalium.ir/captcha.php
session_start();

// Generate random CAPTCHA code
$captchaCode = substr(md5(rand()), 0, 6);
$_SESSION['captcha_code'] = $captchaCode;

// Create CAPTCHA image
$image = imagecreate(100, 30);
$bgColor = imagecolorallocate($image, 255, 255, 255);
$textColor = imagecolorallocate($image, 0, 0, 0);
imagestring($image, 5, 10, 5, $captchaCode, $textColor);

// Output the image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>