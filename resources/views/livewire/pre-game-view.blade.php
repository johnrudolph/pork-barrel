<div wire:poll class="bg-pale">
    @if($game_status === 'awaiting-players')
        <livewire:pre-game-lobby :game="$game" wire:key="pre-gam"/>
    @else
    <!-- header -->
    @if($game_status === 'in-progress')
    <div class="my-4 overflow-hidden">
        <p class="pl-8">Round {{ $this->round->round_number }} of 8</p>
    </div>
    @elseif($game_status === 'complete')
    <div class="my-4 overflow-hidden">
        <p class="pl-8">Game over! Thanks for playing</p>
    </div>
    @endif

    @if($game_status === 'in-progress')
    <!-- round template -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" wire:key="round-template">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-8 sm:px-6 sm:py-8 lg:px-16 bg-teal text-white">
                <div>
                    <p>{{ $this->round_template::HEADLINE }}</p>
                    <p class="mt-2 italic text-xs">{{ $this->round_template::FLAVOR_TEXT }}</p>
                    <p class="mt-2 text-sm">{{ $this->round_template::EFFECT }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- body -->
    @if($game_status === 'complete')
    @elseif($player_status === 'auction')
        <livewire:auction-view :game="$game" wire:key="auction-{{ $this->game->currentRound() }}"/>
    @else($player_status === 'waiting')
        <livewire:awaiting-next-round-view :game="$game" wire:key="round-{{ $this->game->currentRound() }}"/>
    @endif

    
    @endif
</div>