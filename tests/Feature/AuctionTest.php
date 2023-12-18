<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\GamblinGoat;
use App\Bureaucrats\MajorityLeaderMare;
use App\Bureaucrats\MinorityLeaderMink;
use App\Bureaucrats\TaxTurkey;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundModifiers\RoundModifier;
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

    GameStarted::fire(game_id: $this->game->id);

    Verbs::commit();

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [
            GamblinGoat::class,
            BailoutBunny::class,
            MinorityLeaderMink::class,
            MajorityLeaderMare::class,
            TaxTurkey::class],
        round_modifier: RoundModifier::class,
    );

    $this->game->players
        ->each(fn ($p) => $p->receiveMoney(10, 'Received starting money.'));

    Verbs::commit();

    $this->john = Player::first();
    $this->daniel = Player::get()->last();
});

it('provides 5 options to bid on in a round', function () {
    $this->assertEquals(
        5,
        collect($this->game->currentRound()->state()->bureaucrats)
            ->unique()
            ->count()
    );
});

it('records offers made to the state', function () {
    $round = $this->game->currentRound();

    $this->john->submitOffer($round, $round->state()->bureaucrats[0], 1);

    $this->assertEquals(
        1,
        collect($round->state()->offers)
            ->filter(fn ($o) => $o['player_id'] === $this->john->id
                && $o['bureaucrat'] === $round->state()->bureaucrats[0]
                && $o['original_amount'] === 1
            )
            ->count()
    );
});

it('records which player won each action', function () {
    $round = $this->game->currentRound();

    $this->john->submitOffer($round, GamblinGoat::class, 1);
    $this->john->submitOffer($round, MinorityLeaderMink::class, 2);

    $this->daniel->submitOffer($round, BailoutBunny::class, 1);
    $this->daniel->submitOffer($round, MinorityLeaderMink::class, 2);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);
    Verbs::commit();

    $johns_actions = $round->state()->actions_awarded
        ->filter(fn ($a) => $a['player_id'] === $this->john->id)
        ->pluck('bureaucrat');

    $daniels_actions = $round->state()->actions_awarded
        ->filter(fn ($a) => $a['player_id'] === $this->daniel->id)
        ->pluck('bureaucrat');

    $this->assertEquals(true, $johns_actions->contains(GamblinGoat::class));
    $this->assertEquals(false, $daniels_actions->contains(GamblinGoat::class));
    $this->assertEquals(false, $johns_actions->contains(BailoutBunny::class));
    $this->assertEquals(true, $daniels_actions->contains(BailoutBunny::class));
    $this->assertEquals(true, $johns_actions->contains(MinorityLeaderMink::class));
    $this->assertEquals(true, $daniels_actions->contains(MinorityLeaderMink::class));
    $this->assertEquals(false, $johns_actions->contains(MajorityLeaderMare::class));
    $this->assertEquals(false, $daniels_actions->contains(MajorityLeaderMare::class));
});

it('spends the money offerred by winners', function () {
    $round = $this->game->currentRound();
    $bureaucrats = $round->state()->bureaucrats;

    $this->john->submitOffer($round, $bureaucrats[0], 1);
    $this->john->submitOffer($round, $bureaucrats[1], 0);
    $this->john->submitOffer($round, $bureaucrats[2], 2);
    $this->john->submitOffer($round, $bureaucrats[3], 0);

    $this->daniel->submitOffer($round, $bureaucrats[0], 0);
    $this->daniel->submitOffer($round, $bureaucrats[1], 1);
    $this->daniel->submitOffer($round, $bureaucrats[2], 2);
    $this->daniel->submitOffer($round, $bureaucrats[3], 0);

    AuctionEnded::fire(round_id: $round->id);
    Verbs::commit();

    // John spends 3, because he didn't win the second bureaucrat
    $this->assertEquals(17, $this->john->state()->money);

    // Daniel spends 3, because he didn't win the first bureaucrat
    $this->assertEquals(17, $this->daniel->state()->money);
});
