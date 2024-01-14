<div wire:poll class="bg-pale">
    @if($game->state()->status === 'awaiting-players')
        <livewire:pre-game-lobby :game="$game" wire:key="pre-gam"/>
    @else
    <!-- header -->
    <div class="my-4 overflow-hidden">
        <p class="pl-8">Round {{ $this->round->round_number }} of 8</p>
    </div>

    <!-- round modifier -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" wire:key="round-modifier">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-8 sm:px-6 sm:py-8 lg:px-16 bg-teal text-white">
                <div>
                    <p>{{ $this->round_modifier::HEADLINE }}</p>
                    <p class="mt-2 italic text-xs">{{ $this->round_modifier::FLAVOR_TEXT }}</p>
                    <p class="mt-2 text-sm">{{ $this->round_modifier::EFFECT }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- body -->
    @if($this->player->state()->status === 'auction')
        <livewire:auction-view :game="$game" wire:key="auction-{{ $this->game->currentRound() }}"/>
    @else($game->state()->status === 'in-progress')
        <livewire:awaiting-next-round-view :game="$game" wire:key="round-{{ $this->game->currentRound() }}"/>
    @endif

    <!-- scoreboard -->
    <div class="py-4 text-purple max-w-7xl mx-auto sm:px-6 lg:px-8" wire:key="scoreboard">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
            <div class="bg-white px-6 py-6 sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Financial Report</h1>
                        </div>
                    </div>
                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Industry</th>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Money</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($this->scores as $s)
                                        <tr>
                                            @if ($s['player_id'] === $this->player()->id)
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-gray-900 sm:pl-0">{{ $s['industry'] }} (you)</td>
                                            @else
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ $s['industry'] }}</td>
                                            @endif
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $s['money'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- headlines -->
    @if($this->headlines->count() > 0)
    <div class="max-w-7xl mx-auto mt-8 sm:px-6 lg:px-8" wire:key="headlines">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-8 sm:px-6 sm:py-8 lg:px-16 bg-gray-200 text-purple">
                <div class="mt-2">
                    <p>Latest Headlines</p>
                    @foreach($this->headlines as $h)
                        <p class="mt-4 font-bold">{{ $h->headline }}</p>
                        <p class="mt-2 text-sm">{{ $h->description }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- money log -->
    <div class="py-4 text-purple max-w-7xl mx-auto sm:px-6 lg:px-8" wire:key="money-log">
        <div class="overflow-hidden sm:rounded-lg">
            <div class="px-6 py-6 sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flow-root">
                        <p class="text-sm font-semibold text-gray-900 mb-4">Your Money History</p>
                        @foreach(collect(range($this->game->state()->current_round_number, 1)) as $round_number)
                            <p class="mb-8"> Round {{ $round_number }} </p>
                            <ul role="list" class="">
                                @foreach($this->money_log_entries->filter(fn ($e) => $e->round_number === $round_number) as $entry)
                                <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        @if($entry->amount > 0)
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-teal flex items-center justify-center ring-8 ring-white">
                                                💰
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between pl-4 space-x-4 pt-1.5 text-teal">
                                            <div>
                                            <p class="text-sm">{{ $entry->description }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm">
                                            <p>{{ $entry->amount }}</p>
                                            </div>
                                        </div>
                                        @else
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-red flex items-center justify-center ring-8 ring-white">
                                                💸
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between pl-4 space-x-4 pt-1.5 text-red">
                                            <div>
                                                <p class="text-sm">{{ $entry->description }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm">
                                                <p>{{ $entry->amount }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div> 
    @endif
</div>