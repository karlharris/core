<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App;

use Exception;
use PDO;
use PDOException;
use ReflectionException;
use ReflectionMethod;
use function logger;

if('cli' !== PHP_SAPI)
{
    header('Content-type: text/html; charset=utf-8', true, 503);
    echo '<h2>Error</h2>';
    echo 'This file is for cli usage only.';
    echo '<h2>Fehler</h2>';
    echo 'Diese Datei ist nur für die Nutzung via cli gedacht.';
    return;
}
if(!require_once('App/Autoloader.php'))
{
    die('Autoloader.php not found.');
}
/**
 * Class Cli
 * @package App
 */
class Cli
{
    /**
     * @var string
     */
    private $oldConfig = '';

    /**
     * Cli constructor.
     */
    public function __construct()
    {
        if(!isset($_SERVER['argv'][1]))
        {
            $this->printHelp();
        }
        $args = explode(':', strtolower($_SERVER['argv'][1]));
        if(3 !== count($args) || 'core' !== $args[0])
        {
            $this->printHelp();
        }
        $method = $args[1].ucfirst($args[2]);
        if(!method_exists($this, $method))
        {
            $this->printHelp();
        }
        $this->{$method}();
    }

    /**
     * delete all cache files
     */
    private function cacheClear()
    {
        utilities()->deleteInDirectory(CP);
    }

    /**
     * delete css cache files
     */
    private function cacheCss()
    {
        utilities()->deleteInDirectory(CP.'css');
    }

    /**
     * delete js cache files
     */
    private function cacheJs()
    {
        utilities()->deleteInDirectory(CP.'js');
    }

    /**
     * delete html cache files
     */
    private function cacheHtml()
    {
        utilities()->deleteInDirectory(CP.'html');
    }

    /**
     * install plugin
     */
    private function pluginInstall()
    {
        $plugin = $this->checkPlugin();
        try
        {
            $reflector = new ReflectionMethod($plugin, 'install');
            if($reflector->getDeclaringClass()->getName() === get_class($plugin))
            {
                $installResult = plugin()->install();
            }
        } catch(ReflectionException $e) {
            logger()->log('ReflectionException -> '.$e->getMessage());
            exit;
        }
        if(!isset($installResult))
        {
            $installResult = $plugin->install();
        } else {
            if($plugin->install() === false)
            {
                echo 'Error during installation (caused by plugin class), remove from database: "'.$_SERVER['argv'][2].'".'."\n";
                $this->pluginUninstall(true);
            }
        }
        plugin()->setPluginToProcess('');
        if($installResult === true)
        {
            plugin()->reloadPluginData();
            echo 'Successfully installed plugin "'.$_SERVER['argv'][2].'".'."\n";
            exit;
        } elseif($installResult === 1)
        {
            echo 'Could not write to database.'."\n";
            exit;
        } elseif($installResult === 2)
        {
            echo 'Plugin already installed.'."\n";
            exit;
        }
    }

    /**
     * activate installed plugin
     */
    private function pluginActivate()
    {
        $this->activateOrDeactivate();
    }

    /**
     * deactivate installed plugin
     */
    private function pluginDeactivate()
    {
        $this->activateOrDeactivate('deactivate');
    }

    /**
     * @param string $method
     */
    private function activateOrDeactivate($method = 'activate')
    {
        $plugin = $this->checkPlugin();
        try
        {
            $reflector = new ReflectionMethod($plugin, $method);
            if($reflector->getDeclaringClass()->getName() === get_class($plugin))
            {
                $aodResult = plugin()->{$method}();
            }
        } catch(ReflectionException $e) {
            logger()->log('ReflectionException -> '.$e->getMessage());
            exit;
        }
        if(!isset($aodResult))
        {
            $aodResult = $plugin->{$method}();
        } else {
            $plugin->{$method}();
        }
        plugin()->setPluginToProcess('');
        if($aodResult === true)
        {
            plugin()->reloadPluginData();
            echo 'Successfully '.$method.'d plugin "'.$_SERVER['argv'][2].'".'."\n";
            exit;
        } elseif($aodResult === 1)
        {
            echo 'Could not update database.'."\n";
            exit;
        } elseif($aodResult === 2)
        {
            echo 'Plugin not installed.'."\n";
            exit;
        } elseif($aodResult === 3)
        {
            echo 'Plugin already '.$method.'d.'."\n";
            exit;
        }
    }

    /**
     * uninstall plugin
     * @param bool $internalCall
     */
    private function pluginUninstall($internalCall = false)
    {
        $plugin = $this->checkPlugin();
        if($internalCall)
        {
            plugin()->uninstall();
            exit;
        }
        try
        {
            $reflector = new ReflectionMethod($plugin, 'uninstall');
            if($reflector->getDeclaringClass()->getName() === get_class($plugin))
            {
                $uninstallResult = plugin()->uninstall();
            }
        } catch(ReflectionException $e) {
            logger()->log('ReflectionException -> '.$e->getMessage());
            exit;
        }
        if(!isset($uninstallResult))
        {
            $uninstallResult = $plugin->uninstall();
        } else {
            $plugin->uninstall();
        }
        plugin()->setPluginToProcess('');
        if($uninstallResult === true)
        {
            plugin()->reloadPluginData();
            echo 'Successfully uninstalled plugin "'.$_SERVER['argv'][2].'".'."\n";
            exit;
        } elseif($uninstallResult === 1)
        {
            echo 'Could not delete from database.'."\n";
            exit;
        } elseif($uninstallResult === 2)
        {
            echo 'Plugin not installed.'."\n";
            exit;
        }
    }

