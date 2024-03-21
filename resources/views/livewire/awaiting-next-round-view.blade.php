<div wire:poll>
    <div>
        <livewire:in-game-nav :game="$this->game" :player="$this->player"/>
    </div>

    <div class="py-4 text-purple max-w-3xl mb-16 mx-auto sm:px-6 lg:px-8">
        <p class="mb-2 pl-4 sm:pl-0">
            Round {{ $this->round->round_number }} of 8
        </p>
        <div class="mb-4">
            <x-round-template :round_template="$this->round->state()->round_template" />
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
            <div class="bg-white px-2 py-6">
                <div class="sm:px-4">
                    @foreach($this->round->state()->bureaucrats as $b)
                        <div class="border px-2 sm:px-6 rounded-xl border-gray-500 bg-gray-100 py-4 mb-4">
                            <div class="sm:flex sm:items-center">
                                <div>
                                    <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $b::NAME }}</h1>
                                    <p class="mt-2 text-sm text-gray-700 ">{{ $b::EFFECT }}</p>
                                </div>
                            </div>
                            @if($this->offers->filter(fn($o) => $o['bureaucrat'] === $b)->count() > 0)
                            <div class="flow-root">
                                <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                        <table class="min-w-full">
                                            <thead class="border-b border-gray-800">
                                                <tr class="text-sm">
                                                    <td>Industry</td>
                                                    <td>Modifications</td>
                                                    <td>Status</td>
                                                    <td>Amount</td>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach($this->offers->filter(fn($o) => $o['bureaucrat'] === $b) as $o)
                                                    <tr>
                                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ $o['industry'] }}</td>
                                                        <td class="px-3 py-4 text-sm text-gray-500">{{ $o['modifications'] }}</td>
                                                        <td>
                                                            @if($this->round->state()->status === 'auction')
                                                                <p>?</p>
                                                            @elseif( ! $o['bureaucrat']::HAS_WINNER)
                                                                <p class="text-teal">N/A</p>
                                                            @elseif ($o['is_blocked'])
                                                                <p class="text-red">Blocked</p>
                                                            @elseif($o['awarded'])
                                                                <p class="text-teal">Won</p>
                                                            @else
                                                                <p class="text-red">Lost</p>
                                                            @endif
                                                        </td>
                                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $o['offer'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <nav class="bg-gray-900 text-white fixed bottom-0 left-0 w-full p-4">
        <div class="flex flex-row max-w-full justify-between items-center">
            <div>
                Available: ${{ $this->player->state()->availableMoney() }}
            </div>
            @if($this->round->status === 'complete' && $this->round->game->state()->status !== 'complete')
                <button 
                    wire:click="readyUp"
                    class="rounded-md bg-teal px-3.5 py-2.5 ml-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Start Next Round
                </button>
            @elseif($this->round->status === 'complete' && $this->round->round_number === 8)
                <button 
                    wire:click="seeFinalScores"
                    class="rounded-md bg-teal px-3.5 py-2.5 ml-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    See Final Scores
                </button>
            @else
                <p class="text-xs">
                    Waiting for other players to finish
                </p>
            @endif
        </div>
    </nav>
</div>