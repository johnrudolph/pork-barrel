<?php

use App\Events\GameCreated;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
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

it('sets transparency to false by default', function () {
    $user = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
    );

    $game = Game::find($event->game_id);

    expect($game->is_transparent)->toBeFalse();
    expect($game->state()->is_transparent)->toBeFalse();

    $event = GameCreated::fire(
        user_id: $user->id,
        is_transparent: true,
    );

    $game = Game::find($event->game_id);

    expect($game->is_transparent)->toBeTrue();
    expect($game->state()->is_transparent)->toBeTrue();
});
