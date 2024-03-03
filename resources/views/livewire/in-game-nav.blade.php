<div class="mt-4">
    <div x-data="{ open_money_log: false, open_scoreboard: false, open_headlines: false, open_perks: false }" class="mx-auto max-w-3xl w-full space-y-3">

        <div class="flex flex-row justify-center space-x-4">
            <span x-on:click="open_money_log = true">
                <button type="button" class="bg-white px-5 py-2.5 rounded-md">
                    Money History
                </button>
            </span>

            <span x-on:click="open_scoreboard = true">
                <button type="button" class="bg-white px-5 py-2.5 rounded-md">
                    Scoreboard
                </button>
            </span>

            @if($this->game->headlines()->count() > 0)
            <span x-on:click="open_headlines = true">
                <button type="button" class="bg-white px-5 py-2.5 rounded-md">
                    Headlines
                </button>
            </span>
            @endif

            @if($this->perks()->count() > 0)
            <span x-on:click="open_perks = true">
                <button type="button" class="bg-white px-5 py-2.5 rounded-md">
                    Perks
                </button>
            </span>
            @endif
        </div>

        <!-- Money log modal -->
        <div
            x-dialog
            x-model="open_money_log"
            style="display: none"
            class="fixed inset-0 overflow-y-auto z-10"
        >
            <!-- Overlay -->
            <div x-dialog:overlay x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>
    
            <!-- Panel -->
            <div
                class="relative min-h-screen flex items-center justify-center p-4"
            >
                <div
                    x-dialog:panel
                    x-transition
                    class="relative max-w-xl w-full bg-white rounded-xl overflow-y-auto"
                >
                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" @click="$dialog.close()" class="bg-gray-50 rounded-lg p-2 text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                            <span class="sr-only">Close modal</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
    
                    <!-- Body -->
                    <div class="p-8">
                        <!-- Title -->
                        <h2 x-dialog:title class="text-2xl font-bold mb-8">My Money History</h2>
    
                        <!-- Content -->
                        <div class="rounded-lg bg-white">
                            <div>
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
                    </div>
                </div>
            </div>
        </div>



        <!-- Scoreboard modal -->
        <div
            x-dialog
            x-model="open_scoreboard"
            style="display: none"
            class="fixed inset-0 overflow-y-auto z-10"
        >
            <!-- Overlay -->
            <div x-dialog:overlay x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>
    
            <!-- Panel -->
            <div
                class="relative min-h-screen flex items-center justify-center p-4"
            >
                <div
                    x-dialog:panel
                    x-transition
                    class="relative max-w-xl w-full bg-white rounded-xl overflow-y-auto"
                >
                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" @click="$dialog.close()" class="bg-gray-50 rounded-lg p-2 text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                            <span class="sr-only">Close modal</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
    
                    <!-- Body -->
                    <div class="p-8">
                        <!-- Title -->
                        <h2 x-dialog:title class="text-2xl font-bold mb-8">Scoreboard</h2>
    
                        <!-- Content -->
                        <div class="rounded-lg bg-white">                    
                            <div>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Headlines modal -->
        <div
            x-dialog
            x-model="open_headlines"
            style="display: none"
            class="fixed inset-0 overflow-y-auto z-10"
        >
            <!-- Overlay -->
            <div x-dialog:overlay x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>
    
            <!-- Panel -->
            <div
                class="relative min-h-screen flex items-center justify-center p-4"
            >
                <div
                    x-dialog:panel
                    x-transition
                    class="relative max-w-xl w-full bg-white rounded-xl overflow-y-auto"
                >
                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" @click="$dialog.close()" class="bg-gray-50 rounded-lg p-2 text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                            <span class="sr-only">Close modal</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
    
                    <!-- Body -->
                    <div class="p-8">
                        <!-- Title -->
                        <h2 x-dialog:title class="text-2xl font-bold mb-8">Scoreboard</h2>
    
                        <!-- Content -->
                        @if($this->game->headlines()->count() > 0)
                            <div class="rounded-lg bg-white">                        
                                <div>
                                    <div class="px-6 pb-4">
                                        <div class="overflow-hidden sm:rounded-lg">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Perks modal -->
        <div
            x-dialog
            x-model="open_perks"
            style="display: none"
            class="fixed inset-0 overflow-y-auto z-10"
        >
            <!-- Overlay -->
            <div x-dialog:overlay x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>
    
            <!-- Panel -->
            <div
                class="relative min-h-screen flex items-center justify-center p-4"
            >
                <div
                    x-dialog:panel
                    x-transition
                    class="relative max-w-xl w-full bg-white rounded-xl overflow-y-auto"
                >
                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" @click="$dialog.close()" class="bg-gray-50 rounded-lg p-2 text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                            <span class="sr-only">Close modal</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
    
                    <!-- Body -->
                    <div class="p-8">
                        <!-- Title -->
                        <h2 class="text-2xl font-bold mb-8">My Perks</h2>
    
                        <!-- Content -->
                        <div class="rounded-lg bg-white">
                            <div>
                                <div class="px-6 pb-4">
                                    <div class="overflow-hidden sm:rounded-lg">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>