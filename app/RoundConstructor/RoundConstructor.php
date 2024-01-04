<?php

namespace App\RoundConstructor;

use App\Bureaucrats\Bureaucrat;
use App\RoundModifiers\RoundModifier;
use App\States\RoundState;

class RoundConstructor
{
    public function __construct(
        public RoundState $round,
        public ?int $number_of_bureaucrats = null,
        public ?string $round_modifier = null,
        public ?array $bureaucrats = null,
    ) {
        $this->round_modifier ??= $this->selectModifier();
        $this->number_of_bureaucrats ??= $this->round_modifier::NUMBER_OF_BUREAUCRATS;
        $this->bureaucrats ??= $this->selectBureaucrats();
    }

    public function selectModifier()
    {
        $modifiers_used_this_game = $this->round->game()->rounds()
            ->map(fn ($r) => $r->round_modifier)
            ->flatten();

        return RoundModifier::all()
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

    public function isFinalRound(): bool
    {
        return $this->round->round_number === 8;
    }
}
