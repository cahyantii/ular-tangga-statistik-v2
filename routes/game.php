<?php

use App\Http\Controllers\Game\GameController;
use App\Http\Controllers\Game\MatchmakingController;
use App\Http\Controllers\Game\RobotSessionController;
use Illuminate\Support\Facades\Route;

Route::post('/vs-robot', [RobotSessionController::class, 'store'])
    ->middleware('no-active-session')
    ->name('robot.store');

Route::prefix('multiplayer')->name('multiplayer.')->group(function () {
    Route::get('/', [MatchmakingController::class, 'lobby'])->name('lobby');

    Route::post('/quick-match', [MatchmakingController::class, 'quickMatch'])
        ->middleware('no-active-session')
        ->name('quick-match');

    Route::post('/room', [MatchmakingController::class, 'createRoom'])
        ->middleware('no-active-session')
        ->name('room.store');

    Route::post('/room/join', [MatchmakingController::class, 'joinRoom'])
        ->middleware('no-active-session')
        ->name('room.join');
});

Route::get('/room/{room}', [MatchmakingController::class, 'showRoom'])->name('room.show');
Route::get('/room/{room}/status', [MatchmakingController::class, 'roomStatus'])->name('room.status');
Route::post('/room/{room}/cancel', [MatchmakingController::class, 'cancelRoom'])->name('room.cancel');

Route::get('/{gameSession}', [GameController::class, 'show'])->name('show');
Route::get('/{gameSession}/state', [GameController::class, 'state'])->name('state');
Route::post('/{gameSession}/roll', [GameController::class, 'roll'])->name('roll');
Route::post('/{gameSession}/answer', [GameController::class, 'answer'])->name('answer');
Route::post('/{gameSession}/duel-answer', [GameController::class, 'duelAnswer'])->name('duel-answer');
Route::post('/{gameSession}/leave', [GameController::class, 'leave'])->name('leave');
Route::post('/{gameSession}/heartbeat', [GameController::class, 'heartbeat'])->name('heartbeat');
