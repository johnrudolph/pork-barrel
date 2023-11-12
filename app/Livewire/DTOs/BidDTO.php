<?php

namespace App\Livewire\DTOs;

use App\Models\Round;
use App\Models\Player;
use App\Models\Product;
use App\DTOs\LivewireDTO as DTO;

class BidDTO extends DTO
{
    public function __construct(
        public int $player_id,
        public string $round_id,
        public string $bureaucrat_1_slug = '',
        public string $bureaucrat_2_slug = '',
        public string $bureaucrat_3_slug = '',
        public string $bureaucrat_4_slug = '',
        public string $bureaucrat_5_slug = '',
        public int $bureaucrat_1_bid = 0,
        public int $bureaucrat_2_bid = 0,
        public int $bureaucrat_3_bid = 0,
        public int $bureaucrat_4_bid = 0,
        public int $bureaucrat_5_bid = 0,
    ) {}

    public static function fromRound(Round $round, Player $player): static
    {
        return new static(
            player_id: $player->id,
            round_id: $round->id,
            bureaucrat_1_slug: $round->state()->bureaucrat_1_slug,
            bureaucrat_2_slug: $round->state()->bureaucrat_2_slug,
            bureaucrat_3_slug: $round->state()->bureaucrat_3_slug,
            bureaucrat_4_slug: $round->state()->bureaucrat_4_slug,
            bureaucrat_5_slug: $round->state()->bureaucrat_5_slug,
            bureaucrat_1_bid: 0,
            bureaucrat_2_bid: 0,
            bureaucrat_3_bid: 0,
            bureaucrat_4_bid: 0,
            bureaucrat_5_bid: 0,
        );
    }

    // public function updateBid(?Product $product, bool $isDealer = false)
    // {
    //     $this->productId = $product?->id;
    //     $this->productLabel = $product?->name;
    //     $this->unit = $product?->unit_name;
    //     $this->pricePerUnit = $product?->price($isDealer);

    //     return $this;
    // }

    public function total()
    {
        return $this->bureaucrat_1_bid + $this->bureaucrat_2_bid 
            + $this->bureaucrat_3_bid + $this->bureaucrat_4_bid + $this->bureaucrat_5_bid;
    }
}
