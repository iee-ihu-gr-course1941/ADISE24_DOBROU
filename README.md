# Ataxx Game Commands

## Overview
This document outlines all the commands available to interact with the Ataxx game via the CLI. Each command serves a specific purpose, such as logging in, starting a game, making moves, or resuming an active game.

## Commands

### 1. Login
**Command:**
```bash
php cli.php login [username]
```
**Description:**
Logs in a player or creates a new player if the username does not already exist.

**Arguments:**
- `username` (string): The username of the player.

**Example:**
```bash
php cli.php login JohnDoe
```

---

### 2. Start Game
**Command:**
```bash
php cli.php start-game [blue_player_id] [red_player_id]
```
**Description:**
Starts a new game between two players. Initializes the board with the starting positions.

**Arguments:**
- `blue_player_id` (int): ID of the player controlling the blue pieces.
- `red_player_id` (int): ID of the player controlling the red pieces.

**Example:**
```bash
php cli.php start-game 1 2
```

---

### 3. Make Move
**Command:**
```bash
php cli.php move [game_id] [player_id] [from_position] [to_position]
```
**Description:**
Makes a move for a player in an active game. Validates the move according to Ataxx rules and updates the board state.

**Arguments:**
- `game_id` (int): ID of the game.
- `player_id` (int): ID of the player making the move.
- `from_position` (string): Current position of the piece being moved (e.g., `A1`).
- `to_position` (string): Destination position of the piece (e.g., `B2`).

**Example:**
```bash
php cli.php move 14 1 A1 B2
```

---

### 4. Show Board
**Command:**
```bash
php cli.php show-board [game_id]
```
**Description:**
Displays the current board state of the specified game.

**Arguments:**
- `game_id` (int): ID of the game.

**Example:**
```bash
php cli.php show-board 14
```

---

### 5. List Active Games
**Command:**
```bash
php cli.php list-games [player_id]
```
**Description:**
Lists all active games for a specific player.

**Arguments:**
- `player_id` (int): ID of the player.

**Example:**
```bash
php cli.php list-games 1
```

---

## Notes
- All game states and player data are stored in the database.
- Ensure the database is properly configured in `config/Database.php`.
- Use the `list-games` command to find active games before resuming or making a move.

