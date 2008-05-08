<?php
require_once('config.php');         // Needed for validateSession() and getDB()
session_start();

ob_start();

require_once('views/CustomerView.inc');
require_once('managers/CustomerManager.inc');

$T = new CustomerView('tpl/customers.tpl');
$surveyor = CustomerManager::getInstance();
$count = $surveyor->getCustomerCount();

$T->set(array('customerCount' => $count));

echo $T->parse();


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

function getCustomersCount($ConsultantID)
{
    if (UserManager::isAdmin() == true)
    {
        $q1s = 'SELECT COUNT(Customers.RollupID) AS CustomerCount FROM Customers';
    }
    else
    {
        $q1s = 'SELECT COUNT(Customers.RollupID) AS CustomerCount ' .
               'FROM AllocationLog INNER JOIN Customers ON AllocationLog.RollupID = Customers.RollupID ' .
               'WHERE ConsultantID=?';
    }

    $dbh = $GLOBALS['dbh'];
    $qs = $dbh->prepare($q1s);
    $qs->execute(array($ConsultantID));

    $count = $qs->fetchColumn($q1q);
    
    return $count;
}

function getCustomerInformation($ConsultantID, $start = 0)
{
    if (isset($_GET['customer_err']) && $_GET['accounts_err'] == 'update failed')
    {
        $err_msg = 'Update of Customer failed: ' . $_GET['reason'] . '.';
    }

    $dbh = $GLOBALS['dbh'];

    if (UserManager::isAdmin() == true)
    {
        $q1s = 'SELECT Customers.* FROM Customers LIMIT ' . $start . ', 1';
        $Segments = array();
        $q2s = 'SELECT SegmentID, SegmentName FROM SegmentTypes';
        $qs = $dbh->query($q2s);
    
        while ($r = $qs->fetchObject())
        {
            $Segments[$r->SegmentID] = $r->SegmentName;
        }
        
        $LPBIDs = array();
        $q3s = 'SELECT LPBID, LPBType FROM LPBTypes';
        $qs = $dbh->prepare($q3s);
        $qs->execute();
    
        while ($r = $qs->fetchObject())
        {
            $LPBIDs[$r->LPBID] = $r->LPBType;
        }
        
        $Territories = array();
        $q4s = 'SELECT TerritoryID, TerritoryName FROM TerritoryTypes';
        $qs = $dbh->prepare($q4s);
        $qs->execute();
    
        while ($r = $qs->fetchObject())
        {
            $Territories[$r->TerritoryID] = $r->TerritoryName;
        }
    
        $qs = $dbh->prepare($q1s);
        $qs->execute(array($ConsultantID));
        $customers = $qs->fetchObject();
    }
    else
    {
        $q1s = 'SELECT Customers.*, ' .
               'SegmentTypes.SegmentName, TerritoryTypes.TerritoryName, LPBTypes.LPBType ' .
               'FROM AllocationLog ' .
               'INNER JOIN Customers ON AllocationLog.RollupID = Customers.RollupID ' .
               'LEFT JOIN SegmentTypes ON SegmentTypes.SegmentID = Customers.Segment ' .
               'LEFT JOIN TerritoryTypes ON TerritoryTypes.TerritoryID = Customers.Territory ' .
               'LEFT JOIN LPBTypes ON LPBTypes.LPBID = Customers.LPBID ' .
               'WHERE ConsultantID=1 ORDER BY Customers.RollupID LIMIT ' . $start . ', 1';

        $qs = $dbh->prepare($q1s);
        $qs->execute(array($ConsultantID));
        $customers = $qs->fetchObject();
    
        $Segments = array($customers->Segment => $customers->SegmentName);
        $Territories = array($customers->Territory => $customers->TerritoryName);
        $LPBIDs = array($customers->LPBID => $customers->LPBType);

        unset($customers->SegmentName);
        unset($customers->TerritoryName);
        unset($customers->LPBType);
    }

    /* --- Reformat $customers --- */
    $customers->Segments = $Segments;
    $customers->Territories = $Territories;
    $customers->LPBTypes= $LPBIDs;

    return array('customer' => $customers,
                 'err_msg' => $err_msg);
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

$customerCount = getCustomersCount($ConsultantID);
$customer_info = getCustomerInformation($ConsultantID, $start);
$customer = $customer_info['customer'];
$accounts = getContractAccounts($customer->RollupID);
$contacts = getCustomerContacts($customer->RollupID);
$profileCount = getProfilesCount($customer->RollupID);
$profile = getCustomerProfile($ConsultantID, $customer->RollupID, $pStart);
$noteCount = getNotesCount($customer->RollupID, $ConsultantID);
$note = getConsultantNote($customer->RollupID, $ConsultantID);
?>
