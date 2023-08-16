<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\helpers\constGuards;
use App\helpers\constDefaults;

Route::prefix('admin')->name('admin.')->group(function(){

    Route::middleware(['guest:admin'])->group(function(){
        Route::view('/login', 'back.pages.admin.auth.login')->name('login');
        Route::post('/login_handler', [AdminController::class, 'loginHandler'])->name('login_handler');
        Route::view('/forgot-password', 'back.pages.admin.auth.forgot-password')->name('forgot-password');
        Route::post('/send-password-reset-link', [AdminController::class, 'sendPasswordResetLink'])->name('send-password-reset-link');
        Route::get('/password/reset/{token}/{email}', [AdminController::class, 'resetPassword'])->name('reset_password'); 
        Route::post('reset-password-handler',[AdminController::class, 'resetPasswordHandler'])->name('reset-password-handler');
    });

    Route::middleware(['auth:admin'])->group(function(){
        Route::view('/home', 'back.pages.admin.home')->name('home');
        Route::post('/logout_handler',[AdminController::class, 'logoutHandler'])->name('logout_handler');
        Route::get('/profile', [AdminController::class, 'profileView'])->name('profile');
    });

});


