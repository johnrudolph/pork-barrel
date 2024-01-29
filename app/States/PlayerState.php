<?php

namespace App\States;

use Illuminate\Support\Collection;
use Thunk\Verbs\State;

class PlayerState extends State
{
    public $game_id;

    public $user_id;

    public $name;

    public $money_in_treasury = 0;

    public $money_frozen = 0;

    public $money_hidden = 0;

    public $income = 5;

    public $status = 'auction';

    public $current_round_id;

    public $current_round_number;

    public $industry;

    public Collection $money_history;

    public Collection $perks;

    public function game(): GameState
    {
        return GameState::load($this->game_id);
    }

    public function availableMoney()
    {
        return $this->money_history->sum(fn ($entry) => $entry->amount);
    }

    public function netWorth()
    {
        return $this->availableMoney() + $this->money_in_treasury + $this->money_frozen + $this->money_hidden;
    }
}
