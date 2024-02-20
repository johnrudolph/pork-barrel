<?php

use App\Bureaucrats\Bureaucrat;
use App\Bureaucrats\GamblinGoat;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\PlayerAwaitingResults;
use App\Events\PlayerJoinedGame;
use App\Events\PlayerReadiedUp;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundTemplates\RoundTemplate;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();
    $user = User::factory()->create();

    $user_2 = User::factory()->create();

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

    $this->game = Game::find($event->game_id);

    $this->game->start();

    $this->john = Player::all()->first();
    $this->daniel = Player::all()->last();
});

it('seeds rounds for new games', function () {
    $this->assertEquals(8, $this->game->rounds->count());
    $this->assertEquals(8, collect($this->game->state()->round_ids)->count());
    $this->assertEquals($this->game->currentRound()->id, $this->game->state()->round_ids[0]);
    $this->assertEquals(1, $this->game->state()->current_round_number);
    $this->assertEquals(1, $this->game->currentRound()->round_number);
});

it('progresses to the next round after rounds end', function () {
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    $this->game->currentRound()->next()->start();

    $this->assertEquals('complete', $this->game->rounds->first()->fresh()->status);
    $this->assertEquals('complete', $this->game->rounds->first()->state()->status);
    $this->assertEquals($this->game->currentRound()->id, $this->game->state()->round_ids[1]);
    $this->assertEquals(2, $this->game->state()->current_round_number);
    $this->assertEquals(2, $this->game->currentRound()->round_number);
});

it('sets the appropriate statuses and current_round_ids as rounds proceed', function () {
    $round_1_id = $this->game->state()->round_ids[0];
    $round_2_id = $this->game->state()->round_ids[1];

    $this->assertTrue($this->game->currentRound()->state()->status === 'auction');
    $this->assertTrue($this->john->state()->current_round_id === $round_1_id);
    $this->assertTrue($this->john->state()->status === 'auction');

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);
    PlayerAwaitingResults::fire(
        player_id: $this->john->id,
        round_id: $round_1_id,
    );

    $this->assertTrue($this->game->currentRound()->state()->status === 'auction');
    $this->assertTrue($this->john->state()->current_round_id === $round_1_id);
    $this->assertTrue($this->john->state()->status === 'waiting');

    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);
    AuctionEnded::fire(round_id: $round_1_id);

    $this->assertTrue($this->game->currentRound()->state()->status === 'complete');
    $this->assertTrue($this->game->state()->currentRound()->id === $round_1_id);
    $this->assertTrue($this->john->state()->current_round_id === $round_1_id);
    $this->assertTrue($this->john->state()->status === 'waiting');

    PlayerReadiedUp::fire(
        player_id: $this->daniel->id,
        game_id: $this->game->id,
        round_id: $round_1_id,
    );

    $this->assertEquals($this->game->state()->currentRound()->id, $round_2_id);
    $this->assertEquals($this->game->state()->currentRound()->status, 'auction');
    $this->assertEquals($this->john->state()->current_round_id, $round_1_id);
    $this->assertEquals($this->john->state()->status, 'waiting');
    $this->assertEquals($this->daniel->state()->current_round_id, $round_2_id);
    $this->assertEquals($this->daniel->state()->status, 'auction');
});

it('ends the game after the final round', function () {
    collect(range($this->game->currentRound()->round_number, $this->game->rounds->count() - 1))
        ->each(function ($round_number) {
            AuctionEnded::fire(round_id: $this->game->state()->round_ids[$round_number - 1]);
            Verbs::commit();

            RoundStarted::fire(
                game_id: $this->game->id,
                round_number: $round_number + 1,
                round_id: $this->game->state()->round_ids[$round_number],
                bureaucrats: [Bureaucrat::class],
                round_template: RoundTemplate::class,
            );

            Verbs::commit();
        });

    AuctionEnded::fire(round_id: $this->game->state()->round_ids[7]);

    $this->assertEquals('complete', $this->game->state()->status);
});
