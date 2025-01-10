<?php
class Database
{
    private static $host = 'localhost';
    private static $port = '3306';
    private static $db_name = 'ataxx';
    private static $username = 'it185404';
    private static $password = '';
    private static $conn;

    public static function connect()
    {
        if (self::$conn == null) {
            self::$conn = new mysqli(self::$host, self::$username, self::$password, self::$db_name, self::$port);

            if (self::$conn->connect_error) {
                die('Database Connection Failed: ' . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }
}
?>
