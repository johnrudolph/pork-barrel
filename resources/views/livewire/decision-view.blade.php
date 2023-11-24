<div>
    <ul role="list" class="divide-y divide-gray-100">
        @foreach($actions as $a)
            <li 
                x-data="{ show: false }"
                class="flex flex-col"
                {{-- :class="show ? 'bg-gray-200' : 'bg-white'" --}}
            >
                <div class="flex items-center justify-between gap-x-6 py-5 w-full">
                    <div>
                        <div class="flex justify-between">
                            <p class="text-sm font-semibold leading-6 text-gray-900">{{ $a::NAME }}</p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                            <p class="whitespace-nowrap">{{ $a::SHORT_DESCRIPTION }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-right" >
                        @if($a::EFFECT_REQUIRES_DECISION)
                            <button class="text-sm text-right" @click="show = !show">
                                {{ $a::SELECT_PROMPT}}
                            </button>
                        @else
                            ✅
                        @endif
                    </div>
                </div>
                <div class="w-full" x-show="show">
                    <select
                        wire:model="decisions.{{ $a::SLUG }}"
                        class="w-full"
                    >
                    @foreach($a::options($game->currentRound(), $this->player()) as $key => $option)
                        <option value="{{ $key }}">{{ $option }}</option>
                    @endforeach
                    </select>
                </div>
            </li>
        @endforeach

        <button wire:click="submit">
            Submit
        </button>
    </ul>
</div>
