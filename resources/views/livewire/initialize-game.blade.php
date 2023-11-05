<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="bg-white">
                <div class="px-6 py-24 sm:px-6 sm:py-32 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        See how the sausage is made.
                    </h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-gray-600">
                        There's money to be made on Pork Barrel farms. <br/> Let's get to work.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <button 
                            wire:click="createGame"
                            class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            New Game
                        </button>
                        <div x-data="{ open: false }">
                            <button 
                                x-on:click="open = ! open"
                                class="text-sm font-semibold leading-6 text-gray-900"
                            >
                                Join Game
                                <span aria-hidden="true">→</span>
                            </button>
                            <div 
                                x-show="open"
                                x-on:click.away="open = false"
                                class="absolute z-10 w-screen max-w-md px-4 mt-3 transform -translate-x-1/2 left-1/2 sm:px-0"
                            >
                                <form>
                                    <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                                        <div class="relative grid gap-8 bg-white p-7 lg:grid-cols-2">
                                            <div class="space-y-4">
                                                <div>
                                                    <label for="game_code" class="block text-sm font-medium text-gray-700">Game Code</label>
                                                    <div class="mt-1">
                                                        <input 
                                                            wire:model="game_code"
                                                            type="text" 
                                                            name="game_code" 
                                                            id="game_code" 
                                                            class="block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button 
                                                wire:click="joinGame"
                                                type="button" 
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                                            >
                                                Join Game
                                            </button>
                                            <button 
                                                x-on:click="open = false"
                                                type="button" 
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>