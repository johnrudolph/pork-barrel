<?php

use App\Events\GameCreated;
use App\Models\Game;
use App\Models\User;
use Thunk\Verbs\Facades\Verbs;
use App\Events\PlayerJoinedGame;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

it('creates a game and player when a game is created', function () {
    $user = User::factory()->create();

    GameCreated::fire(
        user_id: $user->id,
    );

    Verbs::commit();

    $game = Game::first();
    
    $this->assertCount(1, $game->players);
});
