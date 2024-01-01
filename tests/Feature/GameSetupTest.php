<?php

use App\Events\GameCreated;
use App\Events\PlayerJoinedGame;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\States\GameState;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();
});

it('creates a game and player when a game is created', function () {
    $user = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
        game_id: Snowflake::make()->id(),
    );

    $game = Game::find($event->game_id);

    $this->assertCount(1, $game->players);

    $this->assertEquals(
        $game->state()->players[0],
        Player::first()->id,
    );

    $this->assertEquals(
        $game->state()->playerStates()->first()->id,
        Player::first()->id,
    );
});

it('changes a players currentGame when they join a new game', function () {
    $user = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
        game_id: Snowflake::make()->id(),
    );

    $game = Game::find($event->game_id);

    $this->assertEquals($game->id, $user->fresh()->currentGame->id);

    $event2 = GameCreated::fire(
        user_id: $user->id,
        game_id: Snowflake::make()->id(),
    );

    $game2 = Game::find($event2->game_id);

    $this->assertEquals($game2->id, $user->fresh()->currentGame->id);
});

it('assigns random industries for each player', function () {
    $user = User::factory()->create();
    $user_2 = User::factory()->create();
    $user_3 = User::factory()->create();
    $user_4 = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
        game_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user_2->id,
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user_3->id,
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user_4->id,
    );

    $this->assertTrue(
        GameState::load($event->game_id)->playerStates()
            ->map(fn ($p) => $p->industry)
            ->unique()
            ->count() === 4
    );
});
