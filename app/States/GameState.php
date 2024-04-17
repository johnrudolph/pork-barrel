<?php

namespace App\States;

use Illuminate\Support\Collection;
use Thunk\Verbs\State;

class GameState extends State
{
    public string $status = '';

    public Collection $players;

    public Collection $round_ids;

    public string $template;

    public int $current_round_number = 0;

    public int $current_round_id = 0;

    public float $interest_rate = 0.25;

    public bool $is_transparent = false;

    public function playerStates()
    {
        return $this->players->map(fn ($id) => PlayerState::load($id));
    }

    public function rounds()
    {
        return $this->round_ids->map(fn ($id) => RoundState::load($id));
    }

    public function currentRound(): RoundState
    {
        return RoundState::load($this->current_round_id);
    }

    public function nextRound(): RoundState
    {
        return RoundState::load($this->round_ids->toArray()[$this->current_round_number]) ?? null;
    }
}
