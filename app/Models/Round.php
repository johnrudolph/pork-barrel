<?php

namespace App\Models;

use App\Models\Game;
use App\States\RoundState;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Model;
use App\Events\CalculatedAuctionWinners;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Round extends Model
{
    use HasFactory, HasSnowflakes;

    protected $guarded = [];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function state()
    {
        return RoundState::load($this->id);
    }

    public function next()
    {
        return $this->game->rounds()
            ->where('round_number', $this->round_number + 1)
            ->first();
    }

    public function previous()
    {
        return $this->game->rounds()
            ->where('round_number', $this->round_number - 1)
            ->first();
    }

    public function advancePhase()
    {
        if ($this->state()->phase === 'auction') {
            // calculate winners
            CalculatedAuctionWinners::fire(round_id: $this->id);

            // @todo advance to decisions phase
        }
    }
}
