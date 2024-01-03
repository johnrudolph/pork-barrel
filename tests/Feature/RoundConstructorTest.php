<?php

use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\PlayerJoinedGame;
use App\Events\RoundEnded;
use App\Models\Game;
use App\Models\User;
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

    $this->game = Game::first();
});

it('selects bureaucrats and a round modifier for a round', function () {
    $this->game->rounds->first()->start();

    $this->assertTrue($this->game->currentRound()->state()->bureaucrats->count() > 0);
    $this->assertNotNull($this->game->currentRound()->state()->round_modifier);
});

it('prioritizes bureaucrats and modifiers that have not been selected in previous rounds', function () {
    $round_1 = $this->game->rounds->first();
    
    $round_1->start();
    AuctionEnded::fire(round_id: $round_1->id);
    RoundEnded::fire(round_id: $round_1->id);

    $round_2 = $round_1->next();

    $round_2->start();

    $all_bureaucrats_selected = $round_1->state()->bureaucrats
        ->concat($round_2->state()->bureaucrats);

    $this->assertEquals(
        $all_bureaucrats_selected->count(),
        $all_bureaucrats_selected->unique()->count()
    );

    $this->assertNotEquals(
        $round_1->state()->round_modifier,
        $round_2->state()->round_modifier
    );
});