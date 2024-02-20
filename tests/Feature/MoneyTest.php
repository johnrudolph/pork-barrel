<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\Bureaucrat;
use App\Bureaucrats\GamblinGoat;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundTemplates\RoundTemplate;
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
        user_id: $this->user_2->id,
        player_id: Snowflake::make()->id(),
        name: $this->user_2->name,
    );

    $this->game = Game::find($event->game_id);

    GameStarted::fire(game_id: $this->game->id);

    $this->round_started = RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, GamblinGoat::class],
        round_template: RoundTemplate::class,
    );

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('gives players 5 money to start each round', function () {
    $this->assertEquals(5, $this->john->state()->availableMoney());
    app(StateManager::class)->reset();
    $this->assertEquals(5, $this->john->state()->availableMoney());

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 2,
        round_id: $this->game->state()->round_ids[1],
        bureaucrats: [Bureaucrat::class],
        round_template: RoundTemplate::class,
    );

    $this->assertEquals(10, $this->john->state()->availableMoney());

    $this->assertEquals(1, $this->john->state()->money_history->first()->round_number);
});

it('creates money log entries when players win auctions', function () {
    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), GamblinGoat::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $johns_spending = $this->john->state()->money_history
        ->where('amount', '<', 1);

    $this->assertCount(1, $johns_spending);

    $this->assertEquals(
        'You had the highest bid for the Bailout Bunny. Every time you reach 0 money, you will receive 10 money.',
        $johns_spending->first()->description,
    );
});
