<?php
require_once 'config/Database.php';

class Player
{
    /**
     * Find an existing player or create a new one.
     *
     * @param string $username
     * @return array Details of the player.
     */
    public static function findOrCreate($username)
    {
        $db = Database::connect();

        $query = $db->prepare("SELECT * FROM players WHERE username = :username");
        $query->bindParam(':username', $username);
        $query->execute();
        $player = $query->fetch(PDO::FETCH_ASSOC);

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
