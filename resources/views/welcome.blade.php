
    <body class="bg-new-tan">
        <x-app-layout>
            <div class="max-w-md mx-auto p-8 bg-white rounded-lg mt-16 shadow-md">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 text-center">Pork Barrel</h2>
                    <p class="mt-6">
                        Ready to see how the sausage is made? Pork Barrel is a fast-paced multiplayer auction game. You make money by bribing the corrupt bureaucrats at Pork Barrel farms. Outwit your opponents, bluff, and bribe your way to untold fortunes.
                    </p>
                </div>
                <div class="mt-8 flex flex-row justify-center items-center space-x-4">
                    <span class="text-md py-2 px-4 bg-teal rounded-md text-white">
                        <a href="{{ route('login') }}">Login</a>
                    </span>
                    <span class="text-md py-2 px-4 bg-purple rounded-md text-white">
                        <a href="{{ route('register') }}">Create Account</a>
                    </span>
                    <a href="{{ route('rules') }}">Read the rules</a>
                </div>
            </div>
        </x-app-layout>
    </body>
