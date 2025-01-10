<?php
require_once 'config/Database.php';

class Player
{
    public static function findOrCreate($username)
    {
        $db = Database::connect();

        // Check if player exists
        $query = $db->prepare("SELECT * FROM players WHERE username = :username");
        $query->bindParam(':username', $username);
        $query->execute();
        $player = $query->fetch(PDO::FETCH_ASSOC);

        // Create player if not found
        if (!$player) {
            $insert = $db->prepare("INSERT INTO players (username) VALUES (:username)");
            $insert->bindParam(':username', $username);
            $insert->execute();

            return [
                'id' => $db->lastInsertId(),
                'username' => $username
            ];
        }
        return $player;
    }
}
