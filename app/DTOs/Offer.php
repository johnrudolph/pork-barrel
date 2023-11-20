<?php

namespace App\DTOs;

class Offer
{
    public int $player_id;

    public int $round_id;

    public string $bureaucrat;

    public int $amount;

    public function __construct(
        int $player_id,
        int $round_id,
        string $bureaucrat, 
        int $amount
    ) {
        $this->player_id = $player_id;
        $this->bureaucrat = $bureaucrat;
        $this->amount = $amount;
        $this->round_id = $round_id;
    }
}