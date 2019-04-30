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

spl_autoload_register(
    /**
     * @param string $class
     */
    function($class)
    {
        $prefix = 'App\\';
        $base_dir = __DIR__.DS;
        $len = strlen($prefix);
        if(strncmp($prefix, $class, $len) !== 0)
        {
            return;
        }
        $relative_class = substr($class, $len);
        $file = $base_dir.str_replace('\\', DS, $relative_class).'.php';
        if(file_exists($file))
        {
            require_once $file;
        }
    }
);

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
            die('config() -> '.$e->getMessage());
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
config();

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
 * @return \App\Core\Logging
 */
function logger()
{
    static $log;
    if(!$log instanceOf \App\Core\Logging)
    {
        $log = new \App\Core\Logging();
    }
    return $log;
}
logger();