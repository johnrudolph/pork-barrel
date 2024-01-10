<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\GamblinGoat;
use App\Bureaucrats\MajorityLeaderMare;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundConstructor\RoundConstructor;
use App\RoundModifiers\AlwaysABridesmaid;
use App\RoundModifiers\Astroturfing;
use App\RoundModifiers\CampaignFinanceReform;
use App\RoundModifiers\CampaignSeason;
use App\RoundModifiers\Hegemony;
use App\RoundModifiers\LameDuckSession;
use App\RoundModifiers\LegislativeFrenzy;
use App\RoundModifiers\TaxTheRich;
use Glhd\Bits\Snowflake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Thunk\Verbs\Facades\Verbs;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Verbs::commitImmediately();
    $this->user_1 = User::factory()->create();
    $this->user_2 = User::factory()->create();
    $this->user_3 = User::factory()->create();

    $event = GameCreated::fire(
        user_id: $this->user_1->id,
        game_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $this->user_2->id,
        player_id: Snowflake::make()->id(),
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $this->user_3->id,
        player_id: Snowflake::make()->id(),
    );

    $this->game = Game::find($event->game_id);

    $this->john = Player::firstWhere('user_id', $this->user_1->id);
    $this->daniel = Player::firstWhere('user_id', $this->user_2->id);
    $this->jacob = Player::firstWhere('user_id', $this->user_3->id);
});

it('takes 5 money from the richeset player at the end of the round', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [GamblinGoat::class],
        round_modifier: TaxTheRich::class,
    );

    $this->john->submitOffer($this->game->currentRound(), GamblinGoat::class, 10);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(5, $this->daniel->state()->money);
});

it('changes the number of bureaucrats chosen for Lame Duck and Legislative Frenzy', function () {
    $constructor = new RoundConstructor(
        round: $this->game->rounds->first()->state(),
        round_modifier: LameDuckSession::class,
    );

    $this->assertEquals(2, collect($constructor->bureaucrats)->count());

    $constructor = new RoundConstructor(
        round: $this->game->rounds->first()->state(),
        round_modifier: LegislativeFrenzy::class,
    );

    $this->assertEquals(5, collect($constructor->bureaucrats)->count());
});

it('rewards you for only making one offer in Campaign Season', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, MajorityLeaderMare::class],
        round_modifier: CampaignSeason::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(14, $this->john->state()->money);
    $this->assertEquals(8, $this->daniel->state()->money);
});

it('rewards you for making offers that are not rewarded with Always A Bridesmaid', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, MajorityLeaderMare::class],
        round_modifier: AlwaysABridesmaid::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);
    $this->jacob->submitOffer($this->game->currentRound(), BailoutBunny::class, 2);
    $this->jacob->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 2);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(12, $this->john->state()->money);
    $this->assertEquals(14, $this->daniel->state()->money);
    $this->assertEquals(6, $this->jacob->state()->money);
});

it('grants rewards even if you do not have the highest offer with Campaign Finance Reform', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        round_modifier: CampaignFinanceReform::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 8);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 6);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(2, $this->john->state()->money);
    $this->assertEquals(4, $this->daniel->state()->money);

    $this->assertTrue($this->john->state()->has_bailout);
    $this->assertTrue($this->daniel->state()->has_bailout);
});

it('refunds the largest offer for Hegemony', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        round_modifier: Hegemony::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 8);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 6);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(6, $this->john->state()->money);
    $this->assertEquals(10, $this->daniel->state()->money);
});

it('refunds offers under 4 for Astroturfing', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, MajorityLeaderMare::class],
        round_modifier: Astroturfing::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 3);
    $this->john->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 2);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 3);
    $this->daniel->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 7);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(12, $this->john->state()->money);
    $this->assertEquals(3, $this->daniel->state()->money);
});
