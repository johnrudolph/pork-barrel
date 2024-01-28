<?php

namespace App\RoundConstructor;

use App\RoundTemplates\RoundTemplate;
use App\States\RoundState;
use Illuminate\Support\Collection;

class RoundConstructor
{
    public function __construct(
        public RoundState $round,
        public ?string $round_template = null,
        public ?array $bureaucrats = null,
    ) {
        $this->round_template ??= $this->selectTemplate();
        $this->bureaucrats ??= $this->round_template::selectBureaucrats($this);
    }

    public function selectTemplate()
    {
        $templates_used_this_game = $this->round->game()->rounds()
            ->map(fn ($r) => $r->round_template)
            ->flatten();

        return RoundTemplate::all()
            ->shuffle()
            ->sortByDesc(function ($template) use ($templates_used_this_game) {
                $adjustment_for_times_used = $templates_used_this_game
                    ->filter(fn ($m) => $m === $template)
                    ->count() * 0.1;

                return $template::suitability($this) - $adjustment_for_times_used;
            })
            ->first();
    }

    public function selectBureaucratsFromSubset(Collection $bureaucrat_subset, int $number_to_select)
    {
        $bureaucrats_used_this_game = $this->round->game()->rounds()
            ->map(fn ($r) => $r->bureaucrats)
            ->flatten();

        return $bureaucrat_subset
            ->shuffle()
            ->sortByDesc(fn ($bureaucrat) => $bureaucrat::suitability($this) - $bureaucrats_used_this_game
                ->filter(fn ($b) => $b === $bureaucrat)
                ->count() * 0.1
            )
            ->take($number_to_select);
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
