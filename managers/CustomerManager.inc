<?php
//define('LOGIN_PASSWORD_EXPIRED', 4);

require_once('ComplexManagerInterface.inc');
require_once('DatabaseManager.inc');
require_once('UserManager.inc');

class CustomerManagerFactory
{
    public static function Load()
    {
        if (UserManager::isAdmin() === false)
        {
            return NormalCustomerManager::getInstance();
        }
        else
        {
            return AdminCustomerManager::getInstance();
        }
    }
}

class CustomerManager extends DatabaseManager implements ComplexManagerInterface
{
    private static $instance;
    private $customerCount;
    private $ConsultantID;
    private $recordNumber;
    private $customer;

    protected function __construct($record_in = null)
    {
        DatabaseManager::init();
        $this->ConsultantID = $_SESSION['ConsultantID'];
        $this->recordNumber = $record_in;
    }
    
    public static function load($record_in = null)
    {
        if (!isset(self::$instance))
        {
            self::$instance = new CustomerManager($record_in);
        }

        return self::$instance;
    }

    public function fetchCount()
    {
        if (!is_null($this->customerCount))
        {
            return $this->customerCount;
        }
        
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

        $count = DatabaseManager::fetchColumn($q1s, array($this->ConsultantID));
        $this->customerCount = $count;

        return $count;
    }
    
    public function fetchDetails()
    {
        if (UserManager::isAdmin() == true)
        {
            $Segments = array();
            $q1s = 'SELECT SegmentID, SegmentName FROM SegmentTypes';

            while ($r = DatabaseManager::fetchObject($q1s))
            {
                $Segments[$r->SegmentID] = $r->SegmentName;
            }
            
            $LPBIDs = array();
            $q2s = 'SELECT LPBID, LPBType FROM LPBTypes';

            while ($r = DatabaseManager::fetchObject($q2s))
            {
                $LPBIDs[$r->LPBID] = $r->LPBType;
            }
            
            $Territories = array();
            $q3s = 'SELECT TerritoryID, TerritoryName FROM TerritoryTypes';

            while ($r = DatabaseManager::fetchObject($q3s))
            {
                $Territories[$r->TerritoryID] = $r->TerritoryName;
            }
        
            $q4s = 'SELECT Customers.* FROM Customers LIMIT ' . ($this->recordNumber - 1) . ', 1';
            $customer = DatabaseManager::fetchArray($q4s);
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
                   'WHERE ConsultantID=1 ORDER BY Customers.RollupID LIMIT ' . ($this->recordNumber - 1) . ', 1';

            $customer = DatabaseManager::fetchArray($q1s, array($ConsultantID));

            $Segments = array($customer['Segment'] => $customer['SegmentName']);
            $Territories = array($customer['Territory'] => $customer['TerritoryName']);
            $LPBIDs = array($customer['LPBID'] => $customer['LPBType']);
    
            unset($customer['SegmentName']);
            unset($customer['TerritoryName']);
            unset($customer['LPBType']);
        }
    
        /* --- Reformat $customer --- */
        $customer['Segments'] = $Segments;
        $customer['Territories'] = $Territories;
        $customer['LPBTypes'] = $LPBIDs;

        return $customer;
    }
}

class NormalCustomerManager extends CustomerManager implements ComplexManagerInterface
{
    public function fetchCount()
    {    
        if (($customerCount = parent::getCount()) !== false)
        {
            return $customerCount();
        }
    }
}

class AdminCustomerManager extends CustomerManager implements ComplexManagerInterface
{
}

?>
