<?php

namespace App\States;

use Thunk\Verbs\State;

class RoundState extends State
{
    public string $status = 'upcoming';

    public string $phase = '';

    public int $round_number;

    public $bureaucrats;

    public $offers;

    public $auction_winners;
}
