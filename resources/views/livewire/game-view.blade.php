<div wire:poll>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Game code: ') . $game->code}} 
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-8 sm:px-6 sm:py-32 lg:px-8">

                    @if($game->state()->status === 'awaiting-players')
                        <livewire:pre-game-lobby :game="$game" />
                    @elseif($game->state()->status === 'in-progress')
                        @if($game->currentRound()->state()->phase === 'auction')
                            <livewire:auction-view :game="$game" />
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
