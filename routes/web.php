<?php

use App\Http\Controllers\TextCheckerController;

// Route to display the form
Route::get('/', [TextCheckerController::class, 'showForm'])->name('checker.form');

// Route to handle the form submission and check the text
Route::post('/', [TextCheckerController::class, 'check'])->name('checker.check');
