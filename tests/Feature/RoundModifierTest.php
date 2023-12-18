<?php

use App\Bureaucrats\GamblinGoat;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundModifiers\TaxTheRich;
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

it('takes 5 money from the richeset player at the end of the round', function () {
    GameStarted::fire(game_id: $this->game->id);

    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [GamblinGoat::class],
        round_modifier: TaxTheRich::class,
    );

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 10);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();
    $this->game->currentRound()->endRound();
    Verbs::commit();

    $this->assertEquals(5, $this->daniel->state()->money);
});
