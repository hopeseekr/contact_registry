<?php
require_once('config.php');         // Needed for validateSession() and getDB()
session_start();

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
    if (isAdmin() == true)
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

    if (isAdmin() == true)
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

    if (isset($_POST['deleteP']) && isset($_POST['RollupID']) && isAdmin())
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
<html>
    <head>
        <title>
            Customers
        </title>
        <link rel="stylesheet" type="text/css" href="style.css"/>
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
        <div class="top" id="customer_information">
            <div id= "view_records">
                <form method="get" action ="viewRecords.php">
                    <input type="submit" value="View All Records"/>            
                </form> 
            </div>
<?php
if (isset($_GET['customer_err']))
{
?>
            <div class="err_msg">
                <?php echo $r['err_msg']; ?>
            </div>
<?php
}
?>
            <div class="summary">
<?php
if ($start > 0)
{
    print '<a href="?record=' . ($start - 1) . '">&lt;</a>';
}
?>
                Customer <?php echo ($start + 1); ?> of <?php echo $customerCount; ?> 
<?php
if ($start < $customerCount - 1)
{
    print '<a href="?record=' . ($start + 1) . '">&gt;</a>';
}
?>
            </div>
            <form method="post" action="customer_edit.php">
                <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $customer->RollupID; ?>"/>
                <table>
                    <tr>
                        <th>Rollup ID:</th>
                        <td style="width: 50%"><?php echo $customer->RollupID; ?></td>
                    </tr>
                    <tr>
                        <th>Customer:</th>
                        <td><input type="text" name="CustomerName" value="<?php echo $customer->CustomerName; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><input type="text" name="Address" value="<?php echo $customer->Address; ?>"/></td>
                    </tr>
                    <tr>
                        <th>City:</th>
                        <td><input type="text" name="City" value="<?php echo $customer->City; ?>"/></td>
                    </tr>
                    <tr>
                        <th>State:</th>
                        <td><input type="text" name="State" value="<?php echo $customer->State; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Zip code:</th>
                        <td><input type="text" name="Zipcode" value="<?php echo $customer->Zipcode; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Zip 4:</th>
                        <td><input type="text" name="Zip4" value="<?php echo $customer->Zip4; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Total # by Zip:</th>
                        <td><input type="text" name="NumberOfCompanyByZip" value="<?php echo $customer->NumberOfCompanyByZip; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Meter Qty:</th>
                        <td><input type="text" name="Meters" value="<?php echo $customer->Meters; ?>"/></td>
                    </tr>
                    <tr>
                        <th>LPBID:</th>
                        <td>
<?php
if (!isAdmin())
{
    $disabled2=' disabled="disabled"';
}
?>
                            <select name="LPBID"<?php echo $disabled2; ?>>
                                <?php print constructOptions($customer->LPBTypes, $customer->LPBID); ?>                            
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <th>Segment:</th>
                        <td>
                            <select name="Segment"<?php echo $disabled2; ?>>
                                <?php print constructOptions($customer->Segments, $customer->Segment); ?>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <th>Territory:</th>
                        <td>
                            <select name="Territory"<?php echo $disabled2; ?>>
                                <?php print constructOptions($customer->Territories, $customer->Territory); ?>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" value="Save Record"/></td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="top" id="contract_accounts">
            <div class="summary">
                Contract Accounts
            </div>
<?php
if (isset($_GET['accounts_err']))
{
?>
            <div class="err_msg">
                <?php echo $accounts[1]; ?>
            </div>
<?php
}
?>            
            <table>
<?php
foreach ($accounts[0] as $key => $account)
{
    print '<tr><td>(' . ($key+1).')</td><td>'  . $account . '</td></tr>' . "\n";
}
?>
                <tr>
                    <form method="post" action="customer_addAccount.php">
                        <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                        <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                        <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                        <input type="hidden" name="RollupNumber" value="<?php echo $customer->RollupID; ?>"/>
                        <td>
                            <input type="text" name="ContractAccount"/>
                        </td>
                        <td>
                            <input type="submit" value="Add"/>
                        </td>
                    </form>
                </tr>
            </table>
        </div>
        <br style="clear: both"/><br/>
        <div id="customer_contacts">
            <table style="width: 100%">
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Email</th>
                </tr>
<?php
if (!is_null($contacts['contacts']))
{
    foreach ($contacts['contacts'] as $contact)
    {
?>
                <tr>
                    <form method="post" action="customer_editContact.php">
                        <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                        <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                        <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                        <input type="hidden" name="ContactID" value="<?php echo $contact->ContactID; ?>"/>
                        <td><input type="text" name="ContactFirstName" value="<?php echo $contact->ContactFirstName; ?>"/></td>
                        <td><input type="text" name="ContactLastName" value="<?php echo $contact->ContactLastName; ?>"/></td>
                        <td><input type="text" name="ContactNumber" value="<?php echo $contact->ContactNumber; ?>"/></td>
                        <td>
                            <select name="ContactNumberTypeID">
<?php
        print constructOptions($contacts['types'], $contact->ContactNumberTypeID);
?>
                            </select>
                        </td>                    
                        <td><input type="text" name="ContactEmail" value="<?php echo $contact->ContactEmail; ?>"/></td>
                        <td><input type="submit" value="Edit"/></td>
                    </form>
                </tr>
<?php
    }
}
?>
                <tr>
                    <form method="post" action="customer_addContact.php">
                        <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                        <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                        <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                        <input type="hidden" name="RollupID" value="<?php echo $customer->RollupID; ?>"/>
                        <td><input type="text" name="ContactFirstName"/></td>
                        <td><input type="text" name="ContactLastName"/></td>
                        <td><input type="text" name="ContactNumber"/></td>
                        <td>
                            <select name="ContactNumberTypeID">
