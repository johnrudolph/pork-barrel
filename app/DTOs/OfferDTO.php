<?php

namespace App\DTOs;

use Thunk\Verbs\SerializedByVerbs;
use Thunk\Verbs\Support\Normalization\NormalizeToPropertiesAndClassName;

class OfferDTO implements SerializedByVerbs
{
    use NormalizeToPropertiesAndClassName;

    public function __construct(
        public int $player_id,
        public int $round_id,
        public string $bureaucrat,
        public int $amount_offered = 0,
        public int $modified_amount = 0,
        public ?array $data = null
    ) {
    }
}
