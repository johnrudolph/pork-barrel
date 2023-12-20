<?php

namespace App\Events;

use App\Models\Headline;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerPutMoneyInTreasury extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public string $activity_feed_description;

    public int $amount;

    public function apply(PlayerState $state)
    {
        $state->money_in_treasury += $this->amount;
    }

    public function handle()
    {
        Headline::create([
            'round_id' => $this->round_id,
            'game_id' => RoundState::load($this->round_id)->game()->id,
            'headline' => 'Government sells bonds',
            'description' => 'In an effort to raise funds for infrastructure projects, they sold bonds to so and so industry.',
        ]);
    }
}
