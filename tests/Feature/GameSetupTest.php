<?php

use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use App\States\GameState;
use App\Events\GameCreated;
use App\Events\GameStarted;
use Thunk\Verbs\Facades\Verbs;
use App\Events\PlayerJoinedGame;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

it('creates a game and player when a game is created', function () {
    $user = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $user->id,
    );

    Verbs::commit();

    $game = Game::find($event->game_id);

    $this->assertCount(1, $game->players);

    $this->assertEquals(
        $event->state()->players[0],
        Player::first()->id,
    );
});

it('changes a players currentGame when they join a new game', function () {
    $user = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $user->id,
    );

    Verbs::commit();

    $game = Game::find($event->game_id);

    $this->assertEquals($game->id, $user->fresh()->currentGame->id);

    $event2 = GameCreated::fire(
        user_id: $user->id,
    );

    PlayerJoinedGame::fire(
        game_id: $event2->game_id,
        user_id: $user->id,
    );

    Verbs::commit();

    $game2 = Game::find($event2->game_id);

    $this->assertEquals($game2->id, $user->fresh()->currentGame->id);
});

it('seeds rounds for new games', function () {
    $user = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $user->id,
    );

    Verbs::commit();

    $game = Game::find($event->game_id);

    $game->start();

    Verbs::commit();

    $game_state = GameState::load($event->game_id);

    $this->assertEquals(
        $game->rounds->count(),
        8
    );

    $this->assertEquals(
        $game_state->current_round_number,
        1
    );
});