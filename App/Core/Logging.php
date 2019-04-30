<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

namespace App\Core;

/**
 * Class Logging
 * @package App\Core
 */
class Logging
{
    /**
     * @var string
     */
    private $mode = 'file';

    /**
     * @var array
     */
    private $modes = [
        'file',
        'mail'
    ];

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        if(array_key_exists($mode, array_flip($this->modes)))
        {
            $this->mode = $mode;
        }
    }
}