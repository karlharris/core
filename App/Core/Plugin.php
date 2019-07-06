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
     * @var bool
     */
    protected $active = \false;

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
                    $classPath = explode('\\', $className);
                    if(!isset($classPath[4]) && $classPath[2] === $classPath[3])
                    {
                        $pluginObject = new $className();
                        if(is_subclass_of($pluginObject, 'App\Core\Plugin') && $pluginObject->isActive())
                        {
                            $this->plugins[] = $pluginObject;
                            echo '<pre>';
                            print_r(get_class_methods(get_class($pluginObject)));
                            echo '</pre>';
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
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
}