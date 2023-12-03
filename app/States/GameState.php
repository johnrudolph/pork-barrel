<?php

namespace App\States;

use Thunk\Verbs\State;

class GameState extends State
{
    public string $status = '';

    public array $players = [];

    public array $rounds = [];

    public int $current_round_number = 0;

    public int $current_round_id = 0;

    public function currentRoundState(): RoundState
    {
        return RoundState::load($this->current_round_id);
    }

    public function nextRoundId()
    {
        return $this->rounds[$this->current_round_number] ?? null;
    }
}
