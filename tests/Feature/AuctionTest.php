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

    Verbs::commit();

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('provides 5 options to bid on in a round', function () {
    $this->assertEquals(
        5,
        collect($this->game->currentRound()->state()->bureaucrats)
            ->unique()
            ->count()
    );
});

it('records offers made to the state', function () {
    $round = $this->game->currentRound();

    $this->john->submitOffer($round, $round->state()->bureaucrats[0], 1);

    $this->assertEquals(
        1,
        collect($round->state()->offers)
            ->filter(fn ($o) => $o['player_id'] === $this->john->id
                && $o['bureaucrat'] === $round->state()->bureaucrats[0]
                && $o['amount'] === 1
            )
            ->count()
    );
});

it('allows you to easily access which actions are available for each player', function () {
    $round = $this->game->currentRound();
    $bureaucrats = $round->state()->bureaucrats;

    $this->john->submitOffer($round, $bureaucrats[0], 1);
    $this->john->submitOffer($round, $bureaucrats[1], 0);
    $this->john->submitOffer($round, $bureaucrats[2], 2);
    $this->john->submitOffer($round, $bureaucrats[3], 0);

    $this->daniel->submitOffer($round, $bureaucrats[0], 0);
    $this->daniel->submitOffer($round, $bureaucrats[1], 1);
    $this->daniel->submitOffer($round, $bureaucrats[2], 2);
    $this->daniel->submitOffer($round, $bureaucrats[3], 0);

    $round->endAuctionPhase();

    $this->assertEquals(
        true,
        collect($round->state()->actionsWonBy($this->john->id))
            ->contains($bureaucrats[0])
    );

    $this->assertEquals(
        false,
        collect($round->state()->actionsWonBy($this->daniel->id))
            ->contains($bureaucrats[0])
    );

    $this->assertEquals(
        false,
        collect($round->state()->actionsWonBy($this->john->id))
            ->contains($bureaucrats[1])
    );

    $this->assertEquals(
        true,
        collect($round->state()->actionsWonBy($this->daniel->id))
            ->contains($bureaucrats[1])
    );

    $this->assertEquals(
        true,
        collect($round->state()->actionsWonBy($this->john->id))
            ->contains($bureaucrats[2])
    );

    $this->assertEquals(
        true,
        collect($round->state()->actionsWonBy($this->daniel->id))
            ->contains($bureaucrats[2])
    );

    $this->assertEquals(
        false,
        collect($round->state()->actionsWonBy($this->john->id))
            ->contains($bureaucrats[3])
    );

    $this->assertEquals(
        false,
        collect($round->state()->actionsWonBy($this->daniel->id))
            ->contains($bureaucrats[3])
    );
});

it('spends the money offerred by winners', function () {
    $round = $this->game->currentRound();
    $bureaucrats = $round->state()->bureaucrats;

    $this->john->submitOffer($round, $bureaucrats[0], 1);
    $this->john->submitOffer($round, $bureaucrats[1], 0);
    $this->john->submitOffer($round, $bureaucrats[2], 2);
    $this->john->submitOffer($round, $bureaucrats[3], 0);

    $this->daniel->submitOffer($round, $bureaucrats[0], 0);
    $this->daniel->submitOffer($round, $bureaucrats[1], 1);
    $this->daniel->submitOffer($round, $bureaucrats[2], 2);
    $this->daniel->submitOffer($round, $bureaucrats[3], 0);

    $round->endAuctionPhase();

    // John spends 3, because he didn't win the second bureaucrat
    $this->assertEquals(7, $this->john->state()->money);

    // Daniel spends 3, because he didn't win the first bureaucrat
    $this->assertEquals(7, $this->daniel->state()->money);
});
