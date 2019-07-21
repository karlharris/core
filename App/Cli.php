<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App;

use Exception;
use PDO;
use PDOException;

if(PHP_SAPI !== 'cli')
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
     * install db
     */
    private function dbInstall()
    {
        $requirements = [
            'Host: ',
            'User: ',
            'Database name: ',
            'Password: '
        ];
        echo 'Are you aware that the config.php file will be overwritten? Type "yes" if you wish to continue: ';
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        if('yes' !== $line)
        {
            echo "\n".'Aborting.'."\n\n";
            die;
        }
        $data = [];
        foreach($requirements as $requirement)
        {
            echo $requirement;
            $handle = fopen ("php://stdin","r");
            $line = trim(fgets($handle));
            if('' === $line)
            {
                echo "\n".'No valid input - aborting'."\n\n";
                die;
            } else {
                $data[] = $line;
            }
        }
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
                    echo 'No valid database connection';
                    return \false;
                }
            } catch(PDOException $e)
            {
                echo 'Could not establish database connection.'."\n".$e->getMessage()."\n".'Check your data and try again.'."\n";
                return \false;
            }
            $configContent = <<<CONTENT
<?php

return [
    'db' => [
        'host' => '$data[0]',
        'user' => '$data[1]',
        'name' => '$data[2]',
        'pass' => '$data[3]'
    ]
];
CONTENT;
            try
            {
                file_put_contents(BP.'config.php', $configContent);
            } catch(Exception $e)
            {
                echo 'Could not write database config.'."\n".$e->getMessage()."\n".'Check user rights and owner of config.php file and try again.'."\n";
            }
        }
        echo "\n".'Database installed.'."\n";
        return \true;
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

    core:cache:clear            delete all cache files
    core:cache:css              delete css cache files
    core:cache:js               delete js cache files
    
    core:db:install             install database
                                Be aware that the config.php file will be overwritten!

HELP;
        print $help."\n";
        exit;
    }
}
$cli = new Cli();