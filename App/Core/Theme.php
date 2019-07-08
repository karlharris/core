<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

namespace App\Core;

use function config;
use function router;
use function utilities;
use function logger;

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
     * @var string
     */
    private $minCssFile = '';

    /**
     * @var string
     */
    private $minJsFile = '';

    /**
     * @var array
     */
    private $templates = [];

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
        $this->setResource(
            $this->js,
            $resource,
            'js',
            ($params['type'] === 'internal' ? self::RESOURCE_TYPE_INTERNAL : self::RESOURCE_TYPE_EXTERNAL)
        );
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
        $this->setResource(
            $this->less,
            $resource,
            'less',
            ($params['type'] === 'internal' ? self::RESOURCE_TYPE_INTERNAL : self::RESOURCE_TYPE_EXTERNAL)
        );
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
     * @return string
     */
    public function getMinCssFile()
    {
        return $this->minCssFile;
    }

    /**
     * @return string
     */
    public function getMinJsFile()
    {
        return $this->minJsFile;
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
        $resourcePath = strtolower(str_replace(['App\Controllers\\', '\\'], ['', '/'], router()->getControllerClass()).DS.router()->getActionName());
        $this->minCssFile = CP.'css'.$resourcePath.'.css';
        $this->minJsFile = CP.'js'.$resourcePath.'.js';
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
        if(!config()['cache']['js'] || !stream_resolve_include_path($this->minJsFile))
        {
            $this->processJs();
        }
        if(!config()['cache']['less'] || !stream_resolve_include_path($this->minCssFile))
        {
            $this->processLess();
        }
        $this->collectTemplates();
    }

    /**
     * collect and process resource file
     */
    private function processJs()
    {
        $this->setResource($this->js, config()['js']['internal'], 'js');
        $this->setResource($this->js, config()['js']['external'], 'js', self::RESOURCE_TYPE_EXTERNAL);
        if(isset($this->js[0])) /** faster than count($this->js) > 0 or empty($this->js) */
        {
            utilities()->sortArrayByValue($this->js);
            try
            {
                $minifiedJs = '';
                foreach($this->js as $files)
                {
                    foreach($files['files'] as $file)
                    {
                        /** not imported because not always necessary */
                        $minifiedJs .= \JShrink\Minifier::minify(file_get_contents($file['file']));
                    }
                }
                utilities()->mkd(str_replace('/'.basename($this->minJsFile), '', $this->minJsFile), 0777);
                file_put_contents($this->minJsFile, $minifiedJs);
            } catch(\Exception $e) {
                logger()->log('failed to minify js files -> '.$e->getMessage());
            }
        }
    }

    /**
     * collect and process resource file
     */
    private function processLess()
    {
        $this->setResource($this->less, config()['less']['internal'], 'less');
        $this->setResource($this->less, config()['less']['external'], 'less', self::RESOURCE_TYPE_EXTERNAL);
        if(isset($this->less[0]))
        {
            utilities()->sortArrayByValue($this->less);
            try
            {
                $parser = new \Less_Parser(['compress'=>true]);
                foreach($this->less as $files)
                {
                    foreach($files['files'] as $file)
                    {
                        $parser->parseFile($file['file'], str_replace(basename($file['file']), '', $file['file']));
                    }
                }
                utilities()->mkd(str_replace('/'.basename($this->minCssFile), '', $this->minCssFile), 0777);
                file_put_contents($this->minCssFile, $parser->getCss());
            } catch(\Exception $e) {
                logger()->log('failed to parse less files -> '.$e->getMessage());
            }
        }
    }

    /**
     * collect templates
     */
    private function collectTemplates()
    {
        $parts = explode('\\', str_replace('\\app\controllers\\', '', strtolower(router()->getControllerClass())));
        $parts[] = router()->getActionName();
        $paths = $used = [];
        $sort = -1;
        foreach($parts as $part)
        {
            $paths[] = 'frontend'.DS.(isset($used[0]) ? implode(DS, $used).DS : '').$part.DS;
            $used[] = $part;
        }
        foreach(array_reverse($paths) as $path)
        {
            if($check = $this->getPaths($path.'head.phtml', \true))
            {
                $this->templates[] = $check['file'];
            }
            if($check = $this->getPaths($path.'index.phtml', \true))
            {
                $this->templates[] = $check['file'];
            }
            if($check = $this->getPaths($path.'foot.phtml', \true))
            {
                $this->templates[] = $check['file'];
            }
            $sort++;
        }
        $this->output();
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
                    if($files = $this->getPaths('assets'.DS.$type.DS.$data['file'], (isset($data['override']) ? $data['override'] : \false)))
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
     * @param bool $override
     * @return bool|array
     */
    private function getPaths($path, $override = \false)
    {
        $return = [];
        if(stream_resolve_include_path(TP.config()['theme'].DS.$path))
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
                if(stream_resolve_include_path(TP.$theme.DS.$path))
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
            if($override)
            {
                return array_pop($return);
            }
            return $return;
        }
    }

    /**
     * @param $file
     * @param bool $echo
     * @return bool|string
     */
    private function renderPhtml($file, $echo = false)
    {
        $output = '';
        ob_start();
        include($file);
        $output .= ob_get_clean();
        if(config()['debug'])
        {
            $output = '<div class="path-info-div"><small class="path-info-small" title="'.$file.'">FILE: '.$file.'</small>'.$output.'</div>';
        }
        if($echo)
        {
            echo $output;
        } else {
            return $output;
        }
        return false;
    }

    /**
     * @param $output
     * @return null|string|string[]
     */
    public function minifyOutput($output)
    {
        if(preg_match("/\<html/i",$output) == 1 && preg_match("/\<\/html\>/i",$output) == 1)
        {
            $output = preg_replace([
                '/\>[^\S ]+/s',
                '/[^\S ]+\</s',
                '/(\s)+/s'
            ], [
                '>',
                '<',
                '\\1'
            ], $output);
        }
        return $output;
    }

    /**
     * output rendered phtml
     */
    private function output()
    {
        $output = '';
        foreach($this->templates as $template)
        {
            $output .= $this->renderPhtml($template);
        }
        $copyrightDate = (date('Y') === '2018' ? '2018' : '2018 - '.date('Y'));
        $copyright = <<<COPYRIGHT
<!--
  -- Copyright (c) $copyrightDate. karlharris.org
  -->

COPYRIGHT;
        echo $copyright.$this->minifyOutput($output);
    }
}