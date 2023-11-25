<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\DisruptiveDonkey;
use App\Bureaucrats\GamblinGoat;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Headlines\Headline;
use App\Headlines\TaxTheRich;
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
        bureaucrats: [GamblinGoat::class],
        headline: Headline::class,
    );

    $this->game->players
        ->each(fn ($p) => $p->receiveMoney(1, 'Received starting money.'));

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);

    $this->assertEquals(1, $this->john->state()->money);

    $this->game->currentRound()->endAuctionPhase();
    Verbs::commit();
    $this->game->currentRound()->endRound();
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
        bureaucrats: [GamblinGoat::class, DisruptiveDonkey::class],
        headline: TaxTheRich::class,
    );

    $this->game->players
        ->each(fn ($p) => $p->receiveMoney(1, 'Received starting money.'));

    $this->john->submitOffer($this->game->currentRound(), DisruptiveDonkey::class, 1, ['bureaucrat' => GamblinGoat::class]);
    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);

    Verbs::commit();

    $this->game->currentRound()->endAuctionPhase();
    Verbs::commit();

    $this->assertTrue(collect($this->game->currentRound()->state()->blocked_actions)
        ->contains(GamblinGoat::class));

    $this->game->currentRound()->endRound();
    Verbs::commit();

    $this->assertEquals(0, $this->daniel->state()->money);
});

it('gives you a bailout if you ever reach 0 money after an auction', function () {
    GameStarted::fire(game_id: $this->game->id);

    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        headline: Headline::class,
    );

    $this->game->players
        ->each(fn ($p) => $p->receiveMoney(1, 'Received starting money.'));

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    Verbs::commit();

    $this->game->currentRound()->endAuctionPhase();
    Verbs::commit();

    $this->game->currentRound()->endRound();
    Verbs::commit();

    $this->assertEquals(10, $this->john->state()->money);
});
