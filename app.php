<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

/**
 * @return array
 */
function config()
{
    static $config;
    if(empty($config))
    {
        $config = array_merge(require('App/default_config.php'), require('config.php'));
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

if(!require_once('App/Autoloader.php'))
{
    die('Autoloader.php not found.');
}

router();

echo '<pre>';
print_r(router()->getPathParams());
print_r(router()->getRequestParams());
print_r(config());
echo '</pre>';

/*if(is_dir(BP.'install'))
{

}*/