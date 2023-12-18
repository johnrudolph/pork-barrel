<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Event;

class ActionAppliedAtEndOfRound extends Event
{
    public int $round_id;

    public int $player_id;

    public $bureaucrat;

    public $amount;

    public $data;

    public function handle()
    {
        $this->bureaucrat::handleOnRoundEnd(
            PlayerState::load($this->player_id),
            RoundState::load($this->round_id),
            $this->amount,
            $this->data
        );
    }
}
