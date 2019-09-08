<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App\Core;

use PDO;
use PDOException;
use function logger;

/**
 * Class Plugin
 * @package App\Core
 */
class Plugin
{
    /**
     * plugin management table name
     * @const string
     */
    const TABLE_NAME = 'core_plugins';

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
    private $installedPlugins = [];

    /**
     * @var array
     */
    private $activePlugins = [];

    /**
     * @var array
     */
    private $inactivePlugins = [];

    /**
     * @var array
     */
    private $defaultConfig = [];

    /**
     * @var string
     */
    private $pluginToProcess = '';

    /**
     * Plugin constructor.
     * @param $composer
     */
    public function __construct($composer = \false)
    {
        if($composer && db() instanceof PDO)
        {
            $this->composer = $composer;
            $this->defaultConfig = require_once(BP.'Plugins/default_config.php');
            $this->installedPlugins = $this->getInstalledPlugins();
            $this->activePlugins = $this->getActivePlugins();
            foreach($this->composer->getClassMap() as $className => $file)
            {
                if(\false !== strpos($className, 'Plugins\\'))
                {
                    $classPath = explode('\\', $className);
                    if(!isset($classPath[3]) && $classPath[1] === $classPath[2] && isset($this->activePlugins[$classPath[1]]))
                    {
                        $plugin['object'] = new $className();
                        if(stream_resolve_include_path(PP.$classPath[2].DS.'config.php'))
                        {
                            $plugin['config'] = array_merge($this->defaultConfig, include_once(PP.$classPath[2].DS.'config.php'));
                        } else {
                            $plugin['config'] = $this->defaultConfig;
                        }
                        if(is_subclass_of($plugin['object'], 'App\Core\Plugin'))
                        {
                            $this->plugins[$className] = $plugin;
                        } elseif(config()['debug']['plugin']) {
                            logger()->log($className.' is deactivated or not extending the Plugin class (App\Core\Plugin)');
                        }
                    }
                }
            }
            echo "\n".'----------------------------------------<br><pre>';
            print_r($this->plugins);
            echo '<pre>----------------------------------------<br>'."\n";
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

    /**
     * @return array
     */
    public function getInstalledPlugins()
    {
        if([] !== $this->installedPlugins)
        {
            return $this->installedPlugins;
        }
        try
        {
            $statement = db()->prepare('SELECT * FROM '.self::TABLE_NAME.';');
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            if($result)
            {
                $return = [];
                foreach($result as $row)
                {
                    $return[$row['plugin_name']] = $row;
                }
                return $return;
            }
        } catch(PDOException $e) {
            logger()->log('Could not fetch plugin data -> '.$e->getMessage());
        }
        return [];
    }

    /**
     * @return array
     */
    public function getActivePlugins()
    {
        if([] !== $this->activePlugins)
        {
            return $this->activePlugins;
        }
        try
        {
            $statement = db()->prepare('SELECT * FROM '.self::TABLE_NAME.' WHERE active = 1;');
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            if($result)
            {
                $return = [];
                foreach($result as $row)
                {
                    $return[$row['plugin_name']] = $row;
                }
                return $return;
            }
        } catch(PDOException $e) {
            logger()->log('Could not fetch plugin data -> '.$e->getMessage());
        }
        return [];
    }

    /**
     * @return array
     */
    public function getInactivePlugins()
    {
        if([] !== $this->inactivePlugins)
        {
            return $this->inactivePlugins;
        }
        try
        {
            $statement = db()->prepare('SELECT * FROM '.self::TABLE_NAME.' WHERE active = 0;');
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            if($result)
            {
                $return = [];
                foreach($result as $row)
                {
                    $return[$row['plugin_name']] = $row;
                }
                return $return;
            }
        } catch(PDOException $e) {
            logger()->log('Could not fetch plugin data -> '.$e->getMessage());
        }
        return [];
    }

    /**
     * reload installed plugins from database
     */
    public function reloadInstalledPlugins()
    {
        $this->installedPlugins = [];
        $this->installedPlugins = $this->getInstalledPlugins();
    }

    /**
     * reload active plugins from database
     */
    public function reloadActivePlugins()
    {
        $this->activePlugins = [];
        $this->activePlugins = $this->getActivePlugins();
    }

    /**
     * reload inactive plugins from database
     */
    public function reloadInactivePlugins()
    {
        $this->inactivePlugins = [];
        $this->inactivePlugins = $this->getInactivePlugins();
    }

    /**
     * reload all plugin state arrays
     */
    public function reloadPluginData()
    {
        $this->reloadInstalledPlugins();
        $this->reloadActivePlugins();
        $this->reloadInactivePlugins();
    }

    /**
     * @return integer|bool
     */
    public function install()
    {
        echo 'parent::install().'."\n";
        if('' !== plugin()->getPluginToProcess())
        {
            if(!isset(plugin()->getInstalledPlugins()[plugin()->getPluginToProcess()]))
            {
                try
                {
                    $statement = db()->prepare('INSERT INTO '.self::TABLE_NAME.' (plugin_name) VALUES (?);');
                    if($statement->execute([plugin()->getPluginToProcess()]))
                    {
                        return true;
                    }
                } catch(PDOException $e) {
                    logger()->log('Could not insert plugin data -> '.$e->getMessage());
                    return 1;
                }
                return 1;
            } else {
                return 2;
            }
        }
        return false;
    }

    /**
     * activate plugin
     * @return bool|int
     */
    public function activate()
    {
        echo 'parent::activate().'."\n";
        return $this->activateOrDeactivate();
    }

    /**
     * deactivate plugin
     * @return bool|int
     */
    public function deactivate()
    {
        echo 'parent::deactivate().'."\n";
        return $this->activateOrDeactivate(false);
    }

    /**
     * @param bool $activate
     * @return bool|int
     */
    private function activateOrDeactivate($activate = true)
    {
        if('' !== plugin()->getPluginToProcess())
        {
            if(isset(plugin()->getInstalledPlugins()[plugin()->getPluginToProcess()]))
            {
                if($activate && isset(plugin()->getActivePlugins()[plugin()->getPluginToProcess()]))
                {
                    return 3;
                } elseif(!$activate && isset(plugin()->getInactivePlugins()[plugin()->getPluginToProcess()]))
                {
                    return 3;
                }
                try
                {
                    $statement = db()->prepare('UPDATE '.self::TABLE_NAME.' SET active = '.($activate ? '1' : '0').' WHERE plugin_name = ?;');
                    if($statement->execute([plugin()->getPluginToProcess()]))
                    {
                        return true;
                    }
                } catch(PDOException $e) {
                    logger()->log('Could not update plugin data -> '.$e->getMessage());
                    return 1;
                }
                return 1;
            } else {
                return 2;
            }
        }
        return false;
    }

    /**
     * @return integer|bool
     */
    public function uninstall()
    {
        echo 'parent::uninstall().'."\n";
        if('' !== plugin()->getPluginToProcess())
        {
            if(isset(plugin()->getInstalledPlugins()[plugin()->getPluginToProcess()]))
            {
                try
                {
                    $statement = db()->prepare('DELETE FROM '.self::TABLE_NAME.' WHERE plugin_name = ?;');
                    if($statement->execute([plugin()->getPluginToProcess()]))
                    {
                        return true;
                    }
                } catch(PDOException $e) {
                    logger()->log('Could not delete plugin data -> '.$e->getMessage());
                    return 1;
                }
                return 1;
            } else {
                return 2;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getPluginToProcess()
    {
        return $this->pluginToProcess;
    }

    /**
     * @param string $pluginToProcess
     */
    public function setPluginToProcess(string $pluginToProcess)
    {
        $this->pluginToProcess = $pluginToProcess;
    }
}