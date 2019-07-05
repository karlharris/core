<?php
/**
 * Copyright (c) 2019. karlharris.org
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
     * Plugin constructor.
     * @param $composer
     */
    public function __construct($composer = \false)
    {
        if($composer)
        {
            $this->composer = $composer;
            foreach($this->composer->getClassMap() as $className => $file)
            {
                if(strpos($className, 'App\Plugins\\') !== \false)
                {
                    $pluginObject = new $className();
                    if(is_subclass_of($pluginObject, 'App\Core\Plugin'))
                    {
                        $this->plugins[] = $pluginObject;
                    } else {
                        logger()->log($className.' is not extending the Plugin class (App\Core\Plugin)');
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}