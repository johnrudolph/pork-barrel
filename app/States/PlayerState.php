<?php

namespace App\States;

use App\Events\PlayerReceivedMoney;
use Thunk\Verbs\State;

class PlayerState extends State
{
    public $industry = null;
    
    public $money = 0;

    public $income = 10;

    public $has_bailout = false;

    public $money_in_treasury = 0;

    public function endRound($round_id)
    {
        if ($this->has_bailout && $this->money === 0) {
            PlayerReceivedMoney::fire(
                player_id: $this->id,
                round_id: $round_id,
                amount: 10,
                activity_feed_description: 'You received a bailout. No one needs a stronger safety net than you.',
            );

            $this->has_bailout = false;
        }
    }
}
