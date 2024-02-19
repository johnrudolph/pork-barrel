<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\BearhugBrownBear;
use App\Bureaucrats\BrinksmanshipBronco;
use App\Bureaucrats\Bureaucrat;
use App\Bureaucrats\CopyCat;
use App\Bureaucrats\CronyCrocodile;
use App\Bureaucrats\DilemmaDinosaur;
use App\Bureaucrats\DoubleDonkey;
use App\Bureaucrats\FeeCollectingFerret;
use App\Bureaucrats\FocusedFoal;
use App\Bureaucrats\ForecastFox;
use App\Bureaucrats\FrozenFrog;
use App\Bureaucrats\FrugalFruitFly;
use App\Bureaucrats\GamblinGoat;
use App\Bureaucrats\IndexIbex;
use App\Bureaucrats\MajorityLeaderMare;
use App\Bureaucrats\MinorityLeaderMink;
use App\Bureaucrats\MuckrakingMule;
use App\Bureaucrats\ObstructionOx;
use App\Bureaucrats\PonziPony;
use App\Bureaucrats\RejectedReindeer;
use App\Bureaucrats\SubsidySloth;
use App\Bureaucrats\TaxTurkey;
use App\Bureaucrats\TiedHog;
use App\Bureaucrats\TreasuryChicken;
use App\Bureaucrats\Watchdog;
use App\Events\AuctionEnded;
use App\Events\RoundStarted;
use App\RoundTemplates\RoundTemplate;
use App\States\OfferState;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

// @todo: in general, could we test this code without creating a whole game?
beforeEach(function () {
    Verbs::commitImmediately();

    $this->bootGame();
});

it('gives player random amount of money for winning Gamblin Goat', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 5);

    $this->assertEquals(5, $this->john->state()->availableMoney());

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $amount_earned = $this->john->state()->availableMoney();

    $this->assertGreaterThan(0, $amount_earned);

    $this->assertEquals(
        $amount_earned,
        $this->john->state()->money_history
            ->filter(fn ($entry) => $entry->type === 'award')
            ->first()
            ->amount
    );
});

it('blocks an action from resolving if was blocked by the Ox', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, DilemmaDinosaur::class, ObstructionOx::class],
        round_template: RoundTemplate::class,
    );

    $this->jacob->submitOffer($this->game->currentRound(), ObstructionOx::class, 5, ['bureaucrat' => DilemmaDinosaur::class]);
    $this->john->submitOffer($this->game->currentRound(), ObstructionOx::class, 5, ['bureaucrat' => BailoutBunny::class]);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 5);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue(
        $this->game->currentRound()->state()->offers()
            ->filter(fn ($o) => $o->bureaucrat === BailoutBunny::class)
            ->first()
            ->is_blocked === true
    );

    $this->assertFalse($this->daniel->state()->perks->contains(BailoutBunny::class));

    $this->assertEquals(5, $this->daniel->state()->availableMoney());

    $this->assertDatabaseHas('headlines', [
        'round_id' => $this->game->rounds->first()->id,
        'is_round_template' => false,
        'headline' => 'Bailout Bunny Ousted',
        'description' => 'The Obstructionist Ox blocked Bailout Bunny from taking action this round.',
    ]);
});

it('gives you a bailout if you ever reach 0 money after an auction', function () {
    dd($this->john->state()->money_history);
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TreasuryChicken::class, 9);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(10, $this->john->state()->availableMoney());
});

it('fines a player if they were caught by the watchdog', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [MajorityLeaderMare::class, Watchdog::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);
    $this->daniel->submitOffer(
        $this->game->currentRound(),
        Watchdog::class,
        1,
        ['bureaucrat' => MajorityLeaderMare::class, 'player' => $this->john->id]
    );

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(0, $this->john->state()->availableMoney());
});

it('allows you to win with 1 less token if you have the Majority Leader Mare', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [MajorityLeaderMare::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class, BailoutBunny::class],
        round_template: RoundTemplate::class,
    );

    // john and daniel should both get win the goat, despite daniel having a higher bid
    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 2);

    // john alone should win, despite having equal bids.
    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 2);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 2);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(
        1,
        $this->game->currentRound()->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === GamblinGoat::class)
            ->first()
            ->amount_modified
    );

    $this->assertTrue(
        $this->game->currentRound()->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === GamblinGoat::class)
            ->first()
            ->awarded
    );

    $this->assertTrue(
        $this->game->currentRound()->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->daniel->id && $o->bureaucrat === GamblinGoat::class)
            ->first()
            ->awarded
    );

    $this->assertEquals(
        1,
        $this->game->currentRound()->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === BailoutBunny::class)
            ->first()
            ->amount_modified
    );

    $this->assertTrue($this->john->state()->perks->contains(BailoutBunny::class));

    $this->assertTrue(OfferState::load($this->game->currentRound()->state()->
        offers_from_previous_rounds_that_resolve_this_round->first())
            ->bureaucrat === MajorityLeaderMare::class
    );

    $this->assertTrue($this->game->currentRound()->next()->state()->
        offers_from_previous_rounds_that_resolve_this_round->count() === 0
    );

    $this->assertFalse($this->daniel->state()->perks->contains(BailoutBunny::class));
});

