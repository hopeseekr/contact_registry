<?php
function expiredPassword()
{
    $_SESSION['expiredPassword'] = true;
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/changePassword.php');
    exit;
}

function login()
{
    if (isset($_POST['username']))
    {
        require_once('lib/db.php');

        db_connect();

        $results = validateUser($_POST['username'], $_POST['password']);

        /* --- The user has been authenticated --- */
        if ($results != null)
        {
            if (USER_AUTH == 'advanced' && $results->active == 0)
            {
                print '<h2>Your account is not active.  Please contact your administrator.</h2>';
                
                return false;
            }

            session_start();

            $_SESSION['username'] = $_POST['username'];
            $_SESSION['ConsultantID'] = $results->ConsultantID;
            $_SESSION['AccountTypeID'] = $results->AccountTypeID;
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

            if ((isset($results->Password) && $results->Password == '') || (USER_AUTH == 'advanced' && strtotime($results->PasswordExpires) < time()))
            {
                expiredPassword();
            }
    
            if ($results->AccountTypeID == 2)
            {
                header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php');
                exit;
            }
            else if ($results->AccountTypeID == 3)
            {
                $_SESSION['admin'] = true;
                header("Location: http://" . $_SERVER['HTTP_HOST'] . '/admin/');
                exit;
            }
            else
            {
                trigger_error($_POST['username'] . ' has an invalid Account Type of ' . $results->AccountTypeID, E_USER_ERROR);
            }
        }
        else
        {
            print '<h1>Login failed!!</h1>';
            return false;
        }
    }
}

login();
?>
<html>
    <head>
        <title>Login | SBConsultants</title>
    </head>
    <body>
        <h2>Consultant Login</h2>
        <form method="post">
            <table style="border: 0">
                <tr>
                    <th>Username:</th>
                    <td><input type="text" id="username" name="username" style="width: 200px" value="<?php echo $_POST['username']; ?>"/></td>
                </tr>
                <tr>
                    <th>Password:</th>
                    <td><input type="password" id="password" name="password" style="width: 200px"/></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="submit" value="Log in" style="width: 100px"/>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
