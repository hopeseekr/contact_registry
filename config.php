<?php

/* USER_AUTH modes:
   - simple: plaintext password, no Active flag, no ExpirationDate flag
   - advanced: encrypted password, Active flag, ExpirationDate flag
*/

define('USER_AUTH', 'simple');

function getMySQLDBCreds()
{
    return array('server' => 'localhost',
                 'user' => 'root',
                 'pass' => '',
                 'database' => 'sbconsultants');
}

/*function getDB()
{
    return getMySQLDBCreds();
}*/

function getSQLiteCreds()
{
    return '/var/www/blinds.com/htdocs/sbconsultants.sdb';
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

function validateUser($user_in, $pass_in)
{
    if ($pass_in != '')
    {
        $pass_cond = 'Password=?';
    }
    else
    {
        $pass_cond = 'Password IS NULL';
    }

    $dbh = $GLOBALS['dbh'];

    if (USER_AUTH == 'simple')
    {
        $qs = 'SELECT AccountTypeID, Password, ConsultantID ' .
              'FROM Consultants WHERE UserName=? AND ' . $pass_cond;
//        print $qs;
    }
    else if (USER_AUTH == 'advanced')
    {
        $pass_in = md5($pass_in);
        $qs = 'SELECT AccountTypeID, Password, ConsultantID, Active, PasswordExpires ' .
              'FROM tblConsultants WHERE UserName=? AND ' . $pass_cond;
    }

    $query = $dbh->prepare($qs);
    if ($query === false)
    {
        print $qs;
        print $dbh->error;
    }
    
    $query->execute(array($user_in, md5($pass_in)));
    $results = $query->fetchObject();

    /* --- For security reasons, only keep the password if it is already NULL --- */
    if ($results->Password != '')
    {
        unset($results->Password);
    }

    /* --- The user has been validated or NULL is returned --- */
    return $results;
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
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1
        && $_SESSION['AccountTypeID'] == 3)
    {
        return true;
    }

    return false;
}
?>
