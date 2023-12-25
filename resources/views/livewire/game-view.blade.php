<div wire:poll class="bg-pale">
    @if($game->state()->status === 'awaiting-players')
        <livewire:pre-game-lobby :game="$game" wire:key="pre-gam"/>
    @else
    <!-- header -->
    <div class="my-4 overflow-hidden">
        <p class="pl-8">Round {{ $this->round->round_number }} of 8</p>
    </div>

    <!-- headlines -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-8 sm:px-6 sm:py-8 lg:px-8 bg-teal text-white">
                <div>
                    <p class="text-sm font-semibold leading-6">Today's headlines</p>
                    <p class="mt-2">{{ $this->round_modifier::HEADLINE }}</p>
                    <p class="mt-2 italic text-xs">{{ $this->round_modifier::FLAVOR_TEXT }}</p>
                    <p class="mt-2 text-sm">{{ $this->round_modifier::EFFECT }}</p>
                </div>
                @if($this->other_headlines->count() > 0)
                <div x-data="{ open: false }">
                    <button x-on:click="open = ! open" class="text-sm text-purple mt-2">Show previous headlines</button>
                
                    <div x-show="open" class="mt-2">
                        @foreach($this->other_headlines as $h)
                            <div>
                                <p class="mt-2">{{ $h->headline }}</p>
                                <p class="mt-2 text-sm">{{ $h->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
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
    <div class="py-4 text-purple max-w-7xl mx-auto sm:px-6 lg:px-8">
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

    <!-- money log -->
    <div class="py-4 text-purple max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden sm:rounded-lg">
            <div class="px-6 py-6 sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flow-root">
                    <p class="text-sm font-semibold text-gray-900 mb-4">Your Money History</p>
                    <ul role="list" class="-mb-8">
                        @foreach($this->money_log_entries as $entry)
                        <li>
                        <div class="relative pb-8">
                            <span class="absolute left-4 top-4 h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <div class="relative flex space-x-3">
                            <div>
                                @if($entry->amount > 0)
                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                    💰
                                </span>
                                @else
                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                    💸
                                </span>
                                @endif
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div>
                                <p class="text-sm text-gray-500">{{ $entry->description }}</p>
                                </div>
                                <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                <p>{{ $entry->amount }}</p>
                                </div>
                            </div>
                            </div>
                        </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div> 

    @endif
</div>