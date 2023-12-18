<?php

use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\PlayerJoinedGame;
use App\Models\Game;
use App\Models\User;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

beforeEach(function () {
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

    $this->game = Game::find($event->game_id);

    $this->game->start();

    Verbs::commit();
});

it('seeds rounds for new games', function () {
    $this->assertEquals(8, $this->game->rounds->count());
    $this->assertEquals(8, collect($this->game->state()->rounds)->count());
    $this->assertEquals($this->game->currentRound()->id, $this->game->state()->rounds[0]);
    $this->assertEquals(1, $this->game->state()->current_round_number);
    $this->assertEquals(1, $this->game->currentRound()->round_number);
});

it('progresses to the next round after rounds end', function () {
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();
    $this->game->currentRound()->endRound();
    Verbs::commit();
    $this->game->currentRound()->next()->start();

    $this->assertEquals('complete', $this->game->rounds->first()->fresh()->status);
    $this->assertEquals('complete', $this->game->rounds->first()->state()->status);
    $this->assertEquals($this->game->currentRound()->id, $this->game->state()->rounds[1]);
    $this->assertEquals(2, $this->game->state()->current_round_number);
    $this->assertEquals(2, $this->game->currentRound()->round_number);
});
