<?php
class Database
{
    private static $host = 'localhost';
    private static $db_name = 'ataxx';
    private static $username = 'it185404';
    private static $password = '';
    private static $conn;

    public static function connect()
    {
        if (self::$conn === null) {
            if (gethostname() == 'users.iee.ihu.gr') {
                // University server configuration with socket
                self::$conn = new mysqli(
                    self::$host,
                    self::$username,
                    self::$password,
                    self::$db_name,
                    null,
                    '/home/student/it/2018/it185404/mysql/run/mysql.sock'
                );
            } else {
                self::$conn = new mysqli(
                    self::$host,
                    self::$username,
                    self::$password,
                    self::$db_name
                );
            }

            if (self::$conn->connect_error) {
                die("Database Connection Failed: " . self::$conn->connect_error);
            }
        }

        return self::$conn;
    }
}
?>
