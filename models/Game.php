<?php
require_once 'config/Database.php';

class Game
{
    public static function create($bluePlayerId, $redPlayerId)
    {
        $db = Database::connect();

        $query = $db->prepare("
            INSERT INTO games (blue_player, red_player, current_turn, status) 
            VALUES (?, ?, ?, ?)
        ");
        $currentTurn = 1;
        $status = 'active';
        $query->bind_param('iiis', $bluePlayerId, $redPlayerId, $currentTurn, $status);
        $query->execute();

        return $db->insert_id;
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

        $query = $db->prepare("INSERT INTO state (game_id, position, occupant) VALUES (?, ?, ?)");

        for ($row = 1; $row <= 7; $row++) {
            for ($col = 1; $col <= 7; $col++) {
                $position = chr(64 + $row) . $col;
                $occupant = 0;
                $query->bind_param('isi', $gameId, $position, $occupant);
                $query->execute();
            }
        }

        $updateQuery = $db->prepare("UPDATE state SET occupant = ? WHERE game_id = ? AND position = ?");
        foreach ($initialPositions as $pos) {
            $updateQuery->bind_param('iis', $pos[1], $gameId, $pos[0]);
            $updateQuery->execute();
        }
    }

    public static function getActiveGames($playerId)
    {
        $db = Database::connect();

        $query = $db->prepare("
            SELECT id, blue_player, red_player, current_turn, status
            FROM games
            WHERE (blue_player = ? OR red_player = ?) AND status = 'active'
        ");
        $query->bind_param('ii', $playerId, $playerId);
        $query->execute();
        $result = $query->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
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
        $query = $db->prepare("SELECT position, occupant FROM state WHERE game_id = ?");
        $query->bind_param('i', $gameId);
        $query->execute();
        $result = $query->get_result();

        $board = [];
        while ($row = $result->fetch_assoc()) {
            $board[$row['position']] = $row['occupant'];
        }

        return $board;
    }

    public static function getGameData($gameId)
    {
        $db = Database::connect();
        $query = $db->prepare("SELECT * FROM games WHERE id = ?");
        $query->bind_param('i', $gameId);
        $query->execute();
        $result = $query->get_result();

        return $result->fetch_assoc();
    }

    public static function getBoard($gameId)
    {
        $db = Database::connect();
        $query = $db->prepare("SELECT position, occupant FROM state WHERE game_id = ?");
        $query->bind_param('i', $gameId);
        $query->execute();
        $result = $query->get_result();

        $board = [];
        while ($row = $result->fetch_assoc()) {
            $rowIndex = ord($row['position'][0]) - ord('A');
            $colIndex = intval($row['position'][1]) - 1;
            $board[$rowIndex][$colIndex] = $row['occupant'];
        }

        return $board;
    }

    public static function executeMove($gameId, $from, $to, $moveType, $currentPlayer)
    {
        $db = Database::connect();

        $db->begin_transaction();
        try {
            if ($moveType === 'jump') {
                $query = $db->prepare("UPDATE state SET occupant = 0 WHERE game_id = ? AND position = ?");
                $query->bind_param('is', $gameId, $from);
                $query->execute();
            }

            $query = $db->prepare("UPDATE state SET occupant = ? WHERE game_id = ? AND position = ?");
            $query->bind_param('iis', $currentPlayer, $gameId, $to);
            $query->execute();

            self::flipAdjacentPieces($gameId, $to, $currentPlayer);

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    private static function flipAdjacentPieces($gameId, $position, $currentPlayer)
    {
        $db = Database::connect();
        $adjacentPositions = self::getAdjacentPositions($position);

        foreach ($adjacentPositions as $adjPos) {
            $query = $db->prepare("SELECT occupant FROM state WHERE game_id = ? AND position = ?");
            $query->bind_param('is', $gameId, $adjPos);
            $query->execute();
            $result = $query->get_result();
            $occupant = $result->fetch_assoc()['occupant'] ?? 0;

            if ($occupant && $occupant !== $currentPlayer) {
                $updateQuery = $db->prepare("UPDATE state SET occupant = ? WHERE game_id = ? AND position = ?");
                $updateQuery->bind_param('iis', $currentPlayer, $gameId, $adjPos);
                $updateQuery->execute();
            }
        }
    }

    public static function switchTurn($gameId)
    {
        $db = Database::connect();

        $query = $db->prepare("SELECT current_turn FROM games WHERE id = ?");
        $query->bind_param('i', $gameId);
        $query->execute();
        $result = $query->get_result();
        $currentTurn = $result->fetch_assoc()['current_turn'];

        $nextTurn = ($currentTurn == 1) ? 2 : 1;

        $updateQuery = $db->prepare("UPDATE games SET current_turn = ? WHERE id = ?");
        $updateQuery->bind_param('ii', $nextTurn, $gameId);
        $updateQuery->execute();
    }

    public static function getAdjacentPositions($position)
    {
        $row = ord($position[0]) - ord('A');
        $col = intval($position[1]) - 1;

        $directions = [
            [-1, -1], [-1, 0], [-1, 1],
            [0, -1],          [0, 1],
            [1, -1], [1, 0], [1, 1]
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
        $query = $db->prepare("SELECT current_turn FROM games WHERE id = ?");
        $query->bind_param('i', $gameId);
        $query->execute();
        $result = $query->get_result();

        return (int) $result->fetch_assoc()['current_turn'];
    }

    public static function checkEndgame($gameId)
    {
        $db = Database::connect();

        $query = $db->prepare("SELECT COUNT(*) AS empty_spaces FROM state WHERE game_id = ? AND occupant = 0");
        $query->bind_param('i', $gameId);
        $query->execute();
        $result = $query->get_result();
        $emptyCount = $result->fetch_assoc()['empty_spaces'];

        if ($emptyCount > 0) {
            return false;
        }

        $query = $db->prepare("
            SELECT occupant, COUNT(*) as count
            FROM state
            WHERE game_id = ?
            GROUP BY occupant
        ");
        $query->bind_param('i', $gameId);
        $query->execute();
        $result = $query->get_result();

        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['occupant']] = $row['count'];
        }

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
?>
