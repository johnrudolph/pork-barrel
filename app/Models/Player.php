<?php

namespace App\Models;

use App\Events\OffersSubmitted;
use App\Events\PlayerReceivedMoney;
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

    public function submitOffers(Round $round, array $offers)
    {
        OffersSubmitted::fire(
            player_id: $this->id,
            round_id: $round->id,
            offers: $offers,
        );

        // @todo this is temporary until the game has timers and advances automatically
        if (collect($round->state()->offers)->count() === $round->game->players()->count()) {
            $round->advancePhase();
        }
    }
}
