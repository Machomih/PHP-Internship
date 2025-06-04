<?php

use App\Http\Controllers\NameController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/greeting', function () {

    return 'Hello World';

});

Route::redirect('/redirect', '/greeting', '301');

/*
Route::get('/names', [NameController::class,'index']);
Route::get('/names/create', [NameController::class,'create']);
Route::post('/names', [NameController::class,'store']);
Route::get('/names/{id}', [NameController::class,'show']);
Route::get('/names/{id}/edit', [NameController::class,'edit']);
Route::put('/names/{id}', [NameController::class,'update']);
Route::delete('/names/{id}', [NameController::class,'destroy']);

This all can be summed in the line below
*/
Route::resource('/names', NameController::class);

Route::get('/rps/{choice}', function ($choice) {
    $result = rpsBattle($choice);
    
    if (is_string($result)) {
        return $result;
    }

    return $result ? 'You won' : 'You lose';
});

function rpsBattle($playerChoice) {
    $choices = ['rock', 'paper', 'scissors'];
    
    $playerChoice = strtolower($playerChoice);
    
    if (!in_array($playerChoice, $choices)) {
        return 'Invalid choice';
    }

    $computerChoice = $choices[rand(0, 2)];

    if ($playerChoice === $computerChoice) {
        return "Draw! Both chose $playerChoice.";
    }

    $winsAgainst = [
        'rock' => 'scissors',
        'scissors' => 'paper',
        'paper' => 'rock'
    ];

    if ($winsAgainst[$playerChoice] === $computerChoice) {
        return "You win! $playerChoice beats $computerChoice.";
    } else {
        return "You lose! $computerChoice beats $playerChoice.";
    }
}


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';