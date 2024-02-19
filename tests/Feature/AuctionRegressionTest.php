<?php

use App\Models\Round;
use App\DTOs\OfferDTO;
use Livewire\Livewire;
use App\Events\RoundEnded;
use App\Bureaucrats\TiedHog;
use App\Events\AuctionEnded;
use App\Events\RoundStarted;
use App\Bureaucrats\Watchdog;
use App\Livewire\AuctionView;
use App\Bureaucrats\TaxTurkey;
use App\Events\OfferSubmitted;
use Thunk\Verbs\Facades\Verbs;
use App\Events\PlayerReadiedUp;
use App\Bureaucrats\FocusedFoal;
use App\Bureaucrats\BailoutBunny;
use Thunk\Verbs\Models\VerbEvent;
use App\Bureaucrats\FrugalFruitFly;
use App\Bureaucrats\TreasuryChicken;
use App\RoundTemplates\PickYourPerks;
use App\Livewire\AwaitingNextRoundView;
use App\Bureaucrats\FeeCollectingFerret;
use App\RoundTemplates\LameDuckSession;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();

    $this->bootGame();
});

it('can play out multiple normal rounds', function () {
    dump($this->game->rounds->pluck('id'));

    //////////// Round 1 ////////////

    $round_1 = $this->game->rounds->first();
    $bureaucrats = $round_1->state()->bureaucrats;

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [
            FocusedFoal::class,
            BailoutBunny::class,
            FrugalFruitFly::class,
            FeeCollectingFerret::class,
        ],
        round_template: PickYourPerks::class,
    );

    $this->john->submitOffer($round_1, FocusedFoal::class, 4);
    $this->daniel->submitOffer($round_1, BailoutBunny::class, 1);
    $this->jacob->submitOffer($round_1, FeeCollectingFerret::class, 5);

    AuctionEnded::fire(round_id: $round_1->id);

    $this->assertEquals(3, $round_1->state()->offers()
        ->filter(fn($o) => $o->awarded)->count());

    //////////// Round 2 ////////////

    $round_2 = $this->game->rounds->skip(1)->first();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->rounds->skip(1)->first()->id,
        bureaucrats: [
            TiedHog::class,
            TreasuryChicken::class,
        ],
        round_template: LameDuckSession::class,
    );

    $this->john->submitOffer($round_2, TreasuryChicken::class, 4);
    $this->jacob->submitOffer($round_2, TiedHog::class, 5);

    AuctionEnded::fire(round_id: $round_2->id);

    $this->assertEquals(2, $round_2->state()->offers()
        ->filter(fn($o) => $o->awarded)->count());

    //////////// Round 3 ////////////

    $round_3 = $this->game->rounds->skip(2)->first();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->rounds->skip(1)->first()->id,
        bureaucrats: [
            TiedHog::class,
            TreasuryChicken::class,
        ],
        round_template: LameDuckSession::class,
    );

    $this->john->submitOffer($round_3, TreasuryChicken::class, 4);
    $this->jacob->submitOffer($round_3, TiedHog::class, 5);

    AuctionEnded::fire(round_id: $round_3->id);

    $this->assertEquals(2, $round_3->state()->offers()
        ->filter(fn($o) => $o->awarded)->count());

    dd($this->john->state()->money_history);

    
});