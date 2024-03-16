<div wire:poll>
    <div>
        <livewire:in-game-nav :game="$this->game" :player="$this->player"/>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 overflow-hidden flex flex-row justify-between">
        @if($this->round->status === 'complete')
            <button 
                wire:click="readyUp"
                class="rounded-md bg-teal mr-4 px-3.5 py-2.5 ml-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Start Next Round
            </button>
        @endif
    </div>
    <div class="py-4 text-purple max-w-7xl mx-auto lg:px-8">
        <div class="mb-4">
            <x-round-template :round_template="$this->round->state()->round_template" />
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
            <div class="bg-white py-6 lg:px-8">
                <div>
                    @foreach($this->round->state()->bureaucrats as $b)
                        <div class="border rounded-xl border-gray-500 bg-gray-100 py-4 px-4 mb-4">
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
</div>