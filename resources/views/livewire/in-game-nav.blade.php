<div class="mt-4">
    <div x-data class="mx-auto max-w-3xl w-full space-y-3">

        <!-- money log -->
        <div x-disclosure class="rounded-lg bg-white shadow">
            <button
                x-disclosure:button
                class="flex w-full items-center justify-between px-6 py-3 text-md font-bold"
            >
                <span>My money history</span>
    
                <span x-show="$disclosure.isOpen" x-cloak aria-hidden="true" class="ml-4">&minus;</span>
                <span x-show="! $disclosure.isOpen" aria-hidden="true" class="ml-4">&plus;</span>
            </button>
    
            <div x-disclosure:panel x-collapse>
                <div class="px-6 pb-4">
                    @foreach(collect(range($this->game->state()->current_round_number, 1)) as $round_number)
                        <div class="flex flex-row justify-between items-center mb-8">
                            <p>Round {{ $round_number }} </p>
                            <p class="text-sm text-gray-600">Running Balance</p>
                        </div>
                        <ul role="list">
                            @foreach($this->moneyHistory()->reverse()->filter(fn ($e) => $e->round_number === $round_number) as $entry)
                            <li>
                            <div class="relative pb-8">
                                <div class="relative flex space-x-3">
                                    @if($entry->amount >= 0)
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-teal text-sm text-white font-bold flex items-center justify-center ring-8 ring-white">
                                            +{{ $entry->amount }}
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5 text-teal">
                                        <div>
                                        <p class="text-sm">{{ $entry->description }}</p>
                                        </div>
                                        <div class="whitespace-nowrap text-right text-sm flex flex-row">
                                            <div>
                                                <p>{{ $entry->balance }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div>
                                        <span class="h-8 w-8 rounded-full text-sm text-white font-bold bg-red flex items-center justify-center ring-8 ring-white">
                                            {{ $entry->amount }}
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5 text-red">
                                        <div>
                                            <p class="text-sm">{{ $entry->description }}</p>
                                        </div>
                                        <div class="whitespace-nowrap text-right text-sm">
                                            <p>{{ $entry->balance }}</p>
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

        <!-- scoreboard -->
        <div x-disclosure class="rounded-lg bg-white shadow">
            <button
                x-disclosure:button
                class="flex w-full items-center justify-between px-6 py-3 text-md font-bold"
            >
                <span>Scoreboard</span>
    
                <span x-show="$disclosure.isOpen" x-cloak aria-hidden="true" class="ml-4">&minus;</span>
                <span x-show="! $disclosure.isOpen" aria-hidden="true" class="ml-4">&plus;</span>
            </button>
    
            <div x-disclosure:panel x-collapse>
                <div class="px-6 pb-4">
                    <div class="flow-root">
                        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Industry</th>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Money</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($this->scores() as $s)
                                            <tr>
                                                @if ($s['player_id'] === $this->player->id)
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-gray-900 sm:pl-0">{{ $s['industry'] }} (you)</td>
                                                @elseif($game->status === 'complete')
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                                    {{ $s['industry'] }} ({{ $s['player_name'] }})
                                                </td>
                                                @else
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                                    {{ $s['industry'] }}
                                                </td>
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
        @if($this->game->headlines()->count() > 0)
        <div x-disclosure class="rounded-lg bg-white shadow">
            <button
                x-disclosure:button
                class="flex w-full items-center justify-between px-6 py-3 text-md font-bold"
            >
                <span>Headlines</span>
    
                <span x-show="$disclosure.isOpen" x-cloak aria-hidden="true" class="ml-4">&minus;</span>
                <span x-show="! $disclosure.isOpen" aria-hidden="true" class="ml-4">&plus;</span>
            </button>
    
            <div x-disclosure:panel x-collapse>
                <div class="px-6 pb-4">
                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class=" text-purple">
                            <div>
                                @foreach($this->game->headlines() as $h)
                                    <p class="mt-4 font-bold">{{ $h->headline }}</p>
                                    <p class="mt-2 text-sm">{{ $h->description }}</p>
                                @endforeach
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- perks -->
        @if($this->perks()->count() > 0)
        <div x-disclosure class="rounded-lg bg-white shadow">
            <button
                x-disclosure:button
                class="flex w-full items-center justify-between px-6 py-3 text-md font-bold"
            >
                <span>My perks</span>
    
                <span x-show="$disclosure.isOpen" x-cloak aria-hidden="true" class="ml-4">&minus;</span>
                <span x-show="! $disclosure.isOpen" aria-hidden="true" class="ml-4">&plus;</span>
            </button>
    
            <div x-disclosure:panel x-collapse>
                <div class="px-6 pb-4">
                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class=" text-purple">
                            <div>
                                @foreach($this->perks() as $p)
                                    <p class="mt-4 font-bold">{{ $p::NAME }}</p>
                                    <p class="mt-2 text-sm">{{ $p::EFFECT }}</p>
                                @endforeach
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>