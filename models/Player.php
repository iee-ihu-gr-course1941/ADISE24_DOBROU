<?php
require_once 'config/Database.php';

class Player
{
    public static function findOrCreate($username)
    {
        $db = Database::connect();

        // Check if player exists
        $query = $db->prepare("SELECT * FROM players WHERE username = ?");
        $query->bind_param('s', $username);
        $query->execute();
        $result = $query->get_result();
        $player = $result->fetch_assoc();

        if (!$player) {
            // Insert new player
            $insert = $db->prepare("INSERT INTO players (username) VALUES (?)");
            $insert->bind_param('s', $username);
            $insert->execute();

            return [
                'id' => $db->insert_id,
                'username' => $username
            ];
        }

        return $player;
    }
}
?>
