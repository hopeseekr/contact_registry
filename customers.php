<?php
require_once('config.php');         // Needed for validateSession() and getDB()
session_start();

ob_start();

require_once('views/ViewFactory.inc');

$viewFactory = new ViewFactory('blitz');
//$T = $viewFactory->createView('Customer');
require_once('template_engines/blitz/views/CustomerView.inc');

$T = new CustomerView();

echo $T->parse();
exit;

if (!isset($_SESSION['ConsultantID']) || !validateSession())
{
    logout();
    trigger_error('Parameters were not set; invalid login.', E_USER_ERROR);
}

$ConsultantID = $_SESSION['ConsultantID'];

require_once('lib/db.php');
db_connect();

if (isset($_GET['record']) && is_numeric($_GET['record']))
{
    $start = $_GET['record'];
}
else
{
    $start = 0;
}

/* --- Due to a quirk in DB design, RollupID is called RollupNumber in ContractAccounts --- */
function getContractAccounts($RollupID)
{
    if (isset($_GET['accounts_err']) && $_GET['accounts_err'] == 'insert failed')
    {
        $err_msg = 'Insert of Contract Account ' . $_GET['ContractAccount'] . ' failed: ' . $_GET['reason'] . '.';
    }

    $dbh = $GLOBALS['dbh'];
    $q1s = 'SELECT ContractAccount FROM ContractAccounts WHERE RollupNumber=?';
    $qs = $dbh->prepare($q1s);
    $qs->execute(array($RollupID));
    $accounts = array();
    
    while ($r = $qs->fetchObject())
    {
        $accounts[] = $r->ContractAccount;
    }
    
    return array($accounts, $err_msg);
}

function getCustomerContacts($RollupID)
{
    $dbh = $GLOBALS['dbh'];
    $q1s = 'SELECT ContactNumberTypeID, ContactNumberType FROM ContactNumberTypes';
    $qs = $dbh->prepare($q1s);
    $qs->execute();
    
    while ($r = $qs->fetchObject())
    {
        $types[$r->ContactNumberTypeID] = $r->ContactNumberType;
    }
    
    $q2s = 'SELECT ContactID, ContactFirstName, ContactLastName, ContactNumber, ' .
           'CustomerContacts.ContactNumberTypeID, ContactNumberType, ContactEmail, ' .
           'RecordDate FROM CustomerContacts ' .
           'LEFT JOIN ContactNumberTypes USING(ContactNumberTypeID) ' .
           'WHERE RollupID=?';
    $qs = $dbh->prepare($q2s);
    $qs->execute(array($RollupID));

    while ($r = $qs->fetchObject())
    {
        $contacts[$r->ContactID] = $r;
    }

    return array('types' => $types,
                 'contacts' => $contacts);
}

function constructOptions($values, $needle = null)
{
    $options = '';
    foreach ($values as $id => $value)
    {
        if ($id == $needle) { $sel = " selected=\"selected\""; } else { $sel = ""; }
        $options .= sprintf('<option value="%d"%s>%s</option>' . "\n", $id, $sel, $value);
    }
    
    return $options;
}

if (isset($_GET['profile']) && is_numeric($_GET['profile']))
{
    $pStart = $_GET['profile'];
}
else
{
    $pStart = 0;
}

function getProfilesCount($RollupID)
{
    $dbh = $GLOBALS['dbh'];
    $q1s = 'SELECT COUNT(RollupID) AS CustomerCount FROM Profiles ' .
           'WHERE RollupID=?';
    $qs = $dbh->prepare($q1s);
    $qs->execute(array($RollupID));
    
    $count = $qs->fetchColumn();

    return $count;
}

