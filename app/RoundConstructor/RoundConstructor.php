<?php

namespace App\RoundConstructor;

use App\Bureaucrats\Bureaucrat;
use App\RoundTemplates\RoundTemplate;
use App\States\RoundState;

class RoundConstructor
{
    public function __construct(
        public RoundState $round,
        public ?int $number_of_bureaucrats = null,
        public ?string $round_template = null,
        public ?array $bureaucrats = null,
    ) {
        $this->round_template ??= $this->selectModifier();
        $this->number_of_bureaucrats ??= $this->round_template::NUMBER_OF_BUREAUCRATS;
        $this->bureaucrats ??= $this->selectBureaucrats();
    }

    public function selectModifier()
    {
        $modifiers_used_this_game = $this->round->game()->rounds()
            ->map(fn ($r) => $r->round_template)
            ->flatten();

        return RoundTemplate::all()
            ->shuffle()
            ->sortByDesc(function ($modifier) use ($modifiers_used_this_game) {
                $adjustment_for_times_used = $modifiers_used_this_game
                    ->filter(fn ($m) => $m === $modifier)
                    ->count() * 0.1;

                return $modifier::suitability($this) - $adjustment_for_times_used;
            })
            ->first();
    }

    public function selectBureaucrats()
    {
        $bureaucrats_used_this_game = $this->round->game()->rounds()
            ->map(fn ($r) => $r->bureaucrats)
            ->flatten();

        $bureaucrats = Bureaucrat::all()
            ->shuffle()
            ->sortByDesc(fn ($bureaucrat) => $bureaucrat::suitability($this) - $bureaucrats_used_this_game
                ->filter(fn ($b) => $b === $bureaucrat)
                ->count() * 0.1
            )
            ->take($this->number_of_bureaucrats)
            ->toArray();

        return $bureaucrats;
    }

    // HELPERS //

    public function numberOfPlayers(): int
    {
        return $this->round->game()->players->count();
    }

    public function stageOfGame(): string
    {
        if ($this->round->round_number === 1) {
            return 'first-round';
        }

        if ($this->round->round_number < 3) {
            return 'early';
        }

        if ($this->round->round_number < 7) {
            return 'mid';
        }

        if ($this->round->round_number < 8) {
            return 'late';
        }

        return 'final-round';
    }
}
