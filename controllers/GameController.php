<?php
require_once 'models/Game.php';

class GameController
{
    public function listActiveGames($playerId)
    {
        $games = Game::getActiveGames($playerId);
        if (empty($games)) {
            return [
                'status' => 'error',
                'message' => 'No active games found for this player.'
            ];
        }

        return [
            'status' => 'success',
            'games' => $games
        ];
    }

    public function startGame($bluePlayerId, $redPlayerId)
    {
        $gameId = Game::create($bluePlayerId, $redPlayerId);
        Game::initializeBoard($gameId);

        return [
            'status' => 'success',
            'message' => 'Game started successfully.',
            'game_id' => $gameId
        ];
    }

    public function makeMove($gameId, $playerId, $from, $to)
    {
        require_once 'models/Game.php';
        require_once 'models/MoveValidator.php';

        try {
            // Use getFlatBoard instead of getBoard
            $board = Game::getFlatBoard($gameId);

            $currentTurn = Game::getCurrentTurn($gameId);
            $gameData = Game::getGameData($gameId);

            error_log("CurrentTurn=$currentTurn, PlayerID=$playerId, BluePlayer=" . $gameData['blue_player'] . ", RedPlayer=" . $gameData['red_player']);
            if (
                ($currentTurn === 1 && $gameData['blue_player'] != $playerId) ||
                ($currentTurn === 2 && $gameData['red_player'] != $playerId)
            ) {
                error_log("Turn validation failed: Not your turn!");
                return ['status' => 'error', 'message' => 'Not your turn!'];
            }
            error_log("Turn validation passed: It's the player's turn.");


            $moveType = MoveValidator::isValidMove($from, $to, $board, $playerId);

            if (!$moveType) {
                return ['status' => 'error', 'message' => 'Invalid move!'];
            }

            Game::executeMove($gameId, $from, $to, $moveType, $playerId);

            $winner = Game::checkEndgame($gameId);
            if ($winner !== false) {
                $message = ($winner == 0) ? 'The game is a draw!' : "Player $winner wins!";
                return ['status' => 'success', 'message' => $message];
            }

            Game::switchTurn($gameId);

            return ['status' => 'success', 'message' => 'Move executed successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function showBoard($gameId)
    {
        $board = Game::getBoard($gameId);
        $renderedBoard = Game::renderBoard($board);
        return ['status' => 'success', 'board' => $renderedBoard];
    }
}
