<?php

namespace App\DTOs;

use App\Events\OfferSubmitted;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Support\Facades\Validator;
use Thunk\Verbs\SerializedByVerbs;
use Thunk\Verbs\Support\Normalization\NormalizeToPropertiesAndClassName;

class OfferDTO extends LivewireDTO implements SerializedByVerbs
{
    use NormalizeToPropertiesAndClassName;

    public function __construct(
        public int $player_id,
        public int $round_id,
        public string $bureaucrat,
        public int $amount_offered = 0,
        public int $amount_modified = 0,
        public bool $awarded = false,
        public bool $is_blocked = false,
        public ?array $options = null,
        public ?array $data = null,
        public ?array $rules = null,
    ) {
        $this->options = $this->bureaucrat::options(Round::find($round_id), Player::find($player_id));

        $this->data ??= collect($this->options)->mapWithKeys(fn ($v, $k) => [$k => null]
        )->toArray();

        $this->rules ??= collect($this->options)->mapWithKeys(fn ($option, $option_name) => [$option_name => $option['rules']]
        )->toArray();
    }

    public function netOffer()
    {
        return $this->amount_offered + $this->amount_modified;
    }

    public function validate()
    {
        return Validator::make($this->data, $this->rules);
    }

    public function submit()
    {
        OfferSubmitted::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            offer: $this
        );
    }
}
