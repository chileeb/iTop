<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;

require_once APPROOT.'/lib/composer-vendor/autoload.php';

// Load current environment if necessary
if(!defined('MODULESROOT'))
{
	if (file_exists(__DIR__ . '/../../../../approot.inc.php'))
	{
		require_once __DIR__ . '/../../../../approot.inc.php';   // When in env-xxxx folder
	}
	else
	{
		require_once __DIR__ . '/../../../../../approot.inc.php';   // When in datamodels/x.x folder
	}
	require_once APPROOT . '/application/startup.inc.php';
}

// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (is_array($env = @include dirname(__DIR__).'/.env.local.php')) {
    $_ENV += $env;
} elseif (!class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} else {
    $path = dirname(__DIR__).'/.env';
    $dotenv = new Dotenv(false);

    // load all the .env files
    if (method_exists($dotenv, 'loadEnv')) {
        $dotenv->loadEnv($path);
    } else {
        // fallback code in case your Dotenv component is not 4.2 or higher (when loadEnv() was added)

        if (file_exists($path) || !file_exists($p = "$path.dist")) {
            $dotenv->load($path);
        } else {
            $dotenv->load($p);
        }

        if (null === $env = (isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : (isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : null))) {
            $dotenv->populate(array('APP_ENV' => $env = 'prod'));
        }

        if ('test' !== $env && file_exists($p = "$path.local")) {
            $dotenv->load($p);
            $env = isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : (isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : $env);
        }

        if (file_exists($p = "$path.$env")) {
            $dotenv->load($p);
        }

        if (file_exists($p = "$path.$env.local")) {
            $dotenv->load($p);
        }
    }
}

// Set debug mode only when necessary
if (utils::ReadParam('debug', 'false') === 'true')
{
	$_SERVER['APP_DEBUG'] = true;
}

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = (isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : (isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : null)) ?: 'prod';
$_SERVER['APP_DEBUG'] = isset($_SERVER['APP_DEBUG']) ? $_SERVER['APP_DEBUG'] : (isset($_ENV['APP_DEBUG']) ? $_ENV['APP_DEBUG'] : ('prod' !== $_SERVER['APP_ENV']));
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

if ($_SERVER['APP_DEBUG']) {
	umask(0000);

	if (class_exists(Debug::class)) {
		Debug::enable();
	}
}

// If PORTAL_ID is not already defined, we look for it in a parameter
if(!defined('PORTAL_ID'))
{
	// Retrieving portal id from request params
	$sPortalId = utils::ReadParam('portal_id', '', true);
	if ($sPortalId == '')
	{
		echo "Missing argument 'portal_id'";
		exit;
	}

	// Defining portal constants
	define('PORTAL_ID', $sPortalId);
}
else
{
	@trigger_error(
		sprintf(
			'Usage of legacy "PORTAL_ID" constant ("%s") is deprecated. You should pass "portal_id" in the URL as GET parameter.',
			PORTAL_ID
		),
		E_USER_DEPRECATED
	);
}

define('PORTAL_CACHE_PATH', utils::GetCachePath() . '/portals/' . PORTAL_ID . '/');

// Constants to be used in templates and others
define('COMBODO_CURRENT_ENVIRONMENT', utils::GetCurrentEnvironment());
define('COMBODO_ABSOLUTE_URL', utils::GetAbsoluteUrlAppRoot());
define('COMBODO_MODULES_ABSOLUTE_URL',  utils::GetAbsoluteUrlModulesRoot());
define('COMBODO_PORTAL_BASE_ABSOLUTE_URL', utils::GetAbsoluteUrlModulesRoot() . 'itop-portal-base/portal/public/');
define('COMBODO_PORTAL_BASE_ABSOLUTE_PATH',  MODULESROOT . '/itop-portal-base/portal/public/');
define('COMBODO_PORTAL_INSTANCE_ABSOLUTE_URL', utils::GetAbsoluteUrlModulesRoot() . PORTAL_ID . '/');