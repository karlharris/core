<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

namespace App\Controllers;

/**
 * Class Widgets
 * @package App\Controllers
 */
class Widgets
{
    /**
     * @var array
     */
    private $template = [];

    /**
     * Widgets constructor.
     */
    public function __construct()
    {
        $widgetName = strtolower(router()->getRequestParam('load'));
        if(!$widgetName || !isset(config()['registeredWidgets'][$widgetName]))
        {
            router()->redirect('404','404');
        }
        theme()->setNoRender(true);
        $widgetClass = '\App\Widgets\\'.ucfirst($widgetName);
        if(class_exists($widgetClass))
        {
            $widgetClass = new $widgetClass();
        }
        theme()->resolveTemplateResults(theme()->getPaths(DS.'widgets'.DS.$widgetName.'.phtml', \true), $this->template);
        $this->template = array_shift($this->template);
        if($this->template)
        {
            echo theme()->minifyOutput(theme()->renderPhtml($this->template));
        }
    }
}