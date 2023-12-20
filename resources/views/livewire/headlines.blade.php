<div>
    <div class="mt-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-8 sm:px-6 sm:py-8 lg:px-8 bg-teal text-white">
                    <div>
                        <p class="text-sm font-semibold leading-6">Today's headlines</p>
                        <p class="mt-2">{{ $this->headline::HEADLINE }}</p>
                        <p class="mt-2 italic text-xs">{{ $this->headline::FLAVOR_TEXT }}</p>
                        <p class="mt-2 text-sm">{{ $this->headline::EFFECT }}</p>
                    </div>
                    @if($this->other_headlines->count() > 0)
                    <div x-data="{ open: false }">
                        <button x-on:click="open = ! open" class="text-sm text-purple mt-2">Show previous headlines</button>
                    
                        <div x-show="open" class="mt-2">
                            @foreach($this->other_headlines as $h)
                                <div>
                                    <p class="mt-2">{{ $h->headline }}</p>
                                    <p class="mt-2 text-sm">{{ $h->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
