<?php

namespace App\Providers;

use Livewire\Livewire;
use App\Livewire\Synths\DTOSynth;
use Illuminate\Support\ServiceProvider;

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
