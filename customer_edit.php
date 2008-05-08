<?php
if (!isset($_POST['RollupID']) || !isset($_POST['CustomerName']))
{
    $err = 'Parameters were not set; invalid session.';
    print "<h2>$err</h2>";
    trigger_error($err, E_USER_ERROR);
    exit;
}
session_start();
require_once('config.php');

require_once('lib/db.php');
db_connect();

$q1s = sprintf('UPDATE tblcustomers SET CustomerName="%s", Address="%s", City="%s", ' .
               'State="%s", Zipcode="%s", Zip4="%s", NumberOfCompanyByZip=%d, Meters=%d ' .
               'WHERE RollupID=%d',
               mysql_real_escape_string($_POST['CustomerName']),
               mysql_real_escape_string($_POST['Address']),
               mysql_real_escape_string($_POST['City']),
               mysql_real_escape_string($_POST['State']),
               mysql_real_escape_string($_POST['Zipcode']),
               mysql_real_escape_string($_POST['Zip4']),
               mysql_real_escape_string($_POST['NumberOfCompanyByZip']),
               mysql_real_escape_string($_POST['Meters']),
               mysql_real_escape_string($_POST['RollupID']));

if (isAdmin() == true)
{
    $q1s = sprintf('UPDATE tblcustomers SET CustomerName="%s", Address="%s", City="%s", ' .
                   'State="%s", Zipcode="%s", Zip4="%s", NumberOfCompanyByZip=%d, Meters=%d, ' .
                   'LPBID=%d, Segment=%d, Territory=%d ' .
                   'WHERE RollupID=%d',
                   mysql_real_escape_string($_POST['CustomerName']),
                   mysql_real_escape_string($_POST['Address']),
                   mysql_real_escape_string($_POST['City']),
                   mysql_real_escape_string($_POST['State']),
                   mysql_real_escape_string($_POST['Zipcode']),
                   mysql_real_escape_string($_POST['Zip4']),
                   mysql_real_escape_string($_POST['NumberOfCompanyByZip']),
                   mysql_real_escape_string($_POST['Meters']),
                   mysql_real_escape_string($_POST['LPBID']),
                   mysql_real_escape_string($_POST['Segment']),
                   mysql_real_escape_string($_POST['Territory']),
                   mysql_real_escape_string($_POST['RollupID']));
}               

$params  = constructParams();

if (mysql_query($q1s) === false)
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=' . urlencode(mysql_error()) . "&$params");
}
else
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php' . "?$params");
}
?>