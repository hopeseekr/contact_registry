<?php
require_once('lib/db.php');

class DatabaseController
{
    private static $instance;
    protected $dbh;
    public $id;
    
    protected function __construct()
    {
        $this->id = rand(5, 2000); 
        $this->dbh = db_connect();
    }
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new LoginController;
        }

        return self::$instance;
    }
}
?>