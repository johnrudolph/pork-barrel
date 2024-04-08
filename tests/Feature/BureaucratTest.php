<?php

use App\States\OfferState;
use App\Bureaucrats\CopyCat;
use App\Bureaucrats\TiedHog;
use App\Events\AuctionEnded;
use App\Events\RoundStarted;
use App\Bureaucrats\Watchdog;
use App\Bureaucrats\IndexIbex;
use App\Bureaucrats\PonziPony;
use App\Bureaucrats\TaxTurkey;
use Thunk\Verbs\Facades\Verbs;
use App\Bureaucrats\Bureaucrat;
use App\Bureaucrats\FrozenFrog;
use App\Bureaucrats\EqualityElk;
use App\Bureaucrats\FocusedFoal;
use App\Bureaucrats\ForecastFox;
use App\Bureaucrats\GamblinGoat;
use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\DoubleDonkey;
use App\Bureaucrats\SubsidySloth;
use App\Bureaucrats\LoyaltyLocust;
use App\Bureaucrats\ObstructionOx;
use App\Bureaucrats\ConsolationCow;
use App\Bureaucrats\CronyCrocodile;
use App\Bureaucrats\FrugalFruitFly;
use App\Bureaucrats\TreasuryChicken;
use App\Bureaucrats\BearhugBrownBear;
use App\Bureaucrats\InterestInchworm;
use App\Bureaucrats\RejectedReindeer;
use App\Events\PlayerAwaitingResults;
use App\RoundTemplates\RoundTemplate;
use App\RoundTemplates\CampaignSeason;
use App\Bureaucrats\KickbackKingfisher;
use App\Bureaucrats\MajorityLeaderMare;
use App\Bureaucrats\MinorityLeaderMink;
use App\RoundTemplates\LameDuckSession;
use App\Bureaucrats\BrinksmanshipBronco;
use App\Bureaucrats\FeeCollectingFerret;
use App\RoundTemplates\AlwaysABridesmaid;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

    $this->endCurrentRound();

    $amount_earned = $this->john->state()->availableMoney();

    $this->assertGreaterThan(0, $amount_earned);

    $this->assertEquals(
        $amount_earned,
        $this->john->state()->money_history
            ->filter(fn ($entry) => $entry->type === 'bureaucrat_reward')
            ->first()
            ->amount
    );
});

it('blocks an action from resolving if was blocked by the Ox', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, ObstructionOx::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), ObstructionOx::class, 5, ['bureaucrat' => BailoutBunny::class]);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 5);

    $this->endCurrentRound();

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
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TreasuryChicken::class, 9);

    $this->endCurrentRound();

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

    $this->endCurrentRound();

    $this->assertEquals(0, $this->john->state()->availableMoney());
});

it('adds 1 token to your offers if you have the Majority Leader Mare', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [MajorityLeaderMare::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);

    $this->endCurrentRound();

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

    $this->endCurrentRound();

    $johns_gambling_goat_offer = $this->game->currentRound()->state()->offers()
        ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === GamblinGoat::class)
        ->first();

    $johns_bunny_offer = $this->game->currentRound()->state()->offers()
        ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === BailoutBunny::class)
        ->first();

    $daniels_gambling_goat_offer = $this->game->currentRound()->state()->offers()
        ->filter(fn ($o) => $o->player_id === $this->daniel->id && $o->bureaucrat === GamblinGoat::class)
        ->first();

    $daniels_bunny_offer = $this->game->currentRound()->state()->offers()
        ->filter(fn ($o) => $o->player_id === $this->daniel->id && $o->bureaucrat === BailoutBunny::class)
        ->first();

    $this->assertEquals(
        1,
        $johns_gambling_goat_offer->amount_modifications[0]['amount']
    );

    $this->assertEquals(
        2,
        $johns_gambling_goat_offer->netOffer()
    );

    $this->assertTrue($johns_gambling_goat_offer->awarded);

    $this->assertTrue($daniels_gambling_goat_offer->awarded);

    $this->assertEquals(
        1,
        $johns_bunny_offer->amount_modifications[0]['amount']
    );

    $this->assertEquals(
        3,
        $johns_bunny_offer->netOffer()
    );

    $this->assertTrue($this->john->state()->perks->contains(BailoutBunny::class));

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

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $this->endCurrentRound();

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
    $this->daniel->submitOffer($this->game->currentRound(), TreasuryChicken::class, 2);

    $this->endGame($this->game);

    $this->assertEquals(
        5,
        $this->john->state()->money_history
            ->filter(fn ($entry) => $entry->description === 'Received 25% return on money saved in treasury')
            ->first()
            ->amount
    );

    $this->assertEquals(
        2,
        $this->daniel->state()->money_history
            ->filter(fn ($entry) => $entry->description === 'Received 25% return on money saved in treasury')
            ->first()
            ->amount
    );
});

