<?php
	echo "test good...";

$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$url = str_replace("testing.php", "wp-login.php", $url);

$ch = curl_init();  
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$text = curl_exec($ch); 
curl_close($ch);

	sleep(1);
	@unlink("testing.php");
	exit;
?>