<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App;

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

HELP;
        print $help."\n";
        exit;
    }
}
$cli = new Cli();