it('changes the interest rate with Interest Inchworm', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [TreasuryChicken::class, InterestInchworm::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TreasuryChicken::class, 5);
    $this->jacob->submitOffer($this->game->currentRound(), InterestInchworm::class, 1, ['choice' => 'increase']);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [TreasuryChicken::class, InterestInchworm::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TreasuryChicken::class, 5);
    $this->jacob->submitOffer($this->game->currentRound(), InterestInchworm::class, 1, ['choice' => 'increase']);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [TreasuryChicken::class, InterestInchworm::class],
        round_template: RoundTemplate::class,
    );

    $this->jacob->submitOffer($this->game->currentRound(), InterestInchworm::class, 1, ['choice' => 'decrease']);

    $this->endGame($this->game);

    $this->assertEquals(
        13,
        $this->john->state()->money_history
            ->filter(fn ($entry) => $entry->description === 'Received 35% return on money saved in treasury')
            ->first()
            ->amount
    );
});

it('allocates losing offers to winners for the Brinksmanship Bronco', function () {
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

    $this->endCurrentRound();

    $this->assertEquals(1, $this->john->state()->availableMoney());
    $this->assertEquals(2, $this->daniel->state()->availableMoney());
    $this->assertEquals(2, $this->jacob->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [BrinksmanshipBronco::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BrinksmanshipBronco::class, 1);

    $this->endCurrentRound();

    $this->assertEquals(15, $this->john->state()->availableMoney());
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

    $this->endCurrentRound();

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

    $this->endCurrentRound();

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

    $this->endCurrentRound();

    $this->assertEquals(8, $this->john->state()->availableMoney());
    $this->assertEquals(10, $this->daniel->state()->availableMoney());
    $this->assertEquals(3, $this->jacob->state()->availableMoney());
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

    $this->endCurrentRound();

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

    $this->endCurrentRound();

    $this->assertEquals(10, $this->john->state()->availableMoney());
    $this->assertEquals(9, $this->daniel->state()->availableMoney());
});

it('doubles your earnings with the Double Donkey', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [DoubleDonkey::class, LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), DoubleDonkey::class, 3);
    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 2);

    $this->endCurrentRound();

    $this->assertEquals(4, $this->john->state()->availableMoney());
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

    $this->endCurrentRound();

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

    $goat_offer = $round_2->state()->offers()
        ->filter(fn ($o) => $o->player_id === $this->daniel->id && $o->bureaucrat === GamblinGoat::class)
        ->first();

    $this->assertEquals(
        0,
        $goat_offer->netOffer()
    );

    $this->assertEquals(
        -1,
        $goat_offer->amount_modifications[0]['amount']
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
    $this->endCurrentRound();

    $this->assertEquals(
        $this->daniel->state()->availableMoney(),
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
    $this->endCurrentRound();

    $johns_net = $this->john->state()->availableMoney();
    $daniels_net = $this->john->state()->availableMoney();
    $jacobs_net = $this->john->state()->availableMoney();

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

    $this->endCurrentRound();

    $this->assertTrue($this->john->state()->perks->contains(FrugalFruitFly::class));

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [GamblinGoat::class, LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $round_2 = $this->game->currentRound();

    $this->daniel->submitOffer($round_2, LoyaltyLocust::class, 1);
    $this->john->submitOffer($round_2, LoyaltyLocust::class, 4);

    AuctionEnded::fire(round_id: $round_2->id);

    $locust = $round_2->state()->offers()
        ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === LoyaltyLocust::class)
        ->first();

    $this->assertEquals(
        2,
        $locust->netOffer()
    );

    $this->assertEquals(
        -2,
        $locust->amount_modifications[0]['amount']
    );

    $this->assertEquals(
        9,
        $this->john->state()->availableMoney()
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

    $this->endCurrentRound();

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

    PlayerAwaitingResults::fire(
        player_id: $this->john->id,
        round_id: $round_2->id,
    );

    AuctionEnded::fire(round_id: $round_2->id);

    $goat_offer = $round_2->state()->offers()
        ->filter(fn ($o) => $o->player_id === $this->john->id && $o->bureaucrat === GamblinGoat::class)
        ->first();

    $this->assertEquals(
        6,
        $goat_offer->netOffer()
    );

    $this->assertEquals(
        5,
        $goat_offer->amount_modifications[0]['amount']
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

    $this->endCurrentRound();

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

    $this->endCurrentRound();

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

    $this->endCurrentRound();

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

it('doubles your Locust earnings every time you win it', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);

    $this->endCurrentRound();

    $this->assertEquals(6, $this->john->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);

    $this->endCurrentRound();

    $this->assertEquals(14, $this->john->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);
    $this->jacob->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);

    $this->endCurrentRound();

    $this->assertEquals(26, $this->john->state()->availableMoney());
    $this->assertEquals(16, $this->jacob->state()->availableMoney());
});

it('gives you 1 money for each offer you lost with Consolation Cow', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);
    $this->jacob->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 2);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);
    $this->jacob->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 2);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [ConsolationCow::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), ConsolationCow::class, 1);

    $this->endCurrentRound();

    $this->assertEquals(16, $this->john->state()->availableMoney());
});

