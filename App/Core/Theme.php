<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

namespace App\Core;

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

    private $defaultResourceOptions = [
        'sort' => 0,
        'type' => self::RESOURCE_TYPE_INTERNAL,
        'path' => DTP.'resources'.DS
    ];

    /**
     * @var bool
     */
    private $noRender = false;

    /**
     * @var bool
     */
    private $loadDefaultResources = true;

    /**
     * @var array
     */
    private $js = [];

    /**
     * @var array
     */
    private $less = [];

    /**
     * @param bool $noRender
     */
    public function setNoRender($noRender)
    {
        $this->noRender = $noRender;
    }

    /**
     * @param bool $loadDefaultResources
     */
    public function setLoadDefaultResources($loadDefaultResources)
    {
        $this->loadDefaultResources = $loadDefaultResources;
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
        $this->js[] = [
            'file' => ($params['type'] === 'internal' ? $params['path'].$js : $js),
            'sort' => $params['sort']
        ];
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
        $this->less[] = [
            'file' => ($params['type'] === 'internal' ? $params['path'].$less : $less),
            'sort' => $params['sort']
        ];
    }

    /**
     * is triggered before loading resources
     */
    private function preDispatch()
    {
        if(!is_null(router()->getController()) && method_exists(router()->getController(), 'preDispatchTheme'))
        {
            router()->getController()->preDispatchTheme();
        }
    }

    /**
     * load less and js resources
     */
    public function loadResources()
    {
        $this->preDispatch();
        if($this->noRender)
        {
            return;
        }
        if($this->loadDefaultResources)
        {
            $this->setResource($this->js, config()['defaultJs']['internal'], 'js');
            $this->setResource($this->js, config()['defaultJs']['external'], 'js', self::RESOURCE_TYPE_EXTERNAL);
            $this->setResource($this->less, config()['defaultLess']['internal'], 'less');
            $this->setResource($this->less, config()['defaultLess']['external'], 'less', self::RESOURCE_TYPE_EXTERNAL);
        }
        echo '<pre>';
        print_r($this->js);
        print_r($this->less);
        echo '</pre>';
    }

    /**
     * @param $array
     * @param $set
     * @param string $type
     * @param string $source
     */
    private function setResource(&$array, $set, $type, $source = self::RESOURCE_TYPE_INTERNAL)
    {
        if(!empty($set) && array_key_exists($type, array_flip(['js','less'])))
        {
            foreach($set as $data)
            {
                $array[] = [
                    'file' => ($source === 'internal' ? DTP.$type.DS.$data['file'] : $data['file']),
                    'sort' => $data['sort']
                ];
            }
        }
    }
}