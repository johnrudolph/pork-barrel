<?php

use App\Bureaucrats\GamblinGoat;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundConstructor\RoundConstructor;
use App\RoundModifiers\LameDuckSession;
use App\RoundModifiers\LegislativeFrenzy;
use App\RoundModifiers\TaxTheRich;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();
    $this->user_1 = User::factory()->create();
    $this->user_2 = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $this->user_1->id,
        game_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $this->user_2->id,
        player_id: Snowflake::make()->id(),
    );

    $this->game = Game::find($event->game_id);

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('takes 5 money from the richeset player at the end of the round', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [GamblinGoat::class],
        round_modifier: TaxTheRich::class,
    );

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 10);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(5, $this->daniel->state()->money);
});

it('changes the number of bureaucrats chosen for Lame Duck and Legislative Frenzy', function () {
    $constructor = new RoundConstructor(
        round: $this->game->rounds->first()->state(),
        round_modifier: LameDuckSession::class,
    );

    $this->assertEquals(2, collect($constructor->bureaucrats)->count());

    $constructor = new RoundConstructor(
        round: $this->game->rounds->first()->state(),
        round_modifier: LegislativeFrenzy::class,
    );

    $this->assertEquals(6, collect($constructor->bureaucrats)->count());
});