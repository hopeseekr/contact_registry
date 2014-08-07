<?php

interface ViewInterface
{
	public function __construct($tmpl_file);
	public function parse();

	public function block($context_path, array $parameters = null);	
}

class ViewFactory
{
    protected $engine;
    protected $params;

    public function __construct($engine)
    {
        $this->engine = $engine;
        $this->params = fetchParams($engine);
    }

    /**
     * @param string $view
     * @return ViewInterface
     */
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


        $viewObj = new $class_name($this->params->views->$view->template);

        return $viewObj;
    }
}
