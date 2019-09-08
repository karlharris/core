<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace Plugins\CoreOrm;

use App\Core\Plugin;
use PDO;

/**
 * Class CoreOrm
 * @package Plugins\CoreOrm
 */
class CoreOrm extends Plugin
{
    /**
     * @return bool|void
     */
    public function install()
    {
        echo 'child::install().'."\n";
        try
        {
            $statement = db()->prepare('CREATE TABLE IF NOT EXISTS coreOrm_models (
                id int auto_increment not null,
                modelname varchar(255) not null,
                classname varchar(255) not null,
                tablename varchar(255) not null,
                primary key (id)
            );');
            $statement->execute();
        } catch(\PDOException $e) {
            logger()->log('Could not create table -> '.$e->getMessage());
            return false;
        }
    }

    /**
     * @return bool|void
     */
    public function uninstall()
    {
        echo 'child::uninstall().'."\n";
        try
        {
            $statement = db()->prepare('DROP TABLE coreOrm_models;');
            $statement->execute();
        } catch(\PDOException $e) {
            logger()->log('Could not drop table -> '.$e->getMessage());
            return false;
        }
    }

    public function activate()
    {
        echo 'child::activate().'."\n";
    }

    public function deactivate()
    {
        echo 'child::deactivate().'."\n";
    }
}