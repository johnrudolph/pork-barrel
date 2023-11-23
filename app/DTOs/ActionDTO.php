<?php

namespace App\DTOs;

class ActionDTO
{
    public int $player_id;

    public int $round_id;

    public string $bureaucrat;

    public ?array $options = null;

    public function __construct(
        int $player_id,
        int $round_id,
        string $bureaucrat,
        array $options = null
    ) {
        $this->player_id = $player_id;
        $this->bureaucrat = $bureaucrat;
        $this->options = $options;
        $this->round_id = $round_id;
    }
}
