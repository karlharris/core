<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

spl_autoload_register(
    /**
     * @param string $class
     */
    function($class)
    {
        $prefix = 'InstallAssets\\';
        $base_dir = __DIR__.DS;
        $len = strlen($prefix);
        if(strncmp($prefix, $class, $len) !== 0)
        {
            return;
        }
        $relative_class = substr($class, $len);
        $file = $base_dir.str_replace('\\', DS, $relative_class).'.php';
        if(file_exists($file))
        {
            require_once $file;
        }
    }
);