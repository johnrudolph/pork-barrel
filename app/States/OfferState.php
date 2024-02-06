<?php

namespace App\States;

use Illuminate\Support\Collection;
use Thunk\Verbs\State;

class OfferState extends State
{
    public int $player_id;

    public int $round_id;

    public string $bureaucrat;

    public int $amount_offered = 0;

    // @todo this would be nice to have
    // public Collection $amount_modifications = [];

    public int $amount_modified = 0;

    public bool $awarded = false;

    public bool $is_blocked = false;

    public ?array $data = null;

    public function netOffer()
    {
        return $this->amount_offered + $this->amount_modified;

        // @todo then you could do something like this:
        // return $this->amount_offered + $this->amount_modifications->sum('amount');
    }
}
