<div wire:poll>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Game code: ') . $game->code}} 
        </h2>
    </x-slot>
    @if($game->state()->status === 'awaiting-players')
        <livewire:pre-game-lobby :game="$game" />
    @elseif($game->state()->status === 'in-progress')
        <livewire:headlines :game="$game" />
        @if($game->currentRound()->state()->phase === 'auction')
            <livewire:auction-view :game="$game" />
        @endif
        <livewire:money-log :game="$game" />
    @endif
</div>
