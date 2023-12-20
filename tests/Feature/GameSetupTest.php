<?php

use App\Events\GameCreated;
use App\Events\PlayerJoinedGame;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

it('creates a game and player when a game is created', function () {
    $user = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
        game_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user->id,
    );

    Verbs::commit();

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

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user->id,
    );

    Verbs::commit();

    $game = Game::find($event->game_id);

    $this->assertEquals($game->id, $user->fresh()->currentGame->id);

    $event2 = GameCreated::fire(
        user_id: $user->id,
        game_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event2->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user->id,
    );

    Verbs::commit();

    $game2 = Game::find($event2->game_id);

    $this->assertEquals($game2->id, $user->fresh()->currentGame->id);
});
