<?php
if ($argc < 3) {
    die(json_encode(['error' => 'Invalid command. Usage: php cli.php [command] [args...]']));
}

$command = $argv[1];

if ($command == 'list-games') {
    if ($argc < 3) {
        die(json_encode(['error' => 'Usage: php cli.php list-games [player_id]']));
    }

    $playerId = $argv[2];
    require 'controllers/GameController.php';
    $controller = new GameController();
    $response = $controller->listActiveGames($playerId);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

if ($command == 'login') {
    $username = $argv[2];
    require 'controllers/PlayerController.php';
    $controller = new PlayerController();
    $response = $controller->login($username);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

if ($command == 'start-game') {
    if ($argc < 4) {
        die(json_encode(['error' => 'Usage: php cli.php start-game [blue_player_id] [red_player_id]']));
    }

    $bluePlayerId = $argv[2];
    $redPlayerId = $argv[3];

    require 'controllers/GameController.php';
    $controller = new GameController();
    $response = $controller->startGame($bluePlayerId, $redPlayerId);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

if ($command == 'move') {
    if ($argc < 5) {
        die(json_encode(['error' => 'Usage: php cli.php move [game_id] [player_id] [from_position] [to_position]']));
    }

    $gameId = $argv[2];
    $playerId = $argv[3];
    $from = $argv[4];
    $to = $argv[5];

    require 'controllers/GameController.php';
    $controller = new GameController();
    $response = $controller->makeMove($gameId, $playerId, $from, $to);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

if ($command == 'show-board') {
    if ($argc < 3) {
        die(json_encode(['error' => 'Usage: php cli.php show-board [game_id]']));
    }

    $gameId = $argv[2];
    require 'controllers/GameController.php';
    $controller = new GameController();
    $response = $controller->showBoard($gameId);

    if ($response['status'] === 'success') {
        echo $response['board'];
    } else {
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
