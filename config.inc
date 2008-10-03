<?php

/* USER_AUTH modes:
   - simple: plaintext password, no Active flag, no ExpirationDate flag
   - advanced: encrypted password, Active flag, ExpirationDate flag
*/

//define('USER_AUTH', 'simple');
define('USER_AUTH', 'advanced');
define('APP_URI', '/sbconsultants');

function getMySQLDBCreds()
{
    return array('server' => '127.0.0.1',
                 'port' => '3307',
                 'user' => 'sbuser',
                 'pass' => 'AKGhDK3',
                 'database' => 'sbconsultants');
}

function constructParams()
{
    $params  = '';
    $params .= $_POST['record'] == 0 ? '' : 'record=' . $_POST['record'] . '&';
    $params .= $_POST['profile'] == 0 ? '' : 'profile=' . $_POST['profile'] . '&';
    $params .= $_POST['note'] == 0 ? '' : 'note=' . $_POST['note'];

    return $params;
}

function validateSession()
{
    if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'])
    {
        return false;
    }

    return true;
}

function logout()
{
    $_SESSION = array();

    if (isset($_COOKIE[session_name()]))
    {
        setcookie(session_name(), '', time()-42000, '/');
    }
    
    session_destroy();
}

function printHeader($title_in)
{
?>
<html>
    <head>
        <title><?php echo $title_in; ?></title>
        <link rel="stylesheet" type="text/css" href="style.css"/>
        <!--
            AUTHORS:
                Maria Ortiz - Database schema designer/creator;
                              creator of MS Access front-end;
                              system architect;
                              senior developer
                Theodore R. Smith - port of DB schema from MS Access to MySQL;
                              migration from MS Access front- and backend to
                              Apache, PHP, and MySQL;
                              HTML+CSS implementation
        -->
    </head>
    <body>
        <div id="header">
            <img src="reliant_logo.jpg" alt="Reliant Logo"/>
            <h1>Reliant Small Business Customer</h1>
            <div id="topnav">
                <a href="changePassword.php">Change Password</a> |
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <br style="clear: both"/>
<?php
}

if (!defined('MIN_PASSWORD_SIZE'))
{
    define('MIN_PASSWORD_SIZE', 6);
}

function isAdmin()
{
    trigger_error('Use UserManager::isAdmin() instead.', E_USER_WARNING);
}
