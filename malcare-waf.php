<?php
// Please validate auto_prepend_file setting before removing this file

if (file_exists('/home/site/wwwroot/wp-content/plugins/malcare-security/protect/prepend/ignitor.php')) {
	define("MCDATAPATH", '/home/site/wwwroot/wp-content/mc_data/');
	define("MCCONFKEY", '5e1a8317723ecf65b486f3a0d1da48ba');
	include_once('/home/site/wwwroot/wp-content/plugins/malcare-security/protect/prepend/ignitor.php');
}
?>
