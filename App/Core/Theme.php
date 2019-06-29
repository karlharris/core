<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

namespace App\Core;

use function config;
use function router;
use function utilities;

/**
 * Class Theme
 * @package App\Core
 */
class Theme
{
    /**
     * type for external resources
     * @const string RESOURCE_TYPE_EXTERNAL
     */
    const RESOURCE_TYPE_EXTERNAL = 'external';

    /**
     * type id for internal resources
     * @const string RESOURCE_TYPE_INTERNAL
     */
    const RESOURCE_TYPE_INTERNAL = 'internal';

    /**
     * @var array
     */
    private $defaultResourceOptions = [
        'sort' => 0,
        'type' => self::RESOURCE_TYPE_INTERNAL,
        'path' => TP.'assets'.DS.'default'.DS.'resources'.DS
    ];

    /**
     * @var string
     */
    private $theme;

    /**
     * @var bool
     */
    private $noRender = \false;

    /**
     * @var array
     */
    private $js = [];

    /**
     * @var array
     */
    private $less = [];

    /**
     * Theme constructor.
     */
    public function __construct()
    {
        $this->theme = config()['theme'];
    }

    /**
     * @param bool $noRender
     */
    public function setNoRender($noRender = \true)
    {
        $this->noRender = $noRender;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @param string $js
     * @param array $params
     */
    public function addJs($js, $params = [])
    {
        $params = array_merge(
            $this->defaultResourceOptions,
            $params
        );
        $resource = [
            'file' => $js,
            'sort' => ($params['sort'] ? $params['sort'] : 0)
        ];
        $this->setResource($this->js, $resource, 'js', ($params['type'] === 'internal' ? self::RESOURCE_TYPE_INTERNAL : self::RESOURCE_TYPE_EXTERNAL));
    }

    /**
     * @param string $less
     * @param array $params
     */
    public function addLess($less, $params = [])
    {
        $params = array_merge(
            $this->defaultResourceOptions,
            $params
        );
        $resource = [
            'file' => $less,
            'sort' => ($params['sort'] ? $params['sort'] : 0)
        ];
        $this->setResource($this->less, $resource, 'less', ($params['type'] === 'internal' ? self::RESOURCE_TYPE_INTERNAL : self::RESOURCE_TYPE_EXTERNAL));
    }

    /**
     * @return array
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * @return array
     */
    public function getLess()
    {
        return $this->less;
    }

    /**
     * is triggered before loading resources
     * @return bool
     */
    private function preDispatch()
    {
        if(method_exists(router()->getController(), 'preDispatchTheme'))
        {
            return router()->getController()->preDispatchTheme();
        }
        return \true;
    }

    /**
     * load less and js resources
     */
    public function loadResources()
    {
        if(!$this->preDispatch() || $this->noRender)
        {
            return;
        }
        if(config()['debug'])
        {
            $this->setResource($this->less, [
                [
                    'file' => 'pathinfo.less',
                    'sort' => 0
                ]
            ], 'less');
        }
        $this->setResource($this->js, config()['defaultJs']['internal'], 'js');
        $this->setResource($this->js, config()['defaultJs']['external'], 'js', self::RESOURCE_TYPE_EXTERNAL);
        $this->setResource($this->less, config()['defaultLess']['internal'], 'less');
        $this->setResource($this->less, config()['defaultLess']['external'], 'less', self::RESOURCE_TYPE_EXTERNAL);
        if(isset($this->js[0])) /** faster than count($this->js) > 0 or empty($this->js) */
        {
            utilities()->sortArrayByValue($this->js);
        }
        if(isset($this->less[0]))
        {
            utilities()->sortArrayByValue($this->less);
        }
    }

    /**
     * @param $array
     * @param $set
     * @param string $type
     * @param string $source
     */
    private function setResource(&$array, $set, $type, $source = self::RESOURCE_TYPE_INTERNAL)
    {
        if(isset($set['file']) || is_string($set))
        {
            $set = [$set];
        }
        if(!empty($set) && array_key_exists($type, array_flip(['js','less'])))
        {
            foreach($set as $data)
            {
                if(is_string($data))
                {
                    $data = [
                        'file' => $data,
                        'sort' => 0
                    ];
                }
                if($source === 'internal')
                {
                    if($files = $this->getPaths('assets'.DS.$type.DS.$data['file']))
                    {
                        $array[] = [
                            'files' => $files,
                            'sort' => $data['sort']
                        ];
                    }
                } else {
                    $array[] = [
                        'file' => $data['file'],
                        'sort' => $data['sort']
                    ];
                }
            }
        }
    }

    /**
     * @param $path
     * @return bool|array
     */
    private function getPaths($path)
    {
        $return = [];
        if(file_exists(TP.config()['theme'].DS.$path))
        {
            $return[] = [
                'file' => TP.config()['theme'].DS.$path,
                'sort' => -1
            ];
        }
        if(!empty(config()['inheritTheme']))
        {
            foreach(array_reverse(config()['inheritTheme']) as $index => $theme)
            {
                if(file_exists(TP.$theme.DS.$path))
                {
                    $return[] = [
                        'file' => TP.$theme.DS.$path,
                        'sort' => $index
                    ];
                }
            }
        }
        if($return === [])
        {
            return \false;
        } else {
            return $return;
        }
    }
}