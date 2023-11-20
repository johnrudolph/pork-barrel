<?php

use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use App\DTOs\ActionDTO;
use Glhd\Bits\Snowflake;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\RoundStarted;
use Thunk\Verbs\Facades\Verbs;
use App\Bureaucrats\GamblinGoat;
use App\Events\PlayerJoinedGame;
use App\Events\DecisionsSubmitted;
use App\Bureaucrats\DisruptiveDonkey;
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

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('gives player random amount of money for winning Gamblin Goat', function () {
    GameStarted::fire(game_id: $this->game->id);

    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [GamblinGoat::class]
    );

    $this->game->players
        ->each(fn ($p) => $p->receiveMoney(1, 'Received starting money.'));

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);

    $this->assertEquals(1, $this->john->state()->money);

    $this->game->currentRound()->endAuctionPhase();
    Verbs::commit();

    $this->assertEquals(0, $this->john->state()->money);

    $this->game->currentRound()->endDecisionPhase();
    Verbs::commit();

    $amount_earned = $this->john->state()->money;

    $this->assertGreaterThan(0, $amount_earned);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => $amount_earned,
        'description' => "The Gamlin' Goat's scheme paid off!",
    ]);
});

it('blocks an action from resolving if was blocked by the Donkey', function () {
    GameStarted::fire(game_id: $this->game->id);

    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [GamblinGoat::class, DisruptiveDonkey::class]
    );

    $this->game->players
        ->each(fn ($p) => $p->receiveMoney(1, 'Received starting money.'));

    $this->john->submitOffers($this->game->currentRound(), [
        GamblinGoat::class => 0,
        DisruptiveDonkey::class => 1,
    ]);

    $this->daniel->submitOffers($this->game->currentRound(), [
        GamblinGoat::class => 1,
        DisruptiveDonkey::class => 0,
    ]);

    Verbs::commit();

    $this->game->currentRound()->advancePhase();
    Verbs::commit();

    dd($this->game->currentRound()->state()->actions);

    $this->assertEquals($this->game->currentRound()->state()->actions[0], [
        [
            'player_id' => $this->john->id,
            'class' => GamblinGoat::class,
            'requires_decision' => false,
            'options' => null,
        ],
        [
            'player_id' => $this->daniel->id,
            'class' => GamblinGoat::class,
            'requires_decision' => false,
            'options' => null,
        ],
    ]);

    DecisionsSubmitted::fire(
        round_id: $this->game->currentRound()->id,
        player_id: $this->john->id,
        decisions: [
            DisruptiveDonkey::class => GamblingGoat::class,
        ],
    );

    Verbs::commit();

    $this->game->currentRound()->advancePhase();
    Verbs::commit();

    $this->assertEquals(0, $this->daniel->state()->money);
});
