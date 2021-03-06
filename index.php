<?php
$sConfigFile = 'conf/production/config-itop.php';
$sStartPage = './pages/UI.php';
$sSetupPage = './setup/index.php';

//
// Maintenance mode
//
define('APPROOT', dirname(__FILE__).'/');
define('MAINTENANCE_MODE_FILE', APPROOT.'data/.maintenance');

// Use 'maintenance' parameter to bypass maintenance mode
if (!isset($bBypassMaintenance))
{
	$bBypassMaintenance = isset($_REQUEST['maintenance']) ? boolval($_REQUEST['maintenance']) : false;
}

if (file_exists(MAINTENANCE_MODE_FILE) && !$bBypassMaintenance)
{
	http_response_code(503);
	echo 'This application is currently under maintenance';
	exit();
}

/**
 * helper to test if a string ends with another
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function EndsWith($haystack, $needle) {
	return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}


/**
 * Check that the configuration file exists and has the appropriate access rights
 * If the file does not exist, launch the configuration wizard to create it
 */  
if (file_exists(dirname(__FILE__).'/'.$sConfigFile))
{
	if (!is_readable($sConfigFile))
	{
		echo "<p><b>Error</b>: Unable to read the configuration file: '$sConfigFile'. Please check the access rights on this file.</p>";
	}
	else if (is_writable($sConfigFile))
	{
		echo "<p><b>Security Warning</b>: the configuration file '$sConfigFile' should be read-only.</p>";
		echo "<p>Please modify the access rights to this file.</p>";
		echo "<p>Click <a href=\"$sStartPage\">here</a> to ignore this warning and continue to run iTop.</p>";
	}
	else
	{
		header("Location: $sStartPage");
	}
}
else
{
	// Config file does not exist, need to run the setup wizard to create it
	header("Location: $sSetupPage");
}