it('adds 10 money to your offers with Equality Elk', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [EqualityElk::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), EqualityElk::class, 1);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [LoyaltyLocust::class, TreasuryChicken::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);
    $this->john->submitOffer($this->game->currentRound(), TreasuryChicken::class, 1);
    $this->jacob->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 2);

    $round_2 = $this->game->currentRound();

    $this->endCurrentRound();

    $locust_offer = $round_2->state()->offers()
        ->filter(fn ($o) => $o->bureaucrat === LoyaltyLocust::class && $o->player_id === $this->john->id)
        ->first();

    $chicken_offer = $round_2->state()->offers()
        ->filter(fn ($o) => $o->bureaucrat === TreasuryChicken::class && $o->player_id === $this->john->id)
        ->first();

    $this->assertEquals(11, $locust_offer->netOffer());
    $this->assertTrue($locust_offer->awarded);
    $this->assertEquals(1, $chicken_offer->netOffer());
});

it('rewards you for 20 percent of earnings with Kickback Kingfisher', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);

    $this->endCurrentRound();

    $this->assertEquals(26, $this->john->state()->availableMoney());

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 4,
        round_id: $this->game->state()->round_ids[3],
        bureaucrats: [KickbackKingfisher::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), KickbackKingfisher::class, 1);

    $this->endCurrentRound();

    $this->assertEquals(32, $this->john->state()->availableMoney());
});

