<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\GamblinGoat;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\MoneyLogEntry;
use App\Models\Player;
use App\Models\User;
use App\RoundModifiers\RoundModifier;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;
use Thunk\Verbs\Lifecycle\StateManager;

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
        user_id: $this->user_1->id,
        player_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $this->user_2->id,
        player_id: Snowflake::make()->id(),
    );

    $this->game = Game::find($event->game_id);

    GameStarted::fire(game_id: $this->game->id);

    $this->round_started = RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, GamblinGoat::class],
        round_modifier: RoundModifier::class,
    );

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('gives players 10 money to start each round', function () {
    $this->assertEquals(10, $this->john->state()->money);
    app(StateManager::class)->reset();
    $this->assertEquals(10, $this->john->state()->money);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    $this->game->currentRound()->next()->start();

    $this->assertEquals(20, $this->john->state()->money);
});

it('creates money log entries when players win auctions', function () {
    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $johns_spending = MoneyLogEntry::where('player_id', $this->john->id)
        ->where('amount', '<', 1)
        ->get();

    $this->assertCount(1, $johns_spending);

    $this->assertEquals(
        'You had the highest bid for the Bailout Bunny. The next time you reach 0 money, you will receive 10 money.',
        $johns_spending->first()->description,
    );

    $daniel_spending = MoneyLogEntry::where('player_id', $this->daniel->id)
        ->where('amount', '<', 1)
        ->get();

    $this->assertCount(1, $daniel_spending);

    $this->assertEquals(
        "You had the highest bid for the Gamblin' Goat. Let's see how it pays off...",
        $daniel_spending->first()->description,
    );
});
