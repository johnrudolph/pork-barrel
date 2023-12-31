<?php

namespace App\Providers;

use App\Livewire\Synths\DTOSynth;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Livewire::propertySynthesizer([
            DTOSynth::class,
        ]);
    }
}
