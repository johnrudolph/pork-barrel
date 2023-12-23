<div class="bg-pale">
    @if($game->state()->status === 'awaiting-players')
        <livewire:pre-game-lobby @game-started="$refresh" :game="$game" :key="'pre-game'"/>
    @elseif($this->player()->state()->status === 'auction')
        <livewire:auction-view  @submitted="$refresh" :game="$game" :key="'auction'"/>
    @else($game->state()->status === 'in-progress')
        <livewire:awaiting-next-round-view @round-ended="$refresh" @readied-up="$refresh" :game="$game"/>
    @endif
</div>
