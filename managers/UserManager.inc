<?php

class UserManager
{
    private function __construct()
    {
    }

    public static function isAdmin()
    {
        if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1 &&
            $_SESSION['AccountTypeID'] == 3)
        {
            return true;
        }
        
        return false;
    }
}
?>