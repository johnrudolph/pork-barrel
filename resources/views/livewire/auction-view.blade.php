<div>
    <div class="py-4 text-purple">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-2">
                <x-round-template :round_template="$this->round->state()->round_template" />
            </div>
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
                                        <div class="relative flex items-center max-w-[11rem]">
                                            <button 
                                                type="button" 
                                                id="decrement-button" 
                                                data-input-counter-decrement="bedrooms-input" 
                                                wire:click="decrement('{{ $o->bureaucrat::SLUG }}')"
                                                class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none"
                                            >
                                                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                                                </svg>
                                            </button>
                                            <span 
                                                class="bg-gray-50 w-11 h-11 pt-1 font-medium text-center text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block pb-6 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                            >
                                                {{ $o->amount_offered }}
                                            </span>
                                            <div class="absolute bottom-1 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 flex items-center text-xs text-gray-400 space-x-1 rtl:space-x-reverse">
                                                <span>Offer</span>
                                            </div>
                                            <button 
                                                type="button" 
                                                id="increment-button" 
                                                data-input-counter-increment="bedrooms-input" 
                                                wire:click="increment('{{ $o->bureaucrat::SLUG }}')"
                                                class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none"
                                            >
                                                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                                                </svg>
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

                        <button 
                            type="button" 
                            class="rounded-md bg-indigo-500 px-3.5 py-2.5 mt-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                            wire:click="submit"
                        >
                            Submit
                        </button>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <livewire:in-game-nav :game="$this->game" :player="$this->player"/>
    </div>
</div>