    /**
     * @return object
     */
    private function checkPlugin()
    {
        if(!db() instanceof PDO)
        {
            echo 'Database needs to be installed. execute "php App/Cli.php core:db:install" via cli (root directory).'."\n";
            exit;
        }
        if(!isset($_SERVER['argv'][2]) || !is_string($_SERVER['argv'][2]))
        {
            echo 'No plugin name given.'."\n";
            exit;
        }
        $className = '\Plugins\\'.$_SERVER['argv'][2].'\\'.$_SERVER['argv'][2];
        if(!class_exists($className))
        {
            echo 'No plugin named "'.$_SERVER['argv'][2].'" found.'."\n";
            exit;
        }
        plugin()->setPluginToProcess($_SERVER['argv'][2]);
        return new $className();
    }

    /**
     * install db
     */
    private function dbInstall()
    {
        echo "\n".'Are you aware that the config.php file will be overwritten? Type "yes" if you wish to continue: ';
        if('yes' !== $this->readInputLine())
        {
            echo "\n".'Aborting.'."\n\n";
            return \false;
        }
        $this->checkAndSetOldConfig();
        $data = $this->getUserInput([
            'Host: ',
            'User: ',
            'Database name: ',
            'Password: '
        ]);
        if(4 === count($data))
        {
            try
            {
                $connection = new PDO(
                    'mysql:dbname='.$data[2].';host='.$data[0],
                    $data[1],
                    $data[3]
                );
                if(!$connection instanceof PDO)
                {
                    echo "\n".'No valid database connection'."\n";
                    return \false;
                }
            } catch(PDOException $e)
            {
                echo "\n".'Could not establish database connection.'."\n".$e->getMessage()."\n".'Check your data and try again.'."\n";
                return \false;
            }
            $statement = $connection->prepare('CREATE TABLE IF NOT EXISTS core_plugins (
                id int auto_increment not null,
                plugin_name varchar(255) not null,
                active boolean default 0,
                primary key (id)
            )');
            $statement->execute();
            $configContent = <<<CONTENT
<?php

return [
    'db' => [
        'host' => '$data[0]',
        'user' => '$data[1]',
        'name' => '$data[2]',
        'pass' => '$data[3]'
    ],
    'databaseLock' => \\true
];
CONTENT;
            try
            {
                file_put_contents(BP.'config.php', $configContent);
            } catch(Exception $e)
            {
                echo "\n".'Could not write database config.'."\n".$e->getMessage()."\n".'Check user rights and owner of config.php file and try again.'."\n";
                return \false;
            }
            echo "\n".'Database installed.'."\n";
            $this->writeOldConfig();
            return \true;
        } else {
            echo "\n".'Not enough parameters.'."\n";
        }
        return \false;
    }

    /**
     * check if config.php exists
     * read file if it does
     */
    private function checkAndSetOldConfig()
    {
        if(stream_resolve_include_path(BP.'config.php'))
        {
            $this->oldConfig = file_get_contents(BP.'config.php');
            echo "\n".'Old data found.'."\n\n";
        }
    }

    /**
     * write backup file if there is old data
     */
    private function writeOldConfig()
    {
        if('' !== $this->oldConfig)
        {
            $i = 1;
            while(stream_resolve_include_path(BP.'old_config_'.$i.'.php'))
            {
                $i++;
            }
            try
            {
                file_put_contents(BP.'old_config_'.$i.'.php', $this->oldConfig);
                echo "\n".'Written old data to file "'.BP.'old_config_'.$i.'.php".'."\n\n";
            } catch(Exception $e)
            {
                echo "\n".'Could not write copy of old config.php ('.BP.'old_config_'.$i.'.php).'."\n".$e->getMessage()."\n".'Check user rights and owner of root directory.'."\n\n";
            }
        }
    }

    /**
     * @param array $questions
     * @return array
     */
    private function getUserInput($questions = [])
    {
        $answers = [];
        if(isset($questions[0]))
        {
            foreach($questions as $question)
            {
                echo $question;
                $line = $this->readInputLine();
                if('' === $line)
                {
                    echo "\n".'No valid input - aborting'."\n\n";
                    die;
                } else {
                    $answers[] = $line;
                }
            }
        }
        return $answers;
    }

    /**
     * @return string
     */
    private function readInputLine()
    {
        $handle = fopen("php://stdin","r");
        $line = trim(fgets($handle));
        if(!fclose($handle))
        {
            echo "\n".'WARNING: Could not close file handle.'."\n";
        }
        return $line;
    }

    /**
     * output help text
     */
    public function printHelp()
    {
        $help = <<<HELP

                          ___  __  ____  ____
                         / __)/  \(  _ \(  __)
                        ( (__(  O ))   / ) _) 
                         \___)\__/(__\_)(____)

    ==================================================================
                          Available commands
    ==================================================================

    core:cache:clear                        delete all cache files
    core:cache:css                          delete css cache files
    core:cache:js                           delete js cache files
    core:cache:html                         delete html cache files

    core:db:install                         install database
                                            Be aware that the config.php
                                            file will be overwritten!

    core:plugin:install [plugin_name]       install plugin
    core:plugin:uninstall [plugin_name]     uninstall plugin
    core:plugin:activate [plugin_name]      activate plugin
    core:plugin:deactivate [plugin_name]    deactivate plugin

HELP;
        print $help."\n";
        exit;
    }
}
$cli = new Cli();