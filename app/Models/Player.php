<?php

namespace App\Models;

use App\DTOs\OfferDTO;
use App\States\PlayerState;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory, HasSnowflakes;

    protected $guarded = [];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function state()
    {
        return PlayerState::load($this->id);
    }

    public function submitOffer(Round $round, $bureaucrat, $amount, ?array $data = null)
    {
        $offer = new OfferDTO(
            player_id: $this->id,
            round_id: $round->id,
            bureaucrat: $bureaucrat,
            amount_offered: $amount,
            data: $data,
        );

        $offer->submit();
    }
}
