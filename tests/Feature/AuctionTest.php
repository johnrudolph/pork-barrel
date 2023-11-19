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
    $offers = collect($this->game->currentRound()->state()->bureaucrats)
        ->mapWithKeys(fn ($b) => [$b => 2])
        ->toArray();

    $this->john->submitOffers($this->game->currentRound(), $offers);

    $this->assertEquals(
        $offers,
        $this->game->currentRound()->state()->offers[$this->john->id]
    );

    $offers = collect($this->game->currentRound()->state()->bureaucrats)
        ->mapWithKeys(fn ($b) => [$b => 1])
        ->toArray();

    $this->daniel->submitOffers($this->game->currentRound(), $offers);

    $this->assertEquals(
        $offers,
        $this->game->currentRound()->state()->offers[$this->daniel->id]
    );
});

it('records winners to the state when the auction phase ends', function () {
    $bureaucrats = $this->game->currentRound()->state()->bureaucrats;

    $johns_offers = [
        $bureaucrats[0] => 1,
        $bureaucrats[1] => 0,
        $bureaucrats[2] => 2,
        $bureaucrats[3] => 0,
        $bureaucrats[4] => 0,
    ];

    $this->john->submitOffers($this->game->currentRound(), $johns_offers);

    $daniels_offers = [
        $bureaucrats[0] => 0,
        $bureaucrats[1] => 1,
        $bureaucrats[2] => 2,
        $bureaucrats[3] => 0,
        $bureaucrats[4] => 0,
    ];

    $this->daniel->submitOffers($this->game->currentRound(), $daniels_offers);

    $this->game->currentRound()->advancePhase();

    $this->assertEquals(
        true,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[0]]['winning_player_ids'])
            ->contains($this->john->id)
    );

    $this->assertEquals(
        false,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[0]]['winning_player_ids'])
            ->contains($this->daniel->id)
    );

    $this->assertEquals(
        false,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[1]]['winning_player_ids'])
            ->contains($this->john->id)
    );

    $this->assertEquals(
        true,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[1]]['winning_player_ids'])
            ->contains($this->daniel->id)
    );

    $this->assertEquals(
        true,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[2]]['winning_player_ids'])
            ->contains($this->john->id)
    );

    $this->assertEquals(
        true,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[2]]['winning_player_ids'])
            ->contains($this->daniel->id)
    );

    $this->assertEquals(
        false,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[3]]['winning_player_ids'])
            ->contains($this->john->id)
    );

    $this->assertEquals(
        false,
        collect($this->game->currentRound()->state()->auction_winners[$bureaucrats[3]]['winning_player_ids'])
            ->contains($this->daniel->id)
    );
});

it('offers actions to players based on which Bureaucrats they won', function () {
    $bureaucrats = collect($this->game->currentRound()->state()->bureaucrats);

    $offers = $bureaucrats->mapWithKeys(fn ($b) => [$b => 2])->toArray();

    $this->john->submitOffers($this->game->currentRound(), $offers);

    $this->game->currentRound()->advancePhase();

    Verbs::commit();

    $this->assertEquals(
        true,
        collect($this->game->currentRound()->state()->actions[$this->john->id])
            ->pluck('class')
            ->contains($bureaucrats->first())
    );
});

it('spends the money offerred by winners', function () {
    $bureaucrats = collect($this->game->currentRound()->state()->bureaucrats);

    $this->assertEquals(10, $this->john->state()->money);

    $offers = $bureaucrats->mapWithKeys(fn ($b) => [$b => 2])->toArray();

    $this->john->submitOffers($this->game->currentRound(), $offers);

    $this->game->currentRound()->advancePhase();

    Verbs::commit();

    $this->assertEquals(0, $this->john->state()->money);
});
