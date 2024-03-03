<?php

namespace App\States;

use Thunk\Verbs\State;

class OfferState extends State
{
    public int $player_id;

    public int $round_id;

    public string $bureaucrat;

    public int $amount_offered = 0;

    public array $amount_modifications = [];

    public bool $awarded = false;

    public bool $is_blocked = false;

    public ?array $data = null;

    public function player()
    {
        return PlayerState::load($this->player_id);
    }

    public function netOffer()
    {
        return $this->amount_offered + collect($this->amount_modifications)
            ->sum('amount');
    }

    public function amountToChargePlayer()
    {
        return $this->amount_offered + collect($this->amount_modifications)
            ->filter(fn ($m) => $m['charged_to_player'])
            ->sum('amount');
    }
}
