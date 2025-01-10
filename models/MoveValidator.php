<?php
class MoveValidator
{

    public static function isValidMove($from, $to, $board, $currentPlayer)
    {
        // Debugging: Log the initial input
        error_log("Attempting move: From=$from, To=$to, CurrentPlayer=$currentPlayer");

        // Check if the positions exist on the board
        if (!array_key_exists($from, $board) || !array_key_exists($to, $board)) {
            error_log("Invalid positions: From=$from, To=$to");
            return false;
        }

        // Check if the 'from' position belongs to the current player
        if ($board[$from] != $currentPlayer) {
            error_log("Invalid ownership: $from belongs to " . $board[$from]);
            return false;
        }

        // Check if the 'to' position is empty
        if ($board[$to] != 0) {
            error_log("Target not empty: $to occupied by " . $board[$to]);
            return false;
        }

        // Calculate the row and column differences
        $fromRow = ord($from[0]) - ord('A');
        $fromCol = intval($from[1]) - 1;

        $toRow = ord($to[0]) - ord('A');
        $toCol = intval($to[1]) - 1;

        $rowDiff = abs($toRow - $fromRow);
        $colDiff = abs($toCol - $fromCol);

        // Debugging: Log the calculated distances
        error_log("RowDiff=$rowDiff, ColDiff=$colDiff");

        // Check if the move is valid (1 square or 2 squares away)
        if (($rowDiff <= 1 && $colDiff <= 1) || ($rowDiff <= 2 && $colDiff <= 2)) {
            $moveType = $rowDiff <= 1 ? 'extend' : 'jump';
            error_log("Valid move type: $moveType");
            return $moveType;
        }

        error_log("Invalid move distance: RowDiff=$rowDiff, ColDiff=$colDiff");
        return false;
    }
}