function getCustomerProfile($ConsultantID, $RollupID, $pStart = 0)
{
    $dbh = $GLOBALS['dbh'];

    if (isset($_POST['deleteP']) && isset($_POST['RollupID']) && UserManager::isAdmin())
    {
        $qs = 'DELETE FROM Profiles WHERE RollupID=' . mysql_real_escape_string($_POST['RollupID']);
//        $qs = $dbh->prepare($qs);
        print $qs;
        exit;
        if ($qq === false)
        {
//            header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Profile+could+not+be+deleted' . "&$params");
        }

//        header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Profile+deleted' . "&$params");
    }

    if (!isset($_GET['new']))
    {
        $q1s = 'SELECT *, UserName AS CreatorUsername FROM Profiles ' .
               'JOIN Consultants ON ConsultantID=CreatorID WHERE RollupID=? ' .
               'LIMIT ' . $pStart . ', 1';
        $qs = $dbh->prepare($q1s);
        $qs->execute(array($RollupID));

        /* There should only ever be 1 profile per customer. */
        $profile = $qs->fetchObject();
        
        if (is_object($profile))
        {            
            return $profile;
        }
    }    

    $_GET['new'] = true;

    $q2s = 'SELECT UserName FROM Consultants WHERE ConsultantID=?';
    $qs = $dbh->prepare($q2s);
    $qs->execute(array($ConsultantID));
    $username = $qs->fetchColumn();

    $profile = new stdClass;
    $profile->RollupID = $RollupID;
    $profile->CreationDate = 'null';
    $profile->CreatorID = $ConsultantID;
    $profile->Called = false;
    $profile->Visited = false;
    $profile->Question1 = '';
    $profile->Question2 = '';
    $profile->Question3 = '';
    $profile->Question4 = '';
    $profile->Question5 = '';
    $profile->Question6 = '';
    $profile->CreatorUsername = $username;

    return $profile;
}

if (isset($_GET['note']) && is_numeric($_GET['note']))
{
    $nStart = mysql_real_escape_string($_GET['note']);
}
else
{
    $nStart = 0;
}

function getNotesCount($RollupID, $ConsultantID)
{
    $dbh = $GLOBALS['dbh'];
    $q1s = 'SELECT COUNT(RollupID) FROM ConsultantNotes ' .
           'WHERE RollupID=? AND ConsultantID=?';
    $qs = $dbh->prepare($q1s);
    $qs->execute(array($RollupID, $ConsultantID));
    $count = $qs->fetchColumn();

    return $count;    
}

function getConsultantNote($RollupID, $ConsultantID, $nStart = 0)
{
    $dbh = $GLOBALS['dbh'];

    if (!isset($_GET['newN']))
    {
        $q1s = 'SELECT ConsultantNotes.*, UserName AS CreatorUsername FROM ConsultantNotes ' .
               'JOIN Consultants USING(ConsultantID) ' .
               'WHERE RollupID=? AND ConsultantNotes.ConsultantID=? ' .
               'LIMIT ' . $nStart . ', 1';
        $qs = $dbh->prepare($q1s);
        $qs->execute(array($RollupID, $ConsultantID));

        /* There should only ever be 1 profile per query. */
        $note = $qs->fetchObject();
        
        if (is_array($note))
        {
            return $note;
        }
    }

    $_GET['newN'] = true;

    $q2s = 'SELECT UserName FROM Consultants WHERE ConsultantID=?';
    $qs = $dbh->prepare($q2s);
    $qs->execute(array($ConsultantID));
    $username = $qs->fetchColumn();

    $note = array();
    $note[1] = $RollupID;
    $note[5] = 'null';
    $note[2] = $ConsultantID;
    $note[3] = '';
    $note[6] = $username;

    return $note;
}

$customer = $customer_info['customer'];
$accounts = getContractAccounts($customer->RollupID);
$contacts = getCustomerContacts($customer->RollupID);
$profileCount = getProfilesCount($customer->RollupID);
$profile = getCustomerProfile($ConsultantID, $customer->RollupID, $pStart);
$noteCount = getNotesCount($customer->RollupID, $ConsultantID);
$note = getConsultantNote($customer->RollupID, $ConsultantID);
?>
