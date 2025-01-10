<?php
class Database
{
    private static $host = '127.0.0.1';
    private static $port = '3333';
    private static $db_name = 'ataxx';
    private static $username = 'it185404';
    private static $password = '';
    private static $conn;

    public static function connect()
    {
        if (self::$conn == null) {
            try {
                self::$conn = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$db_name,
                    self::$username,
                    self::$password
                );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Database Connection Failed: ' . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
