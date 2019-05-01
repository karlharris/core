<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

namespace App\Core;

/**
 * Class Router
 * @package App\Core
 */
class Router
{
    /**
     * @var array
     */
    private $uriParams = [];

    /**
     * @var mixed
     */
    private $controller = null;

    /**
     * @var array
     */
    private $registeredControllers = [];

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->registeredControllers = config()['registeredControllers'];
        $this->processUri();
        $this->checkInstallation();
        if(!empty($this->uriParams['path']) && !$this->isValidPath($this->uriParams['path']))
        {
            $this->redirect('404', '404');
        }
        if(class_exists($this->uriParams['controllerClass']))
        {
            $this->controller = new $this->uriParams['controllerClass']();
        }
    }

    /**
     * @return array
     */
    public function getUriParams()
    {
        return $this->uriParams;
    }

    /**
     * @param string $name
     * @param mixed default
     * @return mixed
     */
    public function getRequestParam($name, $default = false)
    {
        if(isset($this->uriParams['request'][$name]))
        {
            return $this->uriParams['request'][$name];
        }
        return $default;
    }

    /**
     * @return mixed
     */
    public function getRequestParams()
    {
        return $this->uriParams['request'];
    }

    /**
     * @return mixed
     */
    public function getPathParams()
    {
        return $this->uriParams['path'];
    }

    /**
     * @return mixed
     */
    public function getControllerClass()
    {
        return $this->uriParams['controllerClass'];
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     * @param array $path
     */
    public function registerController($controller, $path = [])
    {
        if(empty($path))
        {
            $this->registeredControllers[] = $controller;
        } else {
            $this->registeredControllers[$controller] = $path;
        }
    }

    /**
     * set uri params
     */
    private function processUri()
    {
        $path = [];
        if(isset($_REQUEST['path']))
        {
            $path = explode('/', $_REQUEST['path']);
            if(empty(end($path)))
            {
                array_pop($path);
            }
            unset($_REQUEST['path']);
        }
        $controllerClass = '\App\Controller';
        if(!empty($path))
        {
            foreach($path as $part)
            {
                $controllerClass .= '\\'.ucfirst(strtolower($part));
            }
        } else {
            $controllerClass .= '\Index';
        }
        $this->uriParams = [
            'path' => $path,
            'request' => $_REQUEST,
            'controllerClass' => $controllerClass
        ];
    }

    /**
     * @param array $params
     * @param bool|string $httpStatus
     */
    public function redirect($params = [], $httpStatus = false)
    {
        if($httpStatus === '301')
        {
            header("HTTP/1.1 301 Moved Permanently");
        }
        if($httpStatus === '404')
        {
            header("HTTP/1.0 404 Not Found", true, 404);
        }

        if(is_string($params))
        {
            $params = [$params];
        }

        $path = '/';
        if(count($params) > 0)
        {
            foreach($params as $param)
            {
                $path .= $param.'/';
            }
        }
        header("Location: ".$path);
        exit;
    }

    /**
     * @param $paths
     * @return bool
     */
    private function isValidPath($paths)
    {
        $array = $this->registeredControllers;
        $lastLevel = array_pop($paths);
        if(empty($paths))
        {
            return array_key_exists($lastLevel, $array) || in_array($lastLevel, $array);
        }
        foreach($paths as $path)
        {
            if(!is_array($array))
            {
                return false;
            } elseif(array_key_exists($path, $array))
            {
                $array = $array[$path];
            } else {
                return false;
            }
        }
        return array_key_exists($lastLevel, $array) || in_array($lastLevel, $array);
    }

    /**
     * @param $pattern
     * @return bool
     */
    public function isPath($pattern)
    {
        $flippedPath = array_flip($this->getPathParams());
        if(strpos($pattern, '/') !== false)
        {
            $pathArray = explode('/', $pattern);
            foreach($pathArray as $key => $path)
            {
                if($path === '*')
                {
                    return true;
                } elseif(!array_key_exists($path, $flippedPath))
                {
                    return false;
                }
            }
        } else {
            return array_key_exists($pattern, $flippedPath);
        }
        return true;
    }

    /**
     * set install params if InstallAssets exists
     */
    private function checkInstallation()
    {
        if(is_dir(BP.'InstallAssets'))
        {
            if(!$this->isPath('install/*'))
            {
                $this->redirect('install');
            } else {
                $this->uriParams['controllerClass'] = str_replace(
                    '\App',
                    '\InstallAssets',
                    $this->uriParams['controllerClass']
                );
                if(!require_once('InstallAssets/Autoloader.php'))
                {
                    die('/InstallAssets/Autoloader.php not found.');
                }
            }
            $this->registeredControllers['install'] = [
                'system-check',
                'database' => [
                    'form',
                    'install'
                ],
                'configuration',
                'finished'
            ];
            theme()->addJs(
                'https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js',
                [
                    'type' => theme()::RESOURCE_TYPE_EXTERNAL,
                    'sort' => -500
                ]
            );
            theme()->addJs(
                'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
                [
                    'type' => theme()::RESOURCE_TYPE_EXTERNAL,
                    'sort' => -499
                ]
            );
            theme()->addLess(
                'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',
                ['type' => theme()::RESOURCE_TYPE_EXTERNAL,
                    'sort' => -500
                ]
            );
        }
    }
}