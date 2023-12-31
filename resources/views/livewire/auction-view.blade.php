<div>
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
                    @foreach($offers as $i => $o)
                        <li class="flex flex-col items-center justify-between gap-x-6 py-5 {{ $loop->index % 2 === 0 ? 'bg-white' : 'bg-gray' }}">
                            <div class="w-full">
                                <div class="flex justify-between">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ $o->bureaucrat::NAME }}</p>
                                    <div class="flex">
                                        <button 
                                            class="text-red font-extrabold text-m w-8 h-6"
                                            wire:click="decrement('{{ $o->bureaucrat::SLUG }}')"
                                        >
                                            -
                                        </button>
                                        <span 
                                            wire:model="offers.{{ $o->bureaucrat::SLUG }}.amount_offered"
                                            class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium text-white bg-teal"
                                        >
                                            {{ $o->amount_offered }}
                                        </span>
                                        <button 
                                            class="text-teal font-extrabold text-m w-8 h-6"
                                            wire:click="increment('{{ $o->bureaucrat::SLUG }}')"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full mt-2 text-sm">
                                <p>{{ $o->bureaucrat::EFFECT }}</p>
                                <p class="mt-2 italic text-gray-400 text-xs">{{ $o->bureaucrat::DIALOG }}</p>
                                @if($o->data)
                                @foreach(collect($o->data) as $key => $value)
                                    <select
                                        wire:model="offers.{{ $o->bureaucrat::SLUG }}.data.{{ $key }}"
                                        class="mt-2 w-full text-sm"
                                    >
                                        <option value="" placeholder>{{ $o->options[$key]['placeholder'] }}</option>
                                        @foreach($o->options[$key]['options'] as $id => $option)
                                            <option value="{{ $id }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @endforeach
                                @endif
                            </div>
                        </li>
                    @endforeach
                    <div class="mt-4 flex flex-col">
                        <p class="text-sm font-semibold leading-6 text-gray-900">
                            Total Offers: {{ collect($offers)->sum('ammount_offered') }}
                        </p>
                        <p class="text-sm font-semibold leading-6 text-gray-900">
                            Money available to offer: {{ $money - collect($offers)->sum('ammount_offered') }}
                        </p>
                    </div>

                    @if(session()->has('error'))
                        <div class="mt-4 flex flex-col">
                            <p class="text-sm font-semibold leading-6 text-red-900">
                                {{ session('error') }}
                            </p>
                        </div>
                    @endif

                    <button wire:click="submit">
                        Submit
                    </button>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
