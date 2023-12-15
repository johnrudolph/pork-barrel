<?php

namespace App\States;

use Thunk\Verbs\State;
use App\States\RoundState;
use App\States\PlayerState;
use Illuminate\Support\Collection;

class GameState extends State
{
    public string $status = '';

    public Collection $players;

    public Collection $rounds;

    public int $current_round_number = 0;

    public int $current_round_id = 0;

    // @todo: these two functions are getting wacky. They get called at unexpected times
    // and then suddenly return states instead of ints. No clue why

    // public function players()
    // {
    //     dump($this);
    //     return $this->players->map(fn ($id) => PlayerState::load($id));
    // }

    // public function rounds()
    // {
    //     return $this->rounds->map(fn ($id) => RoundState::load($id));
    // }

    public function currentRound(): RoundState
    {
        return RoundState::load($this->current_round_id);
    }

    public function nextRound(): RoundState
    {
        return RoundState::load($this->rounds[$this->current_round_number]) ?? null;
    }
}
