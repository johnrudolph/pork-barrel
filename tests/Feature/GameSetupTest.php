<?php

use App\Models\Game;
use App\Models\User;
use Thunk\Verbs\Facades\Verbs;
use App\Events\PlayerJoinedGame;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

it('returns a successful response', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    PlayerJoinedGame::fire(
        game_id: $game->id,
        user_id: $user->id,
    );

    Verbs::commit();

    $this->assertCount(1, $game->players);
});
