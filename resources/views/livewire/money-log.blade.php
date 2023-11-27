<div>
    <div class="mt-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-100 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-8 sm:px-6 sm:py-8 lg:px-8">
                    <div class="flow-root">
                    <p class="text-sm font-semibold leading-6 text-gray-900 mb-4">Your Money History</p>
                    <ul role="list" class="-mb-8">
                        @foreach($entries as $entry)
                        <li>
                        <div class="relative pb-8">
                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
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
</div>
