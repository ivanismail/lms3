<?php

use App\Livewire\PresensiPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/m/{code}', PresensiPage::class)->name('meeting-confirmation');
