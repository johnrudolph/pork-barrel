<div>
    <p>hello world</p>
    <li class="flex flex-col items-center justify-between gap-x-6 py-5">
        <div class="w-full">
        <div class="flex justify-between">
            <p class="text-sm font-semibold leading-6 text-gray-900">{{ $this->bureaucrat::NAME }}</p>
            <div class="flex">
                <button 
                    class="text-slate-600 font-extrabold text-m w-8 h-6"
                    wire:click="decrement"
                >
                    -
                </button>
                <span 
                    wire:model="offer.amount"
                    class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50 ring-green-600/20"
                >
                    {{ $offer['amount'] }}
                </span>
                <button 
                    class="text-slate-600 font-extrabold text-m w-8 h-6"
                    wire:click="increment()"
                >
                    +
                </button>
            </div>
        </div>
            <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                <p class="whitespace-nowrap">{{ $this->bureaucrat::SHORT_DESCRIPTION }}</p>
            </div>
        </div>
        <div class="w-full mt-2 text-sm">
            <p>{{ $this->bureaucrat::EFFECT }}</p>
            <p class="mt-2 italic text-gray-400 text-xs">{{ $this->bureaucrat::DIALOG }}</p>
            {{-- @foreach(collect($this->bureaucrat::options($player->game->currentRound(), $player)) as $key => $value)
                <select
                    wire:model="offer.data.{{ $key }}"
                    class="mt-2 w-full text-sm"
                >
                    @foreach($value as $key => $option)
                        <option value="{{ $key }}">{{ $option }}</option>
                    @endforeach
                </select>
            @endforeach --}}
        </div>
    </li>
</div>