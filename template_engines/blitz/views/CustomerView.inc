<?php
require_once('managers/CustomerManager.inc');

class CustomerView extends Blitz
{
    public function __construct()
    {
        $tmpl_file = dirname(__FILE__) . '/../tpl/customers.tpl';

        if (!file_exists($tmpl_file))
        {
            throw new Exception('Cannot find Blitz template file: ' . $tmpl_file);
        }

        $status = parent::Blitz($tmpl_file);
        $this->registerUser();
        $this->set(array('title' => 'Customers'));
        $this->handleRawHTML();

        $record = 1;
        if (isset($_GET['record']) && is_numeric($_GET['record']))
        {
            $record = $_GET['record'];
        }

        $surveyor = CustomerManager::load($record);

        $count = $surveyor->fetchCount();
        $customer = $surveyor->fetchDetails();

        if ($record > 1)
        {
            $this->block('prevRecord', array('id' => $record - 1));
        }

        if ($record < $count)
        {
            $this->block('nextRecord', array('id' => $record + 1,
                                             'customerCount' => $count));
        }
        $customer['LPBTypes'][2] = 'LPB2';

        /* --- Populate lists --- */
//        $this->block('customer/LPBIDs', array('id' => 5));
        $this->set(array('record' => $record,
                         'customerCount' => $count));
        $this->block('customer', $customer);

//$this->block('debug', array('print_r' => print_r($customer, true)));
        foreach ($customer['LPBTypes'] as $id => $type)
        {
            $params = array('id' => $id, 'type' => $type);

            $this->block('customer/LPBIDs/LPB', $params);

            if ($id == $customer['LPBID'])
            {
                $this->block('customer/LPBIDs/LPB/selected');
            }
        }

//        $this->block('debug', array('print_r' => print_r($_SESSION, true)));

        if (isset($_GET['customer_err']) && $_GET['accounts_err'] == 'update failed')
        {
            $this->block('genericError', array($_GET['reason']));
        }

        return $status;
    }

    public function registerUser()
    {
        $user = '';
        if (isset($_REQUEST['username']))
        {
            $user = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_STRING);
        }

        $this->set(array('username' => $user));
        return $user;
    }

    public function handleRawHTML()
    {
        /* --- See if there is non-templated output. --- */
        if (ob_get_length() > 0)
        {
            trigger_error('There is raw, non-templated HTML output!', E_USER_NOTICE);
            $rawHTML = ob_get_clean();
            // Restart ob_start()
            ob_start();

            $this->block('rawHTML', array('rawHTML' => $rawHTML));
        }
    }

    public function handleChangePassword()
    {
        $_SESSION['expiredPassword'] = true;
        header("Location: https://" . $_SERVER['HTTP_HOST'] . '/changePassword.php');
        exit;
    }

    public function handleLoginFailure($err_msg = null)
    {
        $this->block('loginFailed', array('err_msg' => $err_msg));
    }

    public function handleLoginSuccess()
    {
        if (!isset($_SESSION['AccountTypeID']) || !is_numeric($_SESSION['AccountTypeID']))
        {
            throw new Exception('AccountTypeID not passed in the _SESSION array.');
        }

        $accountTypeID = $_SESSION['AccountTypeID'];

        if ($accountTypeID == 2)
        {
            header("Location: https://" . $_SERVER['HTTP_HOST'] . '/customers.php');
            exit;
        }
        else if ($accountTypeID == 3)
        {
            $_SESSION['admin'] = true;
            header("Location: https://" . $_SERVER['HTTP_HOST'] . '/admin/');
            exit;
        }
    }
}
?>