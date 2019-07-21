<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App\Core;

use function logger;

/**
 * Class Plugin
 * @package App\Core
 */
class Plugin
{
    /**
     * @var bool|object
     */
    private $composer = \false;

    /**
     * @var array
     */
    private $plugins = [];

    /**
     * @var array
     */
    private $defaultConfig = [];

    /**
     * Plugin constructor.
     * @param $composer
     */
    public function __construct($composer = \false)
    {
        if($composer)
        {
            $this->composer = $composer;
            $this->defaultConfig = require_once(BP.'App/default_plugin_config.php');
            foreach($this->composer->getClassMap() as $className => $file)
            {
                if(\false !== strpos($className, 'App\Plugins\\'))
                {
                    $classPath = explode('\\', $className);
                    if(!isset($classPath[4]) && $classPath[2] === $classPath[3])
                    {
                        $plugin['object'] = new $className();
                        if(stream_resolve_include_path(PP.$classPath[2].DS.'config.php'))
                        {
                            $plugin['config'] = array_merge($this->defaultConfig, include_once(PP.$classPath[2].DS.'config.php'));
                        } else {
                            $plugin['config'] = $this->defaultConfig;
                        }
                        if(is_subclass_of($plugin['object'], 'App\Core\Plugin') && $plugin['config']['active'])
                        {
                            $this->plugins[$className] = $plugin;
                        } else {
                            logger()->log($className.' is deactivated or not extending the Plugin class (App\Core\Plugin)');
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getPluginNames()
    {
        return array_map(function($class)
        {
            return get_class($class);
        }, $this->plugins);
    }

    /**
     * @return array
     */
    public function getDefaultConfig()
    {
        return $this->defaultConfig;
    }
}