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
     * @var array
     */
    private $defaultOptions = [
        'type' => 'error',
        'backtrace' => true,
        'mode' => '',
        'email' => '',
        'file' => ''
    ];

    /**
     * @var string
     */
    private $file = '';

    /**
     * Logging constructor.
     */
    public function __construct()
    {
        $filePath = 'var'.DS.'log'.DS;
        $this->file = BP.$filePath.'error_'.date('Ymd').'.log';
        if(!file_exists($this->file))
        {
            utilities()->rmkdir($filePath);
        }
    }

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

    /**
     * Logging
     * @param $message
     * @param array $options
     */
    public function log($message, $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);
        $mode = ($options['mode'] === '' ? $this->mode : $options['mode']);
        ob_start();
        echo "\n[".date('Y-m-d H:i:s')."]\n";
        echo $message."\n";
        if($options['backtrace'])
        {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        $output = ob_get_clean();
        switch($mode)
        {
            case 'mail':
                $mail = ($options['email'] === '' ? config()['loggerEmail'] : $options['email']);
                if($mail !== '')
                {
                    mail($mail, 'error logging '.BU, $output);
                    break;
                }
            case 'file':
            default:
                file_put_contents(($options['file'] === '' ? $this->file : $options['file']), $output, FILE_APPEND);
                break;
        }
    }
}