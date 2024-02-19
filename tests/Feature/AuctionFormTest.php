<?php

use App\Models\Round;
use App\DTOs\OfferDTO;
use Livewire\Livewire;
use App\Events\RoundStarted;
use App\Livewire\AuctionView;
use Thunk\Verbs\Facades\Verbs;
use App\Bureaucrats\FocusedFoal;
use App\Bureaucrats\BailoutBunny;
use Thunk\Verbs\Models\VerbEvent;
use App\Bureaucrats\FrugalFruitFly;
use App\RoundTemplates\PickYourPerks;
use App\Bureaucrats\FeeCollectingFerret;
use App\Events\PlayerReadiedUp;
use App\Livewire\AwaitingNextRoundView;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();

    $this->bootGame();
});

it('can play out multiple normal rounds', function () {
    dump($this->game->rounds->pluck('id'));
    $this->actingAs($this->john->user);

    $round_1 = $this->game->rounds->first();
    $bureaucrats = $round_1->state()->bureaucrats;

    Livewire::test(AuctionView::class, ['game' => $this->game, 'round' => $round_1])
        ->assertStatus(200)
        ->set('offers', [
            $bureaucrats[0]::SLUG => new OfferDTO(
                player_id: $this->john->id,
                round_id: $round_1->id,
                bureaucrat: $bureaucrats[0],
            ),
            $bureaucrats[1]::SLUG => new OfferDTO(
                player_id: $this->john->id,
                round_id: $round_1->id,
                bureaucrat: $bureaucrats[1],
            ),
        ])
        ->call('increment', $bureaucrats[0]::SLUG)
        ->call('increment', $bureaucrats[1]::SLUG)
        ->call('submit');

    $this->actingAs($this->daniel->user);

    Livewire::test(AuctionView::class, ['game' => $this->game, 'round' => $round_1])
        ->set('offers', [
            $bureaucrats[0]::SLUG => new OfferDTO(
                player_id: $this->daniel->id,
                round_id: $round_1->id,
                bureaucrat: $bureaucrats[0],
            ),
            $bureaucrats[1]::SLUG => new OfferDTO(
                player_id: $this->daniel->id,
                round_id: $round_1->id,
                bureaucrat: $bureaucrats[1],
            ),
        ])
        ->call('increment', $bureaucrats[0]::SLUG)
        ->call('increment', $bureaucrats[1]::SLUG)
        ->call('submit');

    $this->actingAs($this->jacob->user);

    Livewire::test(AuctionView::class, ['game' => $this->game, 'round' => $round_1])
        ->call('submit');

    Livewire::test(AwaitingNextRoundView::class, ['game' => $this->game, 'round' => $round_1])
        ->call('readyUp');

    $this->actingAs($this->john->user);
    Livewire::test(AwaitingNextRoundView::class, ['game' => $this->game, 'round' => $round_1])
        ->call('readyUp');

    $this->actingAs($this->daniel->user);
    Livewire::test(AwaitingNextRoundView::class, ['game' => $this->game, 'round' => $round_1])
        ->call('readyUp');

    $this->actingAs($this->john->user);

    $round_2 = $this->game->rounds->skip(1)->first();
    $bureaucrats = $round_2->state()->bureaucrats;

    dump('round 1 is over');

    Livewire::test(AuctionView::class, ['game' => $this->game, 'round' => $round_2])
        ->assertStatus(200)
        ->set('offers', [
            $bureaucrats[0]::SLUG => new OfferDTO(
                player_id: $this->john->id,
                round_id: $round_2->id,
                bureaucrat: $bureaucrats[0],
            ),
            $bureaucrats[1]::SLUG => new OfferDTO(
                player_id: $this->john->id,
                round_id: $round_2->id,
                bureaucrat: $bureaucrats[1],
            ),
        ])
        ->call('increment', $bureaucrats[0]::SLUG)
        ->call('increment', $bureaucrats[1]::SLUG)
        ->call('submit');

    $this->actingAs($this->daniel->user);

    Livewire::test(AuctionView::class, ['game' => $this->game, 'round' => $round_2])
        ->set('offers', [
            $bureaucrats[0]::SLUG => new OfferDTO(
                player_id: $this->daniel->id,
                round_id: $round_2->id,
                bureaucrat: $bureaucrats[0],
            ),
            $bureaucrats[1]::SLUG => new OfferDTO(
                player_id: $this->daniel->id,
                round_id: $round_2->id,
                bureaucrat: $bureaucrats[1],
            ),
        ])
        ->call('increment', $bureaucrats[0]::SLUG)
        ->call('increment', $bureaucrats[1]::SLUG)
        ->call('submit');

    $this->actingAs($this->jacob->user);

    Livewire::test(AuctionView::class, ['game' => $this->game, 'round' => $round_2])
        ->call('submit');

    Livewire::test(AwaitingNextRoundView::class, ['game' => $this->game, 'round' => $round_2])
        ->call('readyUp');

    $this->actingAs($this->john->user);
    Livewire::test(AwaitingNextRoundView::class, ['game' => $this->game, 'round' => $round_2])
        ->call('readyUp');

    $this->actingAs($this->daniel->user);
    Livewire::test(AwaitingNextRoundView::class, ['game' => $this->game, 'round' => $round_2])
        ->call('readyUp');

    dd($round_2->state());

    dd(VerbEvent::all()->pluck('type'));
});