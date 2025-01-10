<?php
require_once 'config/Database.php';

class Game
{
    public static function create($bluePlayerId, $redPlayerId)
    {
        $db = Database::connect();

        $query = $db->prepare("
            INSERT INTO games (blue_player, red_player, current_turn, status) 
            VALUES (:blue_player, :red_player, :current_turn, :status)
        ");
        $query->execute([
            ':blue_player' => $bluePlayerId,
            ':red_player' => $redPlayerId,
            ':current_turn' => 1,
            ':status' => 'active'
        ]);

        return $db->lastInsertId();
    }

    public static function initializeBoard($gameId)
    {
        $db = Database::connect();

        $initialPositions = [
            ['A1', 1],
            ['G7', 1],
            ['A7', 2],
            ['G1', 2]
        ];

        $query = $db->prepare("INSERT INTO state (game_id, position, occupant) VALUES (:game_id, :position, :occupant)");

        for ($row = 1; $row <= 7; $row++) {
            for ($col = 1; $col <= 7; $col++) {
                $position = chr(64 + $row) . $col;
                $occupant = 0;
                $query->execute([
                    ':game_id' => $gameId,
                    ':position' => $position,
                    ':occupant' => $occupant
                ]);
            }
        }

        $updateQuery = $db->prepare("UPDATE state SET occupant = :occupant WHERE game_id = :game_id AND position = :position");
        foreach ($initialPositions as $pos) {
            $updateQuery->execute([
                ':game_id' => $gameId,
                ':position' => $pos[0],
                ':occupant' => $pos[1]
            ]);
        }
    }

    public static function getActiveGames($playerId)
    {
        $db = Database::connect();

        $query = $db->prepare("
            SELECT id, blue_player, red_player, current_turn, status
            FROM games
            WHERE (blue_player = :player_id OR red_player = :player_id) AND status = 'active'
        ");
        $query->execute([':player_id' => $playerId]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function renderBoard($board)
    {
        $output = '';
        foreach ($board as $row) {
            foreach ($row as $cell) {
                $output .= ($cell == 0 ? '.' : ($cell == 1 ? 'B' : 'R')) . ' ';
            }
            $output .= PHP_EOL;
        }
        return $output;
    }

    public static function getFlatBoard($gameId)
    {
        $db = Database::connect();
        $query = $db->prepare("SELECT position, occupant FROM state WHERE game_id = :game_id");
        $query->execute([':game_id' => $gameId]);

        return $query->fetchAll(PDO::FETCH_KEY_PAIR); // Returns associative array like ['A1' => 1, 'B2' => 0, ...]
    }

    public static function getGameData($gameId)
    {
        $db = Database::connect();
        $query = $db->prepare("SELECT * FROM games WHERE id = :game_id");
        $query->execute([':game_id' => $gameId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function getBoard($gameId)
    {
        $db = Database::connect();
        $query = $db->prepare("SELECT position, occupant FROM state WHERE game_id = :game_id");
        $query->execute([':game_id' => $gameId]);

        $result = $query->fetchAll(PDO::FETCH_KEY_PAIR);

        $board = [];
        foreach ($result as $position => $occupant) {
            $row = ord($position[0]) - 65;
            $col = intval($position[1]) - 1;
            $board[$row][$col] = $occupant;
        }

        return $board;
    }

    public static function executeMove($gameId, $from, $to, $moveType, $currentPlayer)
    {
        $db = Database::connect();

        $db->beginTransaction();
        try {
            if ($moveType === 'jump') {
                $query = $db->prepare("UPDATE state SET occupant = 0 WHERE game_id = :game_id AND position = :position");
                $query->execute([
                    ':game_id' => $gameId,
                    ':position' => $from
                ]);
            }

            $query = $db->prepare("UPDATE state SET occupant = :occupant WHERE game_id = :game_id AND position = :position");
            $query->execute([
                ':occupant' => $currentPlayer,
                ':game_id' => $gameId,
                ':position' => $to
            ]);

            self::flipAdjacentPieces($gameId, $to, $currentPlayer);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private static function flipAdjacentPieces($gameId, $position, $currentPlayer)
    {
        $db = Database::connect();
        $adjacentPositions = self::getAdjacentPositions($position);

        foreach ($adjacentPositions as $adjPos) {
            $query = $db->prepare("SELECT occupant FROM state WHERE game_id = :game_id AND position = :position");
            $query->execute([
                ':game_id' => $gameId,
                ':position' => $adjPos
            ]);
            $occupant = $query->fetchColumn();

            if ($occupant && $occupant !== $currentPlayer) {
                $query = $db->prepare("UPDATE state SET occupant = :occupant WHERE game_id = :game_id AND position = :position");
                $query->execute([
                    ':occupant' => $currentPlayer,
                    ':game_id' => $gameId,
                    ':position' => $adjPos
                ]);
            }
        }
    }

    public static function switchTurn($gameId)
    {
        $db = Database::connect();

        $query = $db->prepare("SELECT current_turn FROM games WHERE id = :game_id");
        $query->execute([':game_id' => $gameId]);
        $currentTurn = $query->fetchColumn();

        $nextTurn = ($currentTurn == 1) ? 2 : 1;
        error_log("Switching turn: CurrentTurn=$currentTurn, NextTurn=$nextTurn");

        $query = $db->prepare("UPDATE games SET current_turn = :next_turn WHERE id = :game_id");
        $query->execute([
            ':next_turn' => $nextTurn,
            ':game_id' => $gameId
        ]);
    }

    public static function getAdjacentPositions($position)
    {
        $row = ord($position[0]) - ord('A');
        $col = intval($position[1]) - 1;

        $directions = [
            [-1, -1],
            [-1, 0],
            [-1, 1],
            [0, -1],
            [0, 1],
            [1, -1],
            [1, 0],
            [1, 1],
        ];

        $adjacentPositions = [];
        foreach ($directions as $dir) {
            $newRow = $row + $dir[0];
            $newCol = $col + $dir[1];
            if ($newRow >= 0 && $newRow < 7 && $newCol >= 0 && $newCol < 7) {
                $adjacentPositions[] = chr($newRow + ord('A')) . ($newCol + 1);
            }
        }

        return $adjacentPositions;
    }

    public static function getCurrentTurn($gameId)
    {
        $db = Database::connect();
        $query = $db->prepare("SELECT current_turn FROM games WHERE id = :game_id");
        $query->execute([':game_id' => $gameId]);
        $currentTurn = $query->fetchColumn();

        error_log("Fetched current turn: GameID=$gameId, CurrentTurn=$currentTurn");
        return (int)$currentTurn; // Ensure it returns an integer
    }

    public static function checkEndgame($gameId)
    {
        $db = Database::connect();

        $query = $db->prepare("SELECT COUNT(*) FROM state WHERE game_id = :game_id AND occupant = 0");
        $query->execute([':game_id' => $gameId]);
        $emptyCount = $query->fetchColumn();

        if ($emptyCount > 0) {
            return false;
        }

        $query = $db->prepare("
            SELECT occupant, COUNT(*) as count
            FROM state
            WHERE game_id = :game_id
            GROUP BY occupant
        ");
        $query->execute([':game_id' => $gameId]);
        $counts = $query->fetchAll(PDO::FETCH_KEY_PAIR);

        $blueCount = $counts[1] ?? 0;
        $redCount = $counts[2] ?? 0;

        if ($blueCount > $redCount) {
            return 1;
        } elseif ($redCount > $blueCount) {
            return 2;
        }

        return 0;
    }
}
