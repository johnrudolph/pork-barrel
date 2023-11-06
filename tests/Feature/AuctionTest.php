<?php

use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use Glhd\Bits\Snowflake;
use App\Events\GameCreated;
use Thunk\Verbs\Facades\Verbs;
use App\Events\PlayerJoinedGame;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

    $this->game->start();

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('provides 5 options to bid on in a round', function() {
    $this->assertEquals(
        5,
        $this->game->currentRound()->state()
            ->bureaucrats
            ->unique()
            ->count()
    );
});