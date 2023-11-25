<?php

namespace App\States;

use Thunk\Verbs\State;

class GameState extends State
{
    public string $status = '';

    public $players;

    public $rounds;

    public int $current_round_number = 0;

    public int $current_round_id = 0;
}
