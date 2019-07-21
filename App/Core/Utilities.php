<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App\Core;

use Exception;

use function logger;

/**
 * Class Utilities
 *
 * @package App\Core
 */
class Utilities
{
    /**
     * for possible sort flags see http://php.net/manual/de/function.array-multisort.php
     *
     * @param $array
     * @param int $direction
     * @param string $sortKey
     * @return array
     */
    public function sortArrayByValue(&$array, $direction = SORT_ASC, $sortKey = 'sort')
    {
        if(is_array($array))
        {
            $sort = [];
            foreach ($array as $key => $row)
            {
                $sort[$key] = $row[$sortKey];
            }
            array_multisort($sort, $direction, $array);
        }
        return $array;
    }

    /**
     * @param $str
     * @param bool $allowWhitespaces
     * @param bool $allowBreaks
     * @param string $charsToClean
     * @return string
     */
    public function cleanString($str, $allowWhitespaces = \false, $allowBreaks = \false, $charsToClean = '<>|,;.:-#\'+*~´`?ß\\=})]([/{&%$§"!²³^°@€µäÄüÜöÖ-')
    {
        $str = str_replace(str_split($charsToClean), '', $str);
        if(!$allowWhitespaces)
        {
            $str = str_replace(' ', '', $str);
        }
        if(!$allowBreaks)
        {
            $str = str_replace(['<br>','<br/>','<br />',"\n\r","\r\n","\n","\r",\PHP_EOL], '', $str);
        }
        return $str;
    }

    /**
     * @param $length
     * @param string $specials
     * @param string $keySpace
     * @return string
     */
    public function createRandomStr($length = 12, $specials = '!§$%&/()=?+*#-_', $keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        if($specials)
        {
            $keySpace .= $specials;
        }
        $str = '';
        $max = mb_strlen($keySpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i)
        {
            $str .= $keySpace[rand(0, $max)];
        }
        return utf8_encode($str);
    }

    /**
     * create directory
     *
     * @param $dirPath
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function mkd($dirPath, $mode = 0777, $recursive = \true)
    {
        return is_dir($dirPath) || mkdir($dirPath, $mode, $recursive);
    }

    /**
     * @param $file
     * @return bool
     */
    public function isValidFilename($file)
    {
        return preg_match('/^([-\.\w]+)$/', $file) > 0;
    }

    /**
     * @param string $basePath
     */
    public function deleteInDirectory($basePath)
    {
        $dirHandle = opendir($basePath);
        if(is_resource($dirHandle))
        {
            while(false !== ($entry = readdir($dirHandle)))
            {
                if($entry != '.' && $entry != '..')
                {
                    if(is_file($basePath.$entry))
                    {
                        try
                        {
                            unlink($basePath.$entry);
                        } catch(Exception $e) {
                            logger()->log('Could not unlink file -> '.$basePath.$entry.PHP_EOL.$e->getMessage());
                        }
                    } elseif(is_dir($basePath.$entry))
                    {
                        $this->deleteFiles($entry, $basePath);
                    }
                }
            }
        }
    }

    /**
     * @param string $dir
     * @param bool|string $basePath
     */
    public function deleteFiles($dir, $basePath = false)
    {
        $basePath = ($basePath ? $basePath : '').$dir.DS;
        $dirHandle = opendir($basePath);
        if(is_resource($dirHandle))
        {
            while(false !== ($entry = readdir($dirHandle)))
            {
                if($entry != '.' && $entry != '..')
                {
                    if(is_file($basePath.$entry))
                    {
                        try
                        {
                            unlink($basePath.$entry);
                        } catch(Exception $e) {
                            logger()->log('Could not unlink file -> '.$basePath.$entry.PHP_EOL.$e->getMessage());
                        }
                    } elseif(is_dir($basePath.$entry))
                    {
                        $this->deleteFiles($entry, $basePath);
                    }
                }
            }
            try
            {
                rmdir($basePath);
            } catch (Exception $e)
            {
                logger()->log('Could not delete directory -> '.$basePath.PHP_EOL.$e->getMessage());
            }
        }
    }
}