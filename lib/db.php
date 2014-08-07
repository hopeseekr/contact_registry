<?php
ini_set('zend.ze1_compatibility_mode', '0');

function db_connect()
{
    require_once('config.php');
    
    /* --- If the cookie differs with the _GET, favor the _GET --- */
    if (isset($_GET['db']))
    {
        $_REQUEST['db'] = $_GET['db'];
    }

    if (!isset($_REQUEST['db']) || $_REQUEST['db'] == 'mysql')
    {
        $db = getMySQLDBCreds();
        $dsn = sprintf('mysql:host=%s;dbname=%s',
                       $db['server'],
                       $db['database']);
        setcookie('db', 'mysql');
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
        $dbh = new PDO($dsn, $db['user'], $db['pass']);
        $GLOBALS['dbh'] = $dbh;

        return $dbh;
    }
    catch (PDOException $e)
    {
        print "Error!: " . $e->getMessage() . "<br/>";
        exit;
        die();
    }
}
?>