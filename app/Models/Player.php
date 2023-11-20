<?php

namespace App\Models;

use App\Bureaucrats\Bureaucrat;
use App\DTOs\Offer;
use App\States\PlayerState;
use App\Events\OfferSubmitted;
use App\Events\OffersSubmitted;
use App\Events\PlayerReceivedMoney;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function receiveMoney(int $amount, string $activity_feed_description)
    {
        PlayerReceivedMoney::fire(
            player_id: $this->id,
            round_id: $this->game->currentRound()->id,
            amount: $amount,
            activity_feed_description: $activity_feed_description
        );
    }

    public function getMoneyAttribute()
    {
        return $this->state()->money;
    }

    public function submitOffer(Round $round, $bureaucrat, $amount)
    {
        OfferSubmitted::fire(
            player_id: $this->id,
            round_id: $round->id,
            bureaucrat: $bureaucrat,
            amount: $amount
        );
    }
}
