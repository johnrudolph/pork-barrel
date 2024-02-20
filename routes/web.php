<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\AuctionView;
use App\Livewire\AwaitingNextRoundView;
use App\Livewire\PreGameLobby;
use App\Livewire\RulesPage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('register');
});

Route::get('rules', RulesPage::class)->name('rules');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('games/{game}/pre-game', PreGameLobby::class)->name('games.pre-game');
    Route::get('games/{game}/rounds/{round}/auction', AuctionView::class)->name('games.auction');
    Route::get('games/{game}/rounds/{round}/waiting', AwaitingNextRoundView::class)->name('games.waiting');
});

require __DIR__.'/auth.php';
