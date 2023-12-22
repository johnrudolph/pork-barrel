<div wire:poll class="bg-pale">
    @if($game->state()->status === 'awaiting-players')
        <livewire:pre-game-lobby :game="$game" :key="'pre-game'"/>
    @elseif($this->player()->state()->status === 'auction')
        <livewire:auction-view :game="$game" :key="'auction'"/>
    @else($game->state()->status === 'in-progress')
        <livewire:awaiting-next-round-view :game="$game"/>
    @endif
</div>
