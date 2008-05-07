<?php
require_once('config.php');         // Needed for validateSession() and getDB()
session_start();
ob_start();

if (!isset($_SESSION['ConsultantID']) || !validateSession())
{
    logout();
    trigger_error('Parameters were not set; invalid login.', E_USER_ERROR);
}

?>
<html>
    <head>
        <title>Change Consultant's Password</title>
    </head>
    <body>
        <h2>Reset your password</h2>
<?php
if (isset($_POST['password']))
{
    $db = getDB();
    
    mysql_connect($db['server'], $db['user'], $db['pass']);
    mysql_select_db($db['database']);
    print mysql_error();

    if (strlen($_POST['password'][0]) < MIN_PASSWORD_SIZE)
    {
        $err_msg[] = 'Password must be at least ' . MIN_PASSWORD_SIZE . ' characters long.';
    }

    if (!isset($_SESSION['expiredPassword']) || $_SESSION['expiredPassword'] == false)
    {
        if (validateUser($_SESSION['username'], $_POST['current']) == null)
        {
            $err_msg[] = 'The current password entered is invalid.';
        }
    }

    if ($_POST['password'][0] != $_POST['password'][1])
    {
        $err_msg[] = 'Both of the passwords do not match.';
    }

    if (!isset($err_msg))
    {
        /* --- Every thing looks good to go; change the pass --- */        
        $ConsultantID = $_SESSION['ConsultantID'];
        $qs = sprintf('UPDATE tblconsultants SET Password=PASSWORD("%s"), ' .
                      'PasswordExpires=DATE_ADD(NOW(), INTERVAL 180 DAY) ' .
                      'WHERE ConsultantID=%d',
                      $_POST['password'][0],
                      $ConsultantID);
        $qq = mysql_query($qs);
        
        if ($qq === false)
        {
            print mysql_error();
        }

        print '<h4>Your password has been successfully reset.  Please <a href="login.php">log in</a> with your new password to continue.</h4>';
        logout();
        exit;
    }
}

if (isset($err_msg))
{
    foreach ($err_msg as $msg)
    {
        print '<h4 style="color: red">' . $msg . '</h4>';
    }
}
?>
        <form method="post">
            <table>
<?php
if (!isset($_SESSION['expiredPassword']) || $_SESSION['expiredPassword'] == false)
{
?>
                <tr>
                    <td>Current Password:</td>
                    <td><input type="password" name="current" style="width: 150px"/></td>
                </tr>

<?php
}
?>
                <tr>
                    <td>New Password:</td>
                    <td><input type="password" name="password[]" style="width: 150px"/></td>
                </tr>
                <tr>
                    <td>Password (again):</td>
                    <td><input type="password" name="password[]" style="width: 150px"/></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value="Change Password"/></td>
                </tr>
            </table>
        </form>
    </body>
</html>