<?php

use App\Bureaucrats\DoubleDonkey;
use App\Events\GameCreated;
use App\Events\PlayerJoinedGame;
use App\Models\Game;
use App\Models\User;
use App\RoundConstructor\RoundConstructor;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();

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
        name: $user_2->name,
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user_3->id,
        name: $user_3->name,
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        player_id: Snowflake::make()->id(),
        user_id: $user_4->id,
        name: $user_4->name,
    );

    $this->game = Game::first();
});

it('selects bureaucrats and a round template for a round', function () {
    $this->game->rounds->first()->start();

    $this->assertTrue($this->game->currentRound()->state()->bureaucrats->count() > 0);
    $this->assertNotNull($this->game->currentRound()->state()->round_template);
});

it('has a helper for knowing the stages of the game', function () {
    $this->game->rounds->first()->start();

    expect(new RoundConstructor(round: $this->game->currentRound()->state()))
        ->stageOfGame()
        ->toBe('first-round');

    expect(DoubleDonkey::suitability(new RoundConstructor(round: $this->game->currentRound()->state())))
        ->toBe(1);
});
