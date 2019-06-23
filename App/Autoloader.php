<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

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

require_once(BP.'vendor/autoload.php');

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
 * @return \App\Core\Logger
 */
function logger()
{
    static $log;
    if(!$log instanceOf \App\Core\Logger)
    {
        $log = new \App\Core\Logger();
    }
    return $log;
}

/**
 * @return \App\Core\Utilities
 */
function utilities()
{
    static $utilities;
    if(!$utilities instanceOf \App\Core\Utilities)
    {
        $utilities = new \App\Core\Utilities();
    }
    return $utilities;
}

/**
 * @return \App\Core\Router
 */
function router()
{
    static $router;
    if(!$router instanceOf \App\Core\Router)
    {
        $router = new \App\Core\Router();
    }
    return $router;
}

/**
 * @return \App\Core\Theme
 */
function theme()
{
    static $theme;
    if(!$theme instanceOf \App\Core\Theme)
    {
        $theme = new \App\Core\Theme();
    }
    return $theme;
}

utilities();
logger();
config();
router();
theme();
theme()->loadResources();