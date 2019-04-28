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
     * @var string
     */
    private $controller = 'index';

    /**
     * @var string
     */
    private $action = 'index';

    /**
     * @var array
     */
    private $uriParams = [];

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $path = [];
        if(isset($_REQUEST['path']))
        {
            $path = explode('/', $_REQUEST['path']);
            if(empty(end($path)))
            {
                array_pop($path);
            }
            $path['path'] = $_REQUEST['path'];
            unset($_REQUEST['path']);
        }
        $this->uriParams = [
            'path' => $path,
            'request' => $_REQUEST
        ];
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     * @return Router
     */
    public function setController($controller)
    {
        $this->controller = strtolower($controller);
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return Router
     */
    public function setAction($action)
    {
        $this->action = strtolower($action);
        return $this;
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
     * @param mixed $uriParam
     * @return Router
     */
    public function addUriParam($uriParam)
    {
        $this->uriParams[] = $uriParam;
        return $this;
    }

    /**
     * @param array $uriParams
     * @return Router
     */
    public function setUriParams($uriParams)
    {
        $this->uriParams = $uriParams;
        return $this;
    }

    /**
     * @param array $params
     * @param bool $httpStatus
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
}