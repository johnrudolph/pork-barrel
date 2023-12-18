<?php

namespace Tests;

use App\Bureaucrats\Bureaucrat;
use App\Events\AuctionEnded;
use App\Events\RoundEnded;
use App\Events\RoundStarted;
use App\RoundModifiers\RoundModifier;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Thunk\Verbs\Facades\Verbs;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function endGame($game)
    {
        collect(range($game->currentRound()->round_number, $game->rounds->count() - 1))
            ->each(function ($round_number) use ($game) {
                AuctionEnded::fire(round_id: $game->state()->round_ids[$round_number - 1]);
                Verbs::commit();
                RoundEnded::fire(round_id: $game->state()->round_ids[$round_number - 1]);
                Verbs::commit();

                RoundStarted::fire(
                    game_id: $game->id,
                    round_number: $round_number + 1,
                    round_id: $game->state()->round_ids[$round_number],
                    bureaucrats: [Bureaucrat::class],
                    round_modifier: RoundModifier::class,
                );

                Verbs::commit();
            });

        AuctionEnded::fire(round_id: $game->state()->round_ids[7]);
        Verbs::commit();
        RoundEnded::fire(round_id: $game->state()->round_ids[7]);
        Verbs::commit();

        $game->end();
        Verbs::commit();
    }
}
