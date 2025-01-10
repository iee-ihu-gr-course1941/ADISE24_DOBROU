<?php
require_once 'models/Player.php';

class PlayerController
{
    /**
     * Log in or create a new player.
     *
     * @param string $username
     * @return array Details of the logged-in player.
     */
    public function login($username)
    {
        $player = Player::findOrCreate($username);
        return [
            'status' => 'success',
            'message' => 'Player logged in successfully.',
            'player' => $player
        ];
    }
}
