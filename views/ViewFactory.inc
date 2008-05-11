<?php
interface BasicViewInterface
{
    public function handleRawHTML();
}

interface LoginViewInterface
{
    public function registerUser();

}

class ViewFactory
{
    private $engine;
    private $params;

    public function __construct($engine)
    {
        $this->engine = $engine;
        $this->fetchParams();
    }

    private function findView($class_name, $class_file)
    {
        if (!file_exists($class_file))
        {
            throw new Exception('Template Engine\'s Factory implementation was not found: ' . $class_file);
        }

        require_once($class_file);

        $viewFactory = new $class_name($this->params);

        return $viewFactory;
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
        $class_name = $this->params->engine->name . 'ViewFactory';
        $class_file = realpath('template_engines/' . $this->params->engine->dir . '/' . $class_name . '.inc');

        $viewFactory = $this->findView($class_name, $class_file);

        return $viewFactory->createView($view);
    }
}
?>