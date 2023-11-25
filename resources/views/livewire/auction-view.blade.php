<div>
<div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-6 sm:px-6 lg:px-8">
    @if ($offers)
        <div>
            <p>
                You have already submitted offers for this round. Sit back and wait for the others.
            </p>
        </div>
    @else
        <p class="text-sm font-semibold leading-6 text-gray-900">Offer bribes</p>
        <p class="mt-2 text-sm text-gray-600">
            Offer bribes to the bureaucrats to enrich yourself. If your offer is higher than your opponents, 
            you will get the benefit of the bureaucrat's effect. If not, you will get your money back.
        </p>
        <ul role="list" class="divide-y divide-gray-100">
            @foreach($bureaucrats as $b)
                <li class="flex flex-col items-center justify-between gap-x-6 py-5"
                    x-data="{ show: false }"
                    {{-- :class="show ? 'bg-gray-200' : 'bg-white'" --}}
                >
                    <div class="w-full">
                    <div class="flex justify-between">
                        <p class="text-sm font-semibold leading-6 text-gray-900">{{ $b['class']::NAME }}</p>
                        <div class="flex">
                            <button 
                                class="text-slate-600 font-extrabold text-m w-8 h-6"
                                wire:click="decrement('{{ $b['class']::SLUG }}')"
                            >
                                -
                            </button>
                            <span 
                                wire:model="bureaucrats.{{ $b['class']::SLUG }}.offer"
                                class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50 ring-green-600/20"
                            >
                                {{ $b['offer'] }}
                            </span>
                            <button 
                                class="text-slate-600 font-extrabold text-m w-8 h-6"
                                wire:click="increment('{{ $b['class']::SLUG }}')"
                            >
                                +
                            </button>
                        </div>
                    </div>
                        <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                            <p class="whitespace-nowrap">{{ $b['class']::SHORT_DESCRIPTION }}</p>
                        </div>
                    </div>
                    <div class="w-full mt-2 text-sm">
                        <p>{{ $b['class']::EFFECT }}</p>
                        @if($b['class']::EFFECT_REQUIRES_DECISION)
                            <select
                                wire:model="bureaucrats.{{ $b['class']::SLUG }}.data"
                                class="mt-2 w-full text-sm"
                            >
                                @foreach($b['class']::options($game->currentRound(), $this->player()) as $key => $option)
                                    <option value="{{ $key }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @endif
                        <p class="mt-2 italic text-gray-400 text-xs">{{ $b['class']::DIALOG }}</p>
                    </div>
                </li>
            @endforeach
            <div class="mt-4 flex flex-col">
                <p class="text-sm font-semibold leading-6 text-gray-900">
                    Total Offers: {{ collect($bureaucrats)->sum('bid') }}
                </p>
                <p class="text-sm font-semibold leading-6 text-gray-900">
                    Money available to offer: {{ $money - collect($bureaucrats)->sum('offer') }}
                </p>
            </div>

            <button wire:click="submit">
                Submit
            </button>
        </ul>
    @endif
                </div>
            </div>
        </div>
</div>
