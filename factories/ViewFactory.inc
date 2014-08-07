<?php

class ViewFactory
{
    protected $engine;
    protected $params;

    public function __construct($engine)
    {
        $this->engine = $engine;
        $this->params = fetchParams($engine);
    }

    private function fetchParams()
    {
        if (!is_null($this->params))
        {
            return $this->params;
        }

        $params_file = 'template_engines/' . $this->engine . '/params.json';

        if (($data = file_get_contents($params_file)) == null)
        {
            throw new Exception('Could not load ' . $params_file);
        }

        if (($params = json_decode($data)) === false)
        {
            throw new Exception('Could not decode JSON: ' . $params_file);
        }

        $this->params = $params;
    }

    public function createView($view)
    {
        if (!property_exists($this->params->views, $view))
        {
            throw new Exception('Cannot find ' . $this->params->engine. ' configuration for view <em>' . $view . '</em>');
        }

        $engine_name = $this->params->engine->name . 'View';
        $engine_file = realpath('template_engines/' . $this->engine . '/views/' . $engine_name . '.inc');
        $class_name = $this->params->views->$view->name . 'View';
        $class_file = realpath('template_engines/' . $this->engine . '/views/' . $class_name . '.inc');

        require_once($engine_file);
        require_once($class_file);


        $blitzView = new $class_name($this->params->views->$view->template);

        return $blitzView;
    }
}
?>