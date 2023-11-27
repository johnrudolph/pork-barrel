<?php

use App\Bureaucrats\BailoutBunny;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Headlines\Headline;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;
use Thunk\Verbs\Lifecycle\StateManager;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->user_1 = User::factory()->create();
    $this->user_2 = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $this->user_1->id,
        game_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $this->user_1->id,
        player_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $this->user_2->id,
        player_id: Snowflake::make()->id(),
    );

    Verbs::commit();

    $this->game = Game::find($event->game_id);

    GameStarted::fire(game_id: $this->game->id);

    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        headline: Headline::class,
    );

    Verbs::commit();

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('gives players 10 money to start each round', function () {
    $this->assertEquals(10, $this->john->state()->money);
    app(StateManager::class)->reset();
    $this->assertEquals(10, $this->john->state()->money);

    $this->game->currentRound()->endAuctionPhase();
    Verbs::commit();
    $this->game->currentRound()->endRound();
    Verbs::commit();
    $this->game->currentRound()->next()->start();
    Verbs::commit();

    $this->assertEquals(20, $this->john->state()->money);
});
