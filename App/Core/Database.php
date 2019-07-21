<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App\Core;

use PDO;
use function logger;

/**
 * Class Database
 *
 * @package App\Core\Database
 */
class Database
{
    /**
     * @var bool|array
     */
    private $dbConfig = \false;

    /**
     * Database constructor.
     */
    public function __construct()
    {
        $config = require(BP.'config.php');
        if(isset($config['db']))
        {
            $this->dbConfig = $config['db'];
        }
    }

    /**
     * @return bool|\PDO
     */
    private function createConnection()
    {
        try
        {
            $connection = new PDO(
                'mysql:dbname='.$this->dbConfig['name'].';host='.$this->dbConfig['host'],
                $this->dbConfig['user'],
                $this->dbConfig['pass']
            );
            return ($connection instanceof PDO ? $connection : \false);
        } catch(\PDOException $e)
        {
            logger()->log('Could not establish database connection -> '.$e->getMessage());
            return \false;
        }
    }

    /**
     * @return bool|\PDO
     */
    public function getDb()
    {
        if(!$this->dbConfig)
        {
            return \false;
        }
        return $this->createConnection();
    }
}