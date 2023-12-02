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
            @foreach($bureaucrats as $index => $bureaucrat)
                <livewire:bureaucrat-form 
                    :bureaucrat="$index" 
                    :money="$this->money" 
                    :player="$this->player" 
                    wire:model.live="bureaucrats.{{$index}}"
                    
                />
            @endforeach
            <div class="mt-4 flex flex-col">
                <p class="text-sm font-semibold leading-6 text-gray-900">
                    Total Offers: {{ collect($bureaucrats)->sum('bid') }}
                </p>
                <p class="text-sm font-semibold leading-6 text-gray-900">
                    Money available to offer: {{ $this->money }}
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
