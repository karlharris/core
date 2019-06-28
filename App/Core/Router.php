<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

namespace App\Core;

use function config;

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
     * @var null|object
     */
    private $controller = \null;

    /**
     * @var string
     */
    private $controllerClass = '\\App\\Controllers\\Index';

    /**
     * @var string
     */
    private $actionName = 'index';

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
        if(!empty($this->uriParams['path']) && !$this->isValidPath($this->uriParams['path']))
        {
            $this->redirect('404', '404');
        }
        if(class_exists($this->controllerClass))
        {
            $this->controller = new $this->controllerClass();
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
    public function getRequestParam($name, $default = \false)
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
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
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
     * @return array
     */
    public function getRegisteredControllers()
    {
        return $this->registeredControllers;
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
        $this->controllerClass = '\App\Controllers';
        if(!empty($path))
        {
            foreach($path as $index => $part)
            {
                if(!isset($path[$index+1]))
                {
                    $this->actionName = $part;
                } else {
                    $this->controllerClass .= '\\'.ucfirst(strtolower($part));
                }
            }
        } else {
            $this->controllerClass .= '\\Index';
        }
        $this->uriParams = [
            'path' => $path,
            'request' => $_REQUEST
        ];
    }

    /**
     * @param array $params
     * @param bool|string $httpStatus
     */
    public function redirect($params = [], $httpStatus = \false)
    {
        if($httpStatus === '301')
        {
            header("HTTP/1.1 301 Moved Permanently");
        }
        if($httpStatus === '404')
        {
            header("HTTP/1.0 404 Not Found", \true, 404);
        }

        if(is_string($params))
        {
            $params = [$params];
        }

        $path = '/';
        if(isset($params[0]))
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
        $controllers = $this->registeredControllers;
        $lastLevel = array_pop($paths);
        if(empty($paths))
        {
            return array_key_exists($lastLevel, $controllers) || in_array($lastLevel, $controllers);
        }
        foreach($paths as $path)
        {
            if(!is_array($controllers))
            {
                return \false;
            } elseif(array_key_exists($path, $controllers))
            {
                $controllers = $controllers[$path];
            } else {
                return \false;
            }
        }
        return array_key_exists($lastLevel, $controllers) || in_array($lastLevel, $controllers);
    }

    /**
     * @param $pattern
     * @return bool
     */
    public function isPath($pattern)
    {
        $flippedPath = array_flip($this->getPathParams());
        if(strpos($pattern, '/') !== \false)
        {
            $pathArray = explode('/', $pattern);
            foreach($pathArray as $key => $path)
            {
                if($path === '*')
                {
                    return \true;
                } elseif(!array_key_exists($path, $flippedPath))
                {
                    return \false;
                }
            }
        } else {
            return $pattern === '*' || array_key_exists($pattern, $flippedPath);
        }
        return \true;
    }
}