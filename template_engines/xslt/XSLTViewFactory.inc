<?php


class XSLTViewFactory extends ViewFactory
{
    public function __construct($params = null)
    {
        $this->params = $params;
        require_once(realpath(dirname(__FILE__) . '/views/XSLTView.inc'));
        parent::__construct('xslt');
    }

    public function createView($view)
    {
        if (!property_exists($this->params->views, $view))
        {
            throw new Exception('Cannot find XSLT configuration for view <em>' . $view . '</em>');
        }

        $class_name = $this->params->views->$view->name . 'View';
        $class_file = realpath(dirname(__FILE__) . '/views/' . $class_name . '.inc');

        require_once($class_file);
        require_once($manager_file);

        $blitzView = new $class_name($this->params->views->$view->template);

        return $blitzView;
    }
}

?>