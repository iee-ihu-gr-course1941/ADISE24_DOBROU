<?php
require_once 'models/Player.php';

class PlayerController
{
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
