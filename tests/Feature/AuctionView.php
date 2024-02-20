<?php

use App\Livewire\AuctionView;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();

    $this->bootGame();
});

it('correctly renders the game view', function () {
    $this->actingAs($this->john->user);

    Livewire::test(AuctionView::class, ['game' => $this->game, 'round' => $this->game->rounds->first()])
        ->assertSet('game_status', 'in-progress')
        ->assertSet('round_status', 'auction')
        ->assertSet('player_status', 'auction')
        ->assertStatus(200);
});
