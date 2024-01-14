<div>
    <div class="my-4 max-w-7xl mx-auto sm:px-6 lg:px-8 overflow-hidden flex flex-row justify-between">
        @if($this->round->status === 'complete')
            <button 
                wire:click="readyUp"
                class="rounded-md bg-teal mr-4 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Start Next Round
            </button>
        @endif
    </div>
    <div class="py-4 text-purple max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
            <div class="bg-white px-6 py-6 sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    @if($this->offers_made->count() > 0)
                        <div class="sm:flex sm:items-center">
                            <div class="sm:flex-auto">
                            <h1 class="text-base font-semibold leading-6 text-gray-900">Your offers</h1>
                            <p class="mt-2 text-sm text-gray-700">If your offers are higher than your opponents', you will get this Effect. Otherwise, you'll get your money back.</p>
                            </div>
                        </div>
                        <div class="mt-8 flow-root">
                            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Bureaucrat</th>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Status</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Your Offer</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Effect</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($this->offers_made as $o)
                                            <tr>
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ $o['bureaucrat']::NAME }}</td>
                                                <td>
                                                    @if($this->round->status === 'auction')
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
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $o['bureaucrat']::SHORT_DESCRIPTION }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-700">You made no offers this round.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>