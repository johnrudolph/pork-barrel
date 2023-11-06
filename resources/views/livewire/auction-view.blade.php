<div>
    <ul role="list" class="divide-y divide-gray-100">
        @foreach($game->currentRound()->state()->bureaucrats as $bureaucrat)
            <li class="flex items-center justify-between gap-x-6 py-5">
                <div class="min-w-0">
                <div class="flex items-start gap-x-3">
                    <p class="text-sm font-semibold leading-6 text-gray-900">GraphQL API</p>
                    <p class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50 ring-green-600/20">Complete</p>
                </div>
                <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                    <p class="whitespace-nowrap">{{ $bureaucrat::DIALOG }}</p>
                    <svg viewBox="0 0 2 2" class="h-0.5 w-0.5 fill-current">
                    <circle cx="1" cy="1" r="1" />
                    </svg>
                    <p class="truncate">Created by Leslie Alexander</p>
                </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
