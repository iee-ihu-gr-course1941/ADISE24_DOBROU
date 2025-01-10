<?php

class MoveValidator
{
    /**
     * Validate if a move is valid.
     *
     * @param string $from
     * @param string $to
     * @param array $board
     * @param int $currentPlayer
     * @return string|false The move type ('extend' or 'jump') if valid, false otherwise.
     */
    public static function isValidMove($from, $to, $board, $currentPlayer)
    {
        if (!array_key_exists($from, $board) || !array_key_exists($to, $board)) {
            return false;
        }

        if ($board[$from] != $currentPlayer) {
            return false;
        }

        if ($board[$to] != 0) {
            return false;
        }

        $fromRow = ord($from[0]) - ord('A');
        $fromCol = intval($from[1]) - 1;

        $toRow = ord($to[0]) - ord('A');
        $toCol = intval($to[1]) - 1;

        $rowDiff = abs($toRow - $fromRow);
        $colDiff = abs($toCol - $fromCol);

        if (($rowDiff <= 1 && $colDiff <= 1) || ($rowDiff <= 2 && $colDiff <= 2)) {
            return $rowDiff <= 1 ? 'extend' : 'jump';
        }

        return false;
    }
}
