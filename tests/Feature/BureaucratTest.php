<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\GamblinGoat;
use App\Bureaucrats\MajorityLeaderMare;
use App\Bureaucrats\MinorityLeaderMink;
use App\Bureaucrats\ObstructionOx;
use App\Bureaucrats\TreasuryChicken;
use App\Bureaucrats\Watchdog;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundEnded;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundModifiers\RoundModifier;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

// @todo: in general, could we test this code without creating a whole game?
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
    GameStarted::fire(game_id: $this->game->id);

    Verbs::commit();

    $this->game = Game::find($event->game_id);

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('gives player random amount of money for winning Gamblin Goat', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [GamblinGoat::class],
        round_modifier: RoundModifier::class,
    );

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 10);
    Verbs::commit();

    $this->assertEquals(10, $this->john->state()->money);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    $amount_earned = $this->john->state()->money;

    $this->assertGreaterThan(0, $amount_earned);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => $amount_earned,
        'description' => "The Gamlin' Goat's scheme paid off!",
    ]);
});

it('blocks an action from resolving if was blocked by the Ox', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, ObstructionOx::class],
        round_modifier: RoundModifier::class,
    );

    $this->john->submitOffer($this->game->currentRound(), ObstructionOx::class, 10, ['bureaucrat' => BailoutBunny::class]);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 10);

    Verbs::commit();
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    $this->assertTrue(
        $this->game->currentRound()->state()->offers
            ->filter(fn ($o) => $o->bureaucrat === BailoutBunny::class)
            ->first()
            ->is_blocked === true
    );

    $this->assertFalse($this->daniel->state()->has_bailout);

    $this->assertEquals(0, $this->daniel->state()->money);

    $this->assertDatabaseHas('headlines', [
        'round_id' => $this->game->rounds->first()->id,
        'is_round_modifier' => false,
        'headline' => 'Bailout Bunny Ousted',
        'description' => 'The Obstructionist Ox blocked Bailout Bunny from taking action this round.',
    ]);
});

it('gives you a bailout if you ever reach 0 money after an auction', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        round_modifier: RoundModifier::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 10);
    Verbs::commit();

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    $this->assertEquals(10, $this->john->state()->money);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => 10,
        'description' => 'You received a bailout. No one needs a stronger safety net than you.',
    ]);
});

it('fines a player if they were caught by the watchdog', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, Watchdog::class],
        round_modifier: RoundModifier::class,
    );

    Verbs::commit();

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer(
        $this->game->currentRound(),
        Watchdog::class,
        1,
        ['bureaucrat' => BailoutBunny::class, 'player' => $this->john->id]
    );

    Verbs::commit();
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    $this->assertEquals(4, $this->john->state()->money);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => -5,
        'description' => 'Fined by the Watchdog. Bribery is not tolarated around these parts.',
    ]);
});

it('allows you to win with 1 less token if you have the Majority Leader Mare', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [MajorityLeaderMare::class],
        round_modifier: RoundModifier::class,
    );

    $this->john->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);

    Verbs::commit();
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class, BailoutBunny::class],
        round_modifier: RoundModifier::class,
    );

    // john and daniel should both get win the goat, despite daniel having a higher bid
    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 2);

    // john alone should win, despite having equal bids.
    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 2);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 2);

    Verbs::commit();
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => -1,
        'description' => "You had the highest bid for the Gamblin' Goat. Let's see how it pays off...",
    ]);

    $money_earned_from_goat = $this->john->state()->money - 16;

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => $money_earned_from_goat,
        'description' => "The Gamlin' Goat's scheme paid off!",
    ]);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => -2,
        'description' => 'You had the highest bid for the Bailout Bunny. The next time you reach 0 money, you will receive 10 money.',
    ]);

    $this->assertTrue($this->john->state()->has_bailout);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->daniel->id,
        'amount' => -2,
        'description' => "You had the highest bid for the Gamblin' Goat. Let's see how it pays off...",
    ]);

    $money_earned_from_goat = $this->daniel->state()->money - 18;

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->daniel->id,
        'amount' => $money_earned_from_goat,
        'description' => "The Gamlin' Goat's scheme paid off!",
    ]);

    $this->assertTrue($this->game->currentRound()->state()->
        actions_from_previous_rounds_that_resolve_this_round->first()['bureaucrat'] === MajorityLeaderMare::class
    );

    $this->assertTrue($this->game->currentRound()->next()->state()->
        actions_from_previous_rounds_that_resolve_this_round->count() === 0
    );

    $this->assertFalse($this->daniel->state()->has_bailout);
});

it('gives you 10 money if you make no offers after getting the minority leader mink', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [MinorityLeaderMink::class],
        round_modifier: RoundModifier::class,
    );

    $this->john->submitOffer($this->game->currentRound(), MinorityLeaderMink::class, 1);

    Verbs::commit();
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class],
        round_modifier: RoundModifier::class,
    );

    Verbs::commit();
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    $this->assertTrue($this->game->currentRound()->state()->
        actions_from_previous_rounds_that_resolve_this_round->first()['bureaucrat'] === MinorityLeaderMink::class
    );

    $this->assertTrue($this->game->currentRound()->next()->state()->
        actions_from_previous_rounds_that_resolve_this_round->count() === 0
    );

    $this->assertEquals(29, $this->john->state()->money);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => 10,
        'description' => "You made no offers. That'll show 'em",
    ]);
});

it('gives you a 50% return on your savings if you win the Treasury Chicken', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [TreasuryChicken::class],
        round_modifier: RoundModifier::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TreasuryChicken::class, 10);

    $this->endGame($this->game);

    $this->assertDatabaseHas('money_log_entries', [
        'player_id' => $this->john->id,
        'amount' => 12,
        'description' => 'Received 25% return on money saved in treasury',
    ]);
});
