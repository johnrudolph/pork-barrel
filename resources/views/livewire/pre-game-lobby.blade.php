<div wire:poll>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg bg-salmon border border-color-purple text-purple">
            <div class="bg-white">
                <div class="px-6 py-24 sm:px-6 sm:py-32 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Waiting for players to join
                    </h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-8">
                        Who's in: {{ $this->game->players->map(fn($p) => $p->user->name)->join(', ') }}
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        @if ($this->game->players->count() > 1)
                        <button 
                            type="button" 
                            wire:click="startGame" 
                            class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Everyone's here, let's start
                        </button>
                        @endif
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
