<div>
<div class="my-4 overflow-hidden">
    <p class="pl-8">Round {{ $this->round()->round_number }} of 8</p>
    <p>{{ $this->number_of_offers_submitted }} offers submitted</p>
</div>
<livewire:headlines :game="$game" :key="'headline'"/>
<div class="py-4 text-purple">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
            <div>
                <div class="bg-purple text-white px-6 py-6 sm:px-6 lg:px-8">
                    <p class="text-sm font-semibold leading-6">Offer bribes</p>
                    <p class="mt-2 text-sm">
                        Offer bribes to the bureaucrats below. If your offer is higher than your opponents, 
                        the bureaucrat will work for you. If not, you will get your money back.
                    </p>
                </div>
                <ul role="list" class="divide-y divide-salmon px-6 py-6 sm:px-6 lg:px-8">
                    @foreach($bureaucrats as $i => $b)
                        <li class="flex flex-col items-center justify-between gap-x-6 py-5 {{ $loop->index % 2 === 0 ? 'bg-white' : 'bg-gray' }}"
                            x-data="{ show: false }"
                            {{-- :class="show ? 'bg-gray-200' : 'bg-white'" --}}
                        >
                            <div class="w-full">
                            <div class="flex justify-between">
                                <p class="text-sm font-semibold leading-6 text-gray-900">{{ $b['class']::NAME }}</p>
                                <div class="flex">
                                    <button 
                                        class="text-red font-extrabold text-m w-8 h-6"
                                        wire:click="decrement('{{ $b['class']::SLUG }}')"
                                    >
                                        -
                                    </button>
                                    <span 
                                        wire:model="bureaucrats.{{ $b['class']::SLUG }}.offer"
                                        class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium text-white bg-teal"
                                    >
                                        {{ $b['offer'] }}
                                    </span>
                                    <button 
                                        class="text-teal font-extrabold text-m w-8 h-6"
                                        wire:click="increment('{{ $b['class']::SLUG }}')"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                                {{-- <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                                    <p class="whitespace-nowrap">{{ $b['class']::SHORT_DESCRIPTION }}</p>
                                </div> --}}
                            </div>
                            <div class="w-full mt-2 text-sm">
                                <p>{{ $b['class']::EFFECT }}</p>
                                <p class="mt-2 italic text-gray-400 text-xs">{{ $b['class']::DIALOG }}</p>
                                @foreach(collect($b['class']::options($game->currentRound(), $this->player())) as $key => $value)
                                    <select
                                        wire:model="bureaucrats.{{ $b['class']::SLUG }}.data.{{ $key }}"
                                        class="mt-2 w-full text-sm"
                                    >
                                        @foreach($value as $key => $option)
                                            <option value="{{ $key }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @endforeach
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
            </div>
        </div>
    </div>
</div>