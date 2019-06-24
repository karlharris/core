<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

namespace App\Core;

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
            $sort = array();
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
    public function cleanString($str, $allowWhitespaces = false, $allowBreaks = false, $charsToClean = '<>|,;.:-#\'+*~´`?ß\\=})]([/{&%$§"!²³^°@€µäÄüÜöÖ-')
    {
        $str = str_replace(str_split($charsToClean), '', $str);
        if(!$allowWhitespaces)
        {
            $str = str_replace(' ', '', $str);
        }
        if(!$allowBreaks)
        {
            $str = str_replace(array('<br>','<br/>','<br />',"\n\r","\r\n","\n","\r",PHP_EOL), '', $str);
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
     * @return bool
     */
    public function mkd($dirPath, $mode = 0777)
    {
        return is_dir($dirPath) || mkdir($dirPath, $mode, true);
    }

    /**
     * create directory path
     *
     * @param string $path
     * @return void
     */
    public function rmkdir($path)
    {
        try
        {
            $path = explode('/', $path);
            if(empty($path[0]))
            {
                unset($path[0]);
            }
            if(empty($path[count($path)]))
            {
                unset($path[count($path)]);
            }

            $resolvedPath = BP;
            $resolvedPath = substr($resolvedPath, 0, -1);
            foreach($path as $dir)
            {
                $temp = $resolvedPath.DS.$dir;
                if(!is_dir($temp))
                {
                    mkdir($temp);
                }
                $resolvedPath = $temp;
            }
        } catch(\Exception $e)
        {
            logger()->log('Utilities::rmkdir() -> '.$e->getMessage());
        }
    }
}