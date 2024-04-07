<div class="max-w-3xl mx-auto">
    <div class="px-4 pt-8 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-900">Rules</h1>
            <div class="flex flex-col space-y-4 my-4">
                <p class="text-sm">
                    Pork Barrel is a fast-paced multiplayer auction game. You make money by bribing the corrupt Bureaucrats at Pork Barrel farms. Outwit your opponents, bluff, and bribe your way to untold fortunes.
                </p>
                <h2 class="text-lg font-bold">
                    Objective
                </h2>
                <p class="text-sm">
                    The objective of Pork Barrel is to have the most money at the end of the game.
                </p>
                <h2 class="text-lg font-bold">
                    Gameplay
                </h2>
                <p class="text-sm">
                    The game has 8 rounds. Each round has a Modifier and a set of Bureaucrats to whom you will make offers. For a copmlete list of Bureaucrats and Round Modifiers, see the table below.
                </p>
                <h3 class="text-md font-semibold">
                    Round Start
                </h3>
                <p class="text-sm">
                    At the beginning of each round, you will receive an income of 5 money. 
                </p>
                <h3 class="text-md font-semibold">
                    Auction
                </h3>
                <p class="text-sm">
                    Once the round has begun, you will be able to make offers to any of the Bureaucrats available. You can make as many offers as you like. If you make the highest offer for a Bureaucrat, you will be charged the money you offered, and you will receive their effect. Otherwise, you will not be charged and you will not receive thier effect.
                </p>
                <h3 class="text-md font-semibold">
                    End of Round
                </h3>
                <p class="text-sm">
                    Once all players have submitted their offers, the round will end and each Bureaucrat will resolve their effect. 
                </p>
                <h3 class="text-md font-semibold">
                    Round Lifecycle
                </h3>
                <p class="text-sm">
                    Each Bureaucrat and Modifier happens at a specific point in the round's lifecycle. Not the exact wording to know when their effects will happen. The points at which effects can happen are:
                </p>
                <ul class="list-disc pl-4 text-sm">
                    <li><strong>Round start</strong></li>
                    <li><strong>After submitting offers</strong>: after you have submitted your offers, but before the Auction is over</li>
                    <li><strong>At the end of the Auction</strong>: after all players have submitted their offers, but before the end of the round</li>
                    <li><strong>At the end of the round</strong>: when most Bureaucrats resolve</li>
                    <li><strong>After the round ends</strong>: after most Bureaucrats have resolved</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="pt-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold leading-6 text-gray-900">Round Modifiers</h1>
                <p class="text-sm text-gray-700"></p>
            </div>
        </div>
        <div class="mt-6 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Effect</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($round_templates as $r)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $r::HEADLINE}}</td>
                        <td class=" px-3 py-4 text-sm text-gray-500">{{ $r::EFFECT }}</td>
                    </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- bureaucrats --}}
    <div class="pt-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-900">Bureaucrats</h1>
            <p class="text-sm text-gray-700"></p>
            </div>
        </div>
        <div class="mt-6 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Effect</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($bureaucrats as $b)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $b::NAME}}</td>
                        <td class=" px-3 py-4 text-sm text-gray-500">{{ $b::EFFECT }}</td>
                    </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