it('gives you 10 money if you make no offers after getting the minority leader mink', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [MinorityLeaderMink::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), MinorityLeaderMink::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue(OfferState::load($this->game->currentRound()->state()->
        offers_from_previous_rounds_that_resolve_this_round->first())->bureaucrat === MinorityLeaderMink::class
    );

    $this->assertTrue($this->game->currentRound()->next()->state()->
        offers_from_previous_rounds_that_resolve_this_round->count() === 0
    );

    $this->assertEquals(19, $this->john->state()->availableMoney());
});

it('gives you a 25% return on your savings if you win the Treasury Chicken', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TreasuryChicken::class, 4);

    $this->endGame($this->game);

    $this->assertEquals(
        5,
        $this->john->state()->money_history
            ->filter(fn ($entry) => $entry->description === 'Received 50% return on money saved in treasury')
            ->first()
            ->amount
    );
});

it('allocates offers to winners for the Brinksmanship Bronco', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [BrinksmanshipBronco::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BrinksmanshipBronco::class, 4);
    $this->daniel->submitOffer($this->game->currentRound(), BrinksmanshipBronco::class, 5);
    $this->jacob->submitOffer($this->game->currentRound(), BrinksmanshipBronco::class, 5);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(1, $this->john->state()->availableMoney());
    $this->assertEquals(2, $this->daniel->state()->availableMoney());
    $this->assertEquals(2, $this->jacob->state()->availableMoney());
});

it('doubles the offer for all losers of Ponzi Pony', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [PonziPony::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), PonziPony::class, 4);
    $this->daniel->submitOffer($this->game->currentRound(), PonziPony::class, 5);
    $this->jacob->submitOffer($this->game->currentRound(), PonziPony::class, 5);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(9, $this->john->state()->availableMoney());
    $this->assertEquals(0, $this->daniel->state()->availableMoney());
    $this->assertEquals(0, $this->jacob->state()->availableMoney());
});

it('adjusts income for Crony Crocodile and Tax Turkey', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [CronyCrocodile::class, TaxTurkey::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), CronyCrocodile::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), TaxTurkey::class, 1, ['player' => $this->jacob->id]);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(6, $this->john->state()->income);
    $this->assertEquals(5, $this->daniel->state()->income);
    $this->assertEquals(4, $this->jacob->state()->income);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [Bureaucrat::class],
        round_template: RoundTemplate::class,
    );

    $this->assertEquals(10, $this->john->state()->availableMoney());
    $this->assertEquals(9, $this->daniel->state()->availableMoney());
    $this->assertEquals(9, $this->jacob->state()->availableMoney());
});

it('rewards players for correctly guessing who will be in first or last place', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [SubsidySloth::class, ForecastFox::class, BailoutBunny::class],
        round_template: RoundTemplate::class,
    );

    // john is the poorest. Jacob and Daniel are tied for richest. Daniel's guesses are right, Jacob's are wrong
    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 4);
    $this->daniel->submitOffer($this->game->currentRound(), SubsidySloth::class, 1, ['player' => $this->john->id]);
    $this->daniel->submitOffer($this->game->currentRound(), ForecastFox::class, 1, ['player' => $this->daniel->id]);
    $this->jacob->submitOffer($this->game->currentRound(), SubsidySloth::class, 1, ['player' => $this->daniel->id]);
    $this->jacob->submitOffer($this->game->currentRound(), ForecastFox::class, 1, ['player' => $this->john->id]);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(1, $this->john->state()->availableMoney());
    $this->assertEquals(3, $this->daniel->state()->availableMoney());
    $this->assertEquals(3, $this->jacob->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [Bureaucrat::class],
        round_template: RoundTemplate::class,
    );

    $this->assertEquals(13, $this->john->state()->availableMoney());
    $this->assertEquals(15, $this->daniel->state()->availableMoney());
    $this->assertEquals(8, $this->jacob->state()->availableMoney());
});

