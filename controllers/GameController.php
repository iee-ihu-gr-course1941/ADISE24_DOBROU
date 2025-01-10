<?php
require_once 'models/Game.php';

class GameController
{
    /**
     * List all active games for a player.
     *
     * @param int $playerId
     * @return array An array with the list of active games or an error message.
     */
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

    /**
     * Start a new game with two players.
     *
     * @param int $bluePlayerId
     * @param int $redPlayerId
     * @return array Details of the new game.
     */
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

    /**
     * Make a move in the game.
     *
     * @param int $gameId
     * @param int $playerId
     * @param string $from
     * @param string $to
     * @return array Result of the move.
     */
    public function makeMove($gameId, $playerId, $from, $to)
    {
        require_once 'models/MoveValidator.php';

        try {
            $board = Game::getFlatBoard($gameId);

            $currentTurn = Game::getCurrentTurn($gameId);
            $gameData = Game::getGameData($gameId);

            if (
                ($currentTurn === 1 && $gameData['blue_player'] != $playerId) ||
                ($currentTurn === 2 && $gameData['red_player'] != $playerId)
            ) {
                return ['status' => 'error', 'message' => 'Not your turn!'];
            }

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

    /**
     * Show the current state of the game board.
     *
     * @param int $gameId
     * @return array Rendered board or an error message.
     */
    public function showBoard($gameId)
    {
        $board = Game::getBoard($gameId);
        $renderedBoard = Game::renderBoard($board);
        return ['status' => 'success', 'board' => $renderedBoard];
    }
}
