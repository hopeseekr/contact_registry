<?php
ini_set('zend.ze1_compatibility_mode', '0');

function db_connect()
{
    require_once('config.inc');

    /* --- If the cookie differs with the _GET, favor the _GET --- */
    if (isset($_GET['db']))
    {
        $_REQUEST['db'] = $_GET['db'];
    }

    if (!isset($_REQUEST['db']) || $_REQUEST['db'] == 'mysql')
    {
        $db = getMySQLDBCreds();
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s',
                       $db['server'],
                       $db['port'],
                       $db['database']);
        setcookie('db', 'mysql');
    }
    else if ($_REQUEST['db'] == 'mssql')
    {
        //$dsn = 'sybase:host=192.168.1.138,1433;dbname=sbconsultants20080503';
        $dsn = 'dblib:host=192.168.1.138;dbname=SBCustomers20080503';
        $dsn = array('phptype'  => 'mssql',
                     'dbsyntax' => 'mysqli',
                     'hostspec' => '192.168.1.138',
                     'username' => 'sbconsultant_user1',
                     'password' => 'test2008',
                     'database' => 'SBCustomers20080503');
        setcookie('db', 'mssql');
    }
    else if ($_REQUEST['db'] == 'sqlite')
    {
        $dsn = 'sqlite2:' . getSQLiteCreds();
        setcookie('db', 'sqlite');
    }
    else
    {
        trigger_error($_REQUEST['db'] . ' is not a supported database engine.', E_USER_ERROR);
    }

    try
    {
        $options = array('debug' => 2,
                         'portability' => MDB2_PORTABILITY_ALL);
//        $dbh = new mysqli;
//        $dbh->connect($db['server'], $db['user'], $db['pass'], $db['database']);
        $dbh = new PDO($dsn, $db['user'], $db['pass']);

        return $dbh;
    }
    catch (Exception $e)
    {
        print "Error!: " . $e->getMessage() . "<br/>";
        exit;
        die();
    }
}

?>