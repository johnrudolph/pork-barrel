<?php

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\MajorityLeaderMare;
use App\Bureaucrats\MinorityLeaderMink;
use App\Events\AuctionEnded;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerJoinedGame;
use App\Events\RoundStarted;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\RoundConstructor\RoundConstructor;
use App\RoundTemplates\AlwaysABridesmaid;
use App\RoundTemplates\Astroturfing;
use App\RoundTemplates\CampaignFinanceReform;
use App\RoundTemplates\CampaignSeason;
use App\RoundTemplates\Hegemony;
use App\RoundTemplates\LameDuckSession;
use App\RoundTemplates\LegislativeFrenzy;
use App\RoundTemplates\StimulusPackage;
use App\RoundTemplates\TaxTheRich;
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
        name: $this->user_2->name,
    );

    PlayerJoinedGame::fire(
        game_id: $event->game_id,
        user_id: $this->user_3->id,
        player_id: Snowflake::make()->id(),
        name: $this->user_3->name,
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
        bureaucrats: [BailoutBunny::class],
        round_template: TaxTheRich::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(0, $this->daniel->state()->availableMoney());
});

it('changes the number of bureaucrats chosen for Lame Duck and Legislative Frenzy', function () {
    $constructor = new RoundConstructor(
        round: $this->game->rounds->first()->state(),
        round_template: LameDuckSession::class,
    );

    $this->assertEquals(2, collect($constructor->bureaucrats)->count());

    $constructor = new RoundConstructor(
        round: $this->game->rounds->first()->state(),
        round_template: LegislativeFrenzy::class,
    );

    $this->assertEquals(6, collect($constructor->bureaucrats)->count());
});

it('rewards you for only making one offer in Campaign Season', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, MajorityLeaderMare::class],
        round_template: CampaignSeason::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(9, $this->john->state()->availableMoney());
    $this->assertEquals(3, $this->daniel->state()->availableMoney());
});

it('rewards you for making offers that are not rewarded with Always A Bridesmaid', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, MajorityLeaderMare::class],
        round_template: AlwaysABridesmaid::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 1);
    $this->jacob->submitOffer($this->game->currentRound(), BailoutBunny::class, 2);
    $this->jacob->submitOffer($this->game->currentRound(), MajorityLeaderMare::class, 2);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(7, $this->john->state()->availableMoney());
    $this->assertEquals(9, $this->daniel->state()->availableMoney());
    $this->assertEquals(1, $this->jacob->state()->availableMoney());
});

it('grants rewards even if you do not have the highest offer with Campaign Finance Reform', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        round_template: CampaignFinanceReform::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 5);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 4);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(1, $this->daniel->state()->availableMoney());
    $this->assertTrue($this->daniel->state()->has_bailout);
});

it('refunds half of the largest offer for Hegemony', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class],
        round_template: Hegemony::class,
    );

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 4);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(3, $this->john->state()->availableMoney());
    $this->assertEquals(5, $this->daniel->state()->availableMoney());
});

it('refunds offers under 4 for Astroturfing', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [MinorityLeaderMink::class],
        round_template: Astroturfing::class,
    );

    $this->john->submitOffer($this->game->currentRound(), MinorityLeaderMink::class, 3);
    $this->daniel->submitOffer($this->game->currentRound(), MinorityLeaderMink::class, 4);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    $this->assertEquals(8, $this->john->state()->availableMoney());
    $this->assertEquals(1, $this->daniel->state()->availableMoney());
});

it('offers stimulus to players and takes it away if they fail to use it', function () {
    GameStarted::fire(game_id: $this->game->id);

    RoundStarted::fire(
        game_id: $this->game->id,
        round_number: 1,
        round_id: $this->game->rounds->first()->id,
        bureaucrats: [BailoutBunny::class, MinorityLeaderMink::class],
        round_template: StimulusPackage::class,
    );

    $this->assertEquals(20, $this->john->state()->availableMoney());
    $this->assertEquals(20, $this->jacob->state()->availableMoney());
    $this->assertEquals(20, $this->daniel->state()->availableMoney());

    $this->john->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), BailoutBunny::class, 1);
    $this->daniel->submitOffer($this->game->currentRound(), MinorityLeaderMink::class, 14);

    AuctionEnded::fire(round_id: $this->game->currentRound()->id);

    // John spends 1, and then loses the other 14
    $this->assertEquals(5, $this->john->state()->availableMoney());

    // Jacob spends nothing, then loses all 15
    $this->assertEquals(5, $this->jacob->state()->availableMoney());

    // Daniel spends all 15
    $this->assertEquals(5, $this->daniel->state()->availableMoney());
});
