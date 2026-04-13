<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    abort_unless(array_key_exists($locale, config('locales.supported', [])), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
});
Route::get('customers',[CustomerController::class,'index'])->name('customers.index');
Route::get('customers/create',[CustomerController::class,'create'])->name('customers.create');
Route::get('customers/{customer}',[CustomerController::class,'show'])->name('customers.show');

require __DIR__.'/auth.php';