it('rewards players for guessing which player belongs to an industry', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [MuckrakingMule::class],
        round_template: RoundTemplate::class,
    );

    $this->daniel->submitOffer(
        $this->game->currentRound(),
        MuckrakingMule::class,
        1,
        [
            'player' => $this->john->id,
            'industry' => $this->john->state()->industry,
        ]
    );

    $this->jacob->submitOffer(
        $this->game->currentRound(),
        MuckrakingMule::class,
        1,
        [
            'player' => $this->john->id,
            'industry' => $this->daniel->state()->industry,
        ]
    );

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(5, $this->john->state()->availableMoney());
    $this->assertEquals(9, $this->daniel->state()->availableMoney());
    $this->assertEquals(4, $this->jacob->state()->availableMoney());

    $this->assertDatabaseHas('headlines', [
        'headline' => 'Muckraking Mule Exposes Lobbyist',
        'description' => "{$this->john->user->name} was exposed as a corporate lobbyist for {$this->john->state()->industry}.",
    ]);
});

it('freezes half the moeny of a player with the Frozen Frog', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [FrozenFrog::class],
        round_template: RoundTemplate::class,
    );

    $this->daniel->submitOffer($this->game->currentRound(), FrozenFrog::class, 1, ['player' => $this->john->id]);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(2, $this->john->state()->availableMoney());
    $this->assertEquals(4, $this->daniel->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [Bureaucrat::class],
        round_template: RoundTemplate::class,
    );

    $this->assertEquals(7, $this->john->state()->availableMoney());
    $this->assertEquals(9, $this->daniel->state()->availableMoney());

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(10, $this->john->state()->availableMoney());
    $this->assertEquals(9, $this->daniel->state()->availableMoney());
});

it('does the prisoners dilemma for the Dilemma Dino', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [DilemmaDinosaur::class],
        round_template: RoundTemplate::class,
    );

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(10, $this->john->state()->availableMoney());
    $this->assertEquals(10, $this->daniel->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [DilemmaDinosaur::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), DilemmaDinosaur::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(17, $this->john->state()->availableMoney());
    $this->assertEquals(15, $this->daniel->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [DilemmaDinosaur::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), DilemmaDinosaur::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), DilemmaDinosaur::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(20, $this->john->state()->availableMoney());
    $this->assertEquals(18, $this->daniel->state()->availableMoney());
});

it('doubles your earnings with the Double Donkey', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [DoubleDonkey::class, GamblinGoat::class, DilemmaDinosaur::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), DoubleDonkey::class, 3);
    $this->john->submitOffer($this->game->currentRound(), DilemmaDinosaur::class, 1);
    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $amount_from_goat = $this->john->state()->money_history
        ->where('description', "The Gamlin' Goat's scheme paid off!")
        ->first()
        ->amount;

    $amount_from_dino = 3;

    $this->assertEquals($amount_from_goat + $amount_from_dino, $this->john->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [Bureaucrat::class],
        round_template: RoundTemplate::class,
    );

    $doubled_earnings = $amount_from_goat + $amount_from_dino;

    $this->assertEquals(5 + $amount_from_goat + $amount_from_dino + $doubled_earnings, $this->john->state()->availableMoney());
});

it('breaks ties with the Tied Hog', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [TiedHog::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TiedHog::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue($this->john->state()->perks->contains(TiedHog::class));

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $round_2 = $this->game->currentRound();

    $this->john->submitOffer($round_2, GamblinGoat::class, 1);
    $this->daniel->submitOffer($round_2, GamblinGoat::class, 1);

    AuctionEnded::fire(round_id: $round_2->id);

    $this->assertEquals(
        0,
        $round_2->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->daniel->id && $o->bureaucrat === GamblinGoat::class)
            ->first()
            ->netOffer()
    );

    $this->assertEquals(
        10,
        $this->daniel->state()->availableMoney()
    );
});

it('copies the net earnings of another player with Copy Cat', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [CopyCat::class, TreasuryChicken::class, GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), CopyCat::class, 5, ['player' => $this->daniel->id]);

    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), TreasuryChicken::class, 4);
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $this->assertEquals(
        $this->daniel->state()->availableMoney() - 5,
        $this->john->state()->availableMoney()
    );
});

