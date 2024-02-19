<?php

namespace Tests;

use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use Glhd\Bits\Snowflake;
use App\States\GameState;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\States\PlayerState;
use App\Events\AuctionEnded;
use App\Events\RoundStarted;
use Thunk\Verbs\Facades\Verbs;
use App\Bureaucrats\Bureaucrat;
use App\Events\PlayerJoinedGame;
use App\RoundTemplates\RoundTemplate;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public Game $game;

    public Player $john;

    public Player $daniel;

    public Player $jacob;

    public Player $chris;

    public function bootGame()
    {
        $user_1 = User::factory()->create();
        $user_2 = User::factory()->create();
        $user_3 = User::factory()->create();
    
        $event = GameCreated::fire(
            user_id: $user_1->id,
            game_id: Snowflake::make()->id(),
        );

        $this->game = Game::find($event->game_id);
    
        $daniel_id = PlayerJoinedGame::fire(
            game_id: $event->game_id,
            user_id: $user_2->id,
            name: $user_2->name,
        )->player_id;

        $this->daniel = Player::find($daniel_id);
    
        $jacob_id = PlayerJoinedGame::fire(
            game_id: $event->game_id,
            user_id: $user_3->id,
            name: $user_3->name,
        )->player_id;

        $this->jacob = Player::find($jacob_id);
    
        $this->game = Game::find($event->game_id);
        GameStarted::fire(game_id: $this->game->id);
    
        $this->john = Player::firstWhere('user_id', $user_1->id);
    }

    public function endGame($game)
    {
        collect(range($game->currentRound()->round_number, $game->rounds->count() - 1))
            ->each(function ($round_number) use ($game) {
                AuctionEnded::fire(round_id: $game->state()->round_ids[$round_number - 1]);
                Verbs::commit();

                RoundStarted::fire(
                    game_id: $game->id,
                    round_number: $round_number + 1,
                    round_id: $game->state()->round_ids[$round_number],
                    bureaucrats: [Bureaucrat::class],
                    round_template: RoundTemplate::class,
                );

                Verbs::commit();
            });

        AuctionEnded::fire(round_id: $game->state()->round_ids[7]);
        Verbs::commit();

        $game->end();
        Verbs::commit();
    }
}
