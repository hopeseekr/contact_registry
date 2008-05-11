<?php
require_once('../config.php');         // Needed for validateSession() and getDB()
session_start();
if (!isset($_SESSION['admin']) || !validateSession())
{
    logout();
    trigger_error('Parameters were not set; invalid login.', E_USER_ERROR);
}
else if (!isAdmin())
{
print '<pre>' . print_r($_SESSION, true) . '</pre>';
    logout();
    trigger_error('You are not classified as an Administrator.', E_USER_ERROR);
}
?>
<html>
    <head>
        <title>
            Customers
        </title>
        <link rel="stylesheet" type="text/css" href="../style.css"/>
    </head>
    <body>
        <div id="header">
            <img src="../reliant_logo.jpg" alt="Reliant Logo"/>
            <h1>Reliant Small Business Customer</h1>
            <div id="topnav">
                <a href="../changePassword.php">Change Password</a> |
                <a href="../logout.php">Logout</a>
            </div>
        </div>
        <h2 style="text-align: center">
            You are logged in as an Administrator
        </h2>
<?php

?>
        <div style="background: #0000FF; padding: 6px 10px 6px 10px; width: 10em">
            <a href="../page.php?view=customers" style="color: yellow">View customers profiles</a>
        </div>
    </body>
</html>