it('copies the average net earnings of all players with Index Ibex', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [IndexIbex::class, TreasuryChicken::class, GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), IndexIbex::class, 5);

    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);
    $this->jacob->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), TreasuryChicken::class, 4);
    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $johns_net = $this->john->state()->availableMoney() - 5;
    $daniels_net = $this->john->state()->availableMoney() - 5;
    $jacobs_net = $this->john->state()->availableMoney() - 5;

    $average = ($johns_net + $daniels_net + $jacobs_net) / 3;

    $this->assertEquals(
        $johns_net,
        $average
    );
});

it('only spends what is necessary with the Frugal Fruit Fly', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [FrugalFruitFly::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), FrugalFruitFly::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue($this->john->state()->perks->contains(FrugalFruitFly::class));

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class, TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $round_2 = $this->game->currentRound();

    $this->john->submitOffer($round_2, GamblinGoat::class, 4);
    $this->daniel->submitOffer($round_2, GamblinGoat::class, 1);
    $this->john->submitOffer($round_2, TreasuryChicken::class, 4);

    AuctionEnded::fire(round_id: $round_2->id);

    $this->assertEquals(
        2,
        $round_2->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === GamblinGoat::class)
            ->first()
            ->netOffer()
    );

    $this->assertEquals(
        1,
        $round_2->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === TreasuryChicken::class)
            ->first()
            ->netOffer()
    );
});

it('adds to your offer if you only make one offer per round with Focused Foal', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [FocusedFoal::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), FocusedFoal::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue($this->john->state()->perks->contains(FocusedFoal::class));

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class, TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $round_2 = $this->game->currentRound();

    $this->john->submitOffer($round_2, GamblinGoat::class, 1);

    AuctionEnded::fire(round_id: $round_2->id);

    $this->assertEquals(
        6,
        $round_2->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === GamblinGoat::class)
            ->first()
            ->netOffer()
    );

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [GamblinGoat::class, TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $round_3 = $this->game->currentRound();

    $this->john->submitOffer($round_3, GamblinGoat::class, 1);
    $this->john->submitOffer($round_3, TreasuryChicken::class, 1);

    AuctionEnded::fire(round_id: $round_3->id);

    $this->assertEquals(
        1,
        $round_3->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === GamblinGoat::class)
            ->first()
            ->netOffer()
    );

    $this->assertEquals(
        1,
        $round_3->state()->offers()
            ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === TreasuryChicken::class)
            ->first()
            ->netOffer()
    );
});

it('steals a Perk with the Bearhug Brown Bear', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [BailoutBunny::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue($this->john->state()->perks->contains(BailoutBunny::class));

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [BearhugBrownBear::class],
        round_template: RoundTemplate::class,
    );

    $round_2 = $this->game->currentRound();

    $this->john->submitOffer($round_2, BearhugBrownBear::class, 1, ['player' => $this->daniel->id]);
    $this->daniel->submitOffer($round_2, BearhugBrownBear::class, 1, ['player' => $this->john->id]);

    AuctionEnded::fire(round_id: $round_2->id);

    $this->assertFalse($this->john->state()->perks->contains(BailoutBunny::class));
    $this->assertTrue($this->daniel->state()->perks->contains(BailoutBunny::class));

    // john lost 5 money for attempting a bearhug on daniel
    $this->assertEquals(3, $this->john->state()->availableMoney());
});

it('compensates you when you have no offers accepted with Rejected Reindeer', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [RejectedReindeer::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), RejectedReindeer::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue($this->john->state()->perks->contains(RejectedReindeer::class));

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $round_2 = $this->game->currentRound();

    $this->john->submitOffer($round_2, GamblinGoat::class, 1);
    $this->daniel->submitOffer($round_2, GamblinGoat::class, 2);

    AuctionEnded::fire(round_id: $round_2->id);

    $this->assertEquals(13, $this->john->state()->availableMoney());
});

it('gives you 1 token for every opponent who offered on auctions you lost for Fee Collecting Ferret', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [FeeCollectingFerret::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), FeeCollectingFerret::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertTrue($this->john->state()->perks->contains(FeeCollectingFerret::class));

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class, TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $round_2 = $this->game->currentRound();

    $this->john->submitOffer($round_2, GamblinGoat::class, 1);
    $this->john->submitOffer($round_2, TreasuryChicken::class, 1);
    $this->daniel->submitOffer($round_2, GamblinGoat::class, 2);
    $this->daniel->submitOffer($round_2, TreasuryChicken::class, 2);
    $this->jacob->submitOffer($round_2, TreasuryChicken::class, 1);

    AuctionEnded::fire(round_id: $round_2->id);

    $this->assertEquals(12, $this->john->state()->availableMoney());
});
