<?php

namespace App\States;

use Thunk\Verbs\State;
use Illuminate\Support\Collection;

class GameState extends State
{
    public string $status = '';

    public Collection $players;

    public int $current_round_number = 0;

    public int $current_round_id = 0;
}
