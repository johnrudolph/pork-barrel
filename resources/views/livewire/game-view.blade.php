<div wire:poll class="bg-pale">
    @if($game->state()->status === 'awaiting-players')
        <livewire:pre-game-lobby :game="$game" :key="'pre-game'"/>
    @elseif($game->state()->status === 'in-progress')
        <livewire:headlines :game="$game" :key="'headline'"/>
        <livewire:auction-view :game="$game" :key="'auction'"/>
        {{--<livewire:money-log :game="$game" :key="'log'"/>--}}
    @endif
</div>
