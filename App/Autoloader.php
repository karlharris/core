<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

use App\Core\Database;
use App\Core\Logger;
use App\Core\Plugin;
use App\Core\Router;
use App\Core\Theme;
use App\Core\Utilities;

/**
 * shorter directory separator
 */
defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
/**
 * is ssl connection?
 */
defined('isSecure') ?: define('isSecure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443));
/**
 * absolute base path
 */
defined('BP') ?: define('BP', getcwd().DS);
/**
 * base url
 */
defined('BU') ?: define('BU', (isSecure ? 'https://' : 'http://').(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').DS);
/**
 * theme path
 */
defined('TP') ?: define('TP', BP.'theme'.DS);
/**
 * cache path
 */
defined('CP') ?: define('CP', BP.'var'.DS.'cache'.DS);
/**
 * plugin path
 */
defined('PP') ?: define('PP', BP.'App'.DS.'Plugins'.DS);

$composer = require_once(BP.'vendor/autoload.php');

/**
 * @return array
 */
function config()
{
    static $config;
    if(empty($config))
    {
        try
        {
            $config = array_merge(require(BP.'App/default_config.php'), require(BP.'config.php'));
        } catch(\Exception $e) {
            logger()->log('config() -> '.$e->getMessage());
        }
        if(isset($config['db']))
        {
            unset($config['db']);
        }
        if($config['show_errors'])
        {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
        date_default_timezone_set($config['timezone']);
    }
    return $config;
}

/**
 * @return Utilities
 */
function utilities()
{
    static $utilities;
    if(!$utilities instanceOf Utilities)
    {
        $utilities = new Utilities();
    }
    return $utilities;
}

/**
 * @return Logger
 */
function logger()
{
    static $log;
    if(!$log instanceOf Logger)
    {
        $log = new Logger();
    }
    return $log;
}

/**
 * @return Router
 */
function router()
{
    static $router;
    if(!$router instanceOf Router)
    {
        $router = new Router();
    }
    return $router;
}

/**
 * @param bool|object $composer
 * @return Plugin
 */
function plugin($composer = false)
{
    static $plugin;
    if(!$plugin instanceOf Plugin)
    {
        $plugin = new Plugin($composer);
    }
    return $plugin;
}

/**
 * @return Theme
 */
function theme()
{
    static $theme;
    if(!$theme instanceOf Theme)
    {
        $theme = new Theme();
    }
    return $theme;
}

/**
 * @return bool|PDO
 */
function db()
{
    static $db;
    if(!$db instanceof PDO)
    {
        $db = new Database();
        $db = $db->getDb();
    }
    return $db;
}

logger();
config();
db();
router();
plugin($composer);
theme()->loadResources();