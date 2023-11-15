<?php

namespace App\States;

use Thunk\Verbs\State;
use Illuminate\Support\Collection;

class RoundState extends State
{
    public string $status = 'upcoming';

    public string $phase = '';

    public int $round_number;

    public $bureaucrats;

    public $offers;

    public $auction_winners;
}
