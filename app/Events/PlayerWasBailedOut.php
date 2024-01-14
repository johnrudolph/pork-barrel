<?php

namespace App\Events;

use App\DTOs\MoneyLogEntry;
use App\Models\Headline;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerWasBailedOut extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public function apply(PlayerState $state)
    {
        $state->has_bailout = false;
    }

    public function handle()
    {
        PlayerReceivedMoney::fire(
            player_id: $this->player_id,
            round_id: $this->round_id,
            amount: 10,
            activity_feed_description: 'You received a bailout. No one needs a stronger safety net than you.',
            type: MoneyLogEntry::TYPE_AWARD,
        );

        Headline::create([
            'round_id' => $this->round_id,
            'game_id' => RoundState::load($this->round_id)->game()->id,
            'headline' => $this->state(PlayerState::class)->industry.' bailed out!',
            'description' => 'After hitting rock bottom, the '.$this->state(PlayerState::class)->industry." industry has received a bailout. In order to have a just society, it's important that the mega-rich cannot fail. This corporate welfare will help this industry pick themselves up by their own bootstraps, and then put that boot right back on your neck.",
        ]);
    }
}
