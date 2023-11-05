<?php

namespace App\States;

use Thunk\Verbs\State;

class GameState extends State
{
    public string $status = '';

    public array $players = [];
}