<?php
    print constructOptions($contacts['types'], $contact->ContactNumberTypeID);
?>
                            </select>
                        </td>
                        <td><input type="text" name="ContactEmail"/></td>
                        <td><input type="submit" value="Add"/></td>
                    </form>
                </tr>
            </table>
        </div>
        <div id="customer_profile">
            <div class="summary">
<?php
if (isset($_GET['new']))
{
    print 'New Profile';
}
else
{
    if ($pStart > 0)
    {
        print '<a href="?record=' . $_GET['record'] . '&profile=' . ($pStart - 1) . '&note=' . $_GET['note'] . '">&lt;</a>';
    }
    ?>
                Profile <?php echo ($pStart + 1); ?> of <?php echo $profileCount; ?> 
    <?php
    if ($pStart < $profileCount - 1)
    {
        print '<a href="?record=' . $_GET['record'] . '&profile=' . ($pStart + 1) . '&note=' . $_GET['note'] . '">&gt;</a>';
    }
}
?>
            </div>
<?php
if (!isset($_GET['new']))
{
?>
            <div id="addProfile">
                <form method="get" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="new" value="true"/>
                    <input type="submit" value="Create New Profile"/>
                </form>
            </div>
<?php
}
?>
            <form method="post" action="customer_<?php echo isset($_GET['new']) ? 'add' : 'edit'; ?>Profile.php">
                <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                <input type="hidden" name="ProfileID" value="<?php echo $profile->ProfileID; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $profile->RollupID; ?>"/>
                <input type="hidden" name="CreatorID" value="<?php echo $profile->CreatorID; ?>"/>
                <input type="hidden" name="CreationDate" value="<?php echo $profile->CreationDate; ?>"/>
                <table>
                    <tr>
                        <th>Creator</th>
                        <th>Creation Date</th>
                        <th>Called?</th>
                        <th>Visited?</th>
                    </tr>
                    <tr>
                        <td><?php echo $profile->CreatorUsername; ?></td>
                        <td><?php echo $profile->CreationDate; ?></td>
                        <td><input type="checkbox" name="Called"<?php if ($profile->Called) { echo ' checked="checked"'; } ?>/></td>
                        <td><input type="checkbox" name="Visited"<?php if ($profile->Visited) { echo ' checked="checked"'; } ?>/></td>
                    </tr>
                </table>
                <ul>
                    <li>
                        <div>Needs they feel are not currently being met.</div>
                        <textarea name="Question1"><?php echo $profile->Question1; ?></textarea>
                    </li>
                    <li>
                        <div>What they do/do not like about Reliant.</div>
                        <textarea name="Question2"><?php echo $profile->Question2; ?></textarea>
                    </li>
                    <li>
                        <div>How we can better serve them.</div>
                        <textarea name="Question3"><?php echo $profile->Question3; ?></textarea>
                    </li>
                    <li>
                        <div>How they perceive our competitors, etc.</div>
                        <textarea name="Question4"><?php echo $profile->Question4; ?></textarea>
                    </li>
                    <li>
                        <div>Topics they wish to discuss.</div>
                        <textarea name="Question5"><?php echo $profile->Question5; ?></textarea>
                    </li>
                    <li>
                        <div>Products and Services most interested in/signed up for.</div>
                        <textarea name="Question6"><?php echo $profile->Question6; ?></textarea>
                    </li>
                </ul>
<?php
    if (isAdmin())
    {
?>
            <div id="delProfile" style="float: right">
                <form method="get" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="deleteP" value="true"/>
                    <input type="hidden" name="RollupID" value="<?php echo $profile->ProfileID; ?>"/>
                    <input type="submit" value="Delete Profile"/>
                </form>
            </div>
<?php
    }
?>
                <input type="submit" value="<?php echo isset($_GET['new']) ? 'Add' : 'Edit'; ?> Profile"/>
            </form>
        </div>
        <div id="consultant_notes">
            <div class="summary">
<?php
if (isset($_GET['newN']))
{
    print 'New Note';
}
else
{
    if ($nStart > 0)
    {
        print '<a href="?record=' . $_GET['record'] . '&profile=' . $_GET['profile'] . '&note=' . ($nStart - 1) . '">&lt;</a>';
    }
?>
                Note <?php echo ($nStart + 1); ?> of <?php echo $noteCount; ?> 
<?php
    if ($nStart < $noteCount - 1)
    {
        print '<a href="?record=' . $_GET['record'] . '&profile=' . $_GET['profile'] . '&note=' . ($nStart + 1) . '">&gt;</a>';
    }
}
?>
            </div>
<?php
if (!isset($_GET['newN']))
{
?>
            <div id="addNote">
                <form method="get" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="newN" value="true"/>
                    <input type="submit" value="Create New Note"/>
                </form>
            </div>
<?php
}
?>
            <form method="post" action="customer_<?php echo isset($_GET['newN']) ? 'add' : 'edit'; ?>Note.php">
                <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                <input type="hidden" name="NoteID" value="<?php echo $note[0]; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $note[1]; ?>"/>
                <input type="hidden" name="ConsultantID" value="<?php echo $note[2]; ?>"/>
                <input type="hidden" name="CreationDate" value="<?php echo $note[5] ?>"/>
                <table>
                    <tr>
                        <th>Creator</th>
                        <th>Creation Date</th>
                    </tr>
                    <tr>
                        <td><?php echo $note[6]; ?></td>
                        <td><?php echo $note[5] ?></td>
                    </tr>
                </table>
                <p>
                    <div>Notes:</div>
                    <textarea name="Notes"><?php echo $note[3]; ?></textarea>
                </p>
                <input type="submit" value="<?php echo isset($_GET['newN']) ? 'Add' : 'Edit'; ?> Note"/>
            </form>
        </div>
    </body>
</html>