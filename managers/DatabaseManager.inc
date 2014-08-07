<?php
require_once('lib/db.php');

class DatabaseManager
{
    private static $instance;
    private static $dbh;

    public function __construct()
    {
        if (isset(self::$instance))
        {
            return self::$instance;
        }

        self::$instance = $this;

        self::$dbh = db_connect();
    }

    public static function init()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new DatabaseManager;
        }
    }

    /* $sql is either a plaintext SQL query or an array (part1, part2, ...) */
    private static function prep($sql_in, $params_in)
    {
        $sql = $sql_in;
        if (is_array($sql))
        {
            $sql = join(' ', $sql_in);
        }

        $query = self::$dbh->prepare($sql);
        $query->execute($params_in);

        if ($query === FALSE)
        {
            throw new Exception('Query was not completed successfully: ' . $sql);
        }

        return $query;
    }

    public static function fetchColumn($sql_in, $params_in = null)
    {
        $query = self::prep($sql_in, $params_in);
        $result = $query->fetchColumn();

        return $result;
    }

    public static function fetchObject($sql_in, $params_in = null)
    {
        $query = self::prep($sql_in, $params_in);
        $results = $query->fetchObject();

        return $results;
    }

    public static function fetchArray($sql_in, $params_in = null)
    {
        $query = self::prep($sql_in, $params_in);
        $results = $query->fetch(PDO::FETCH_ASSOC);

        return $results;
    }
}

?>