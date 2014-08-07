<?php
session_start();

ob_start();

require_once('config.inc');
require_once('factories/ControllerFactory.inc');

$views = array('login' => true,
               'customers' => true);

if (!isset($_GET['view']))
{
    throw new Exception('No view parameter passed');
}
else
{
    if (!isset($views[$_GET['view']]) || $views[$_GET['view']] === false)
    {
        throw new Exception('Unsupported view');
    }

    $controller = $_GET['view'];
}

$controllerFactory = new ControllerFactory();
$c = $controllerFactory->loadController($controller);
$c->execute();