it('regression test for interaction of Elk and Fruit Fly', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [EqualityElk::class, FrugalFruitFly::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), EqualityElk::class, 1);
    $this->john->submitOffer($this->game->currentRound(), FrugalFruitFly::class, 1);

    $this->endCurrentRound();

    // Fruit Fly has no effect because John only offered 1 and had no competitors

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);

    $locust_offer = $this->game->currentRound()->state()->offers()
        ->filter(fn ($o) => $o->bureaucrat === LoyaltyLocust::class)
        ->first();

    $this->endCurrentRound();

    $this->assertEquals(9, $this->john->state()->availableMoney());
    $this->assertEquals(11, $locust_offer->netOffer());
    $this->assertEquals(1, $locust_offer->amountToChargePlayer());

    // Fruit Fly modifies by only the amount overpaid, even though elk makes the netOffer huge

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 14);
    $this->daniel->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 10);

    $locust_offer = $this->game->currentRound()->state()->offers()
        ->filter(fn ($o) => $o->bureaucrat === LoyaltyLocust::class
            && $o->player_id === $this->john->id
        )
        ->first();

    $this->endCurrentRound();

    $this->assertEquals(7, $this->john->state()->availableMoney());
    $this->assertEquals(11, $locust_offer->amountToChargePlayer());

    // Fruit Fly modifies nothing because the offer was not overpaid, despite the netOffer() being huge

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 4,
        round_id: $this->game->state()->round_ids[3],
        bureaucrats: [LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 5);

    $locust_offer = $this->game->currentRound()->state()->offers()
        ->filter(fn ($o) => $o->bureaucrat === LoyaltyLocust::class
            && $o->player_id === $this->john->id
        )
        ->first();

    $this->endCurrentRound();

    // dd($this->daniel->state()->money_history->toArray());

    $this->assertEquals(19, $this->john->state()->availableMoney());
    $this->assertEquals(1, $locust_offer->amountToChargePlayer());
});

it('regression test for interaction of Watchdog and Tied Hog', function () {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [TiedHog::class],
        round_template: RoundTemplate::class,
    );

    $this->john->submitOffer($this->game->currentRound(), TiedHog::class, 1);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [Watchdog::class, LoyaltyLocust::class],
        round_template: RoundTemplate::class,
    );

    // since we tied, Daniel's offer is modified, and John should win outright
    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 2);
    $this->daniel->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 2);

    // Daniel guesses John wins
    $this->daniel->submitOffer(
        $this->game->currentRound(),
        Watchdog::class,
        1,
        ['bureaucrat' => LoyaltyLocust::class, 'player' => $this->john->id]
    );

    // Jacob guesses Daniel wins
    $this->jacob->submitOffer(
        $this->game->currentRound(),
        Watchdog::class,
        1,
        ['bureaucrat' => LoyaltyLocust::class, 'player' => $this->daniel->id]
    );

    $this->endCurrentRound();

    $this->assertEquals(4, $this->john->state()->availableMoney());
    $this->assertEquals(9, $this->daniel->state()->availableMoney());
});

it('regression test for minority leader not working in specific game', function() {
    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->state()->round_ids[0],
        bureaucrats: [
            BailoutBunny::class,
            RejectedReindeer::class,
            TiedHog::class,
            FrugalFruitFly::class,
            CronyCrocodile::class,
        ],
        round_template: AlwaysABridesmaid::class,
    );

    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), RejectedReindeer::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), TiedHog::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), FrugalFruitFly::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), CronyCrocodile::class, 1);
    $this->john->submitOffer($this->game->currentRound(), FrugalFruitFly::class, 2);
    $this->john->submitOffer($this->game->currentRound(), CronyCrocodile::class, 3);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [
            FocusedFoal::class,
            FeeCollectingFerret::class,
            MinorityLeaderMink::class,
            EqualityElk::class,
            LoyaltyLocust::class,
        ],
        round_template: CampaignSeason::class,
    );


    $this->daniel->submitOffer($this->game->currentRound(), FocusedFoal::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), FeeCollectingFerret::class, 2);
    $this->daniel->submitOffer($this->game->currentRound(), MinorityLeaderMink::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), EqualityElk::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 2);
    $this->john->submitOffer($this->game->currentRound(), LoyaltyLocust::class, 6);

    $this->endCurrentRound();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 3,
        round_id: $this->game->state()->round_ids[2],
        bureaucrats: [
            ForecastFox::class,
            TreasuryChicken::class,
        ],
        round_template: LameDuckSession::class,
    );

    $this->john->submitOffer($this->game->currentRound(), ForecastFox::class, 7, ['player' => $this->john->id]);

    $this->endCurrentRound();

    $this->assertEquals($this->daniel->state()->availableMoney(), 25);
});