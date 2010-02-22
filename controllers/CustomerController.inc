<?php

require_once('controllers/Controller.inc');

class CustomerController extends Controller implements ControllerInterface
{
    private $agent;

    public function execute($action = null)
    {
        xdebug_break();
        if (!isset($_SESSION['agentID']))
        {
            throw new Exception('agentID is not set in the _SESSION vars');
        }

        $params = array('agentID' => $_SESSION['agentID'],
                        'record' => filter_input(INPUT_GET, 'record', FILTER_VALIDATE_INT));

        $this->initialize('customers', $params);
        $action = $this->fetchAction();

        if ($action == null || $action == 'show customer screen')
        {
            $this->showCustomerScreen();
        }
        else
        {
        	// Nothing better for a default ;-)
			$this->showCustomerScreen();
        }
    }

    private function showCustomerScreen()
    {
        $view = $this->view->createView('customers');
        echo $view->parse();
    }
}
