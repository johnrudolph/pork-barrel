<div>
    <ul role="list" class="divide-y divide-gray-100">
        @foreach($bids as $b)
            <li class="flex items-center justify-between gap-x-6 py-5">
                <div class="w-full">
                <div class="flex justify-between">
                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ $b['class']::NAME }}</p>
                    <div class="flex">
                        <button 
                            class="text-slate-600 font-extrabold text-m w-8 h-6"
                            wire:click="decrement('{{ $b['slug'] }}')"
                        >
                            -
                        </button>
                        <p class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50 ring-green-600/20">
                            {{ $b['bid'] }}
                        </p>
                        <button 
                            class="text-slate-600 font-extrabold text-m w-8 h-6"
                            wire:click="increment('{{ $b['slug'] }}')"
                        >
                            +
                        </button>
                    </div>
                </div>
                <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                    <p class="whitespace-nowrap">{{ $b['class']::SHORT_DESCRIPTION }}</p>
                </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
