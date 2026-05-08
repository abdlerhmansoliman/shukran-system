<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    abort_unless(array_key_exists($locale, config('locales.supported', [])), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('customers/group-enrollments', [GroupController::class, 'bulkEnrollCustomers'])->name('customers.group-enrollments.store');
    Route::post('customers/{customer}/packages', [CustomerController::class, 'storePackage'])->name('customers.packages.store');
    Route::delete('customers/{customer}/subscriptions/{customerPackage}', [CustomerController::class, 'destroySubscription'])->name('customers.subscriptions.destroy');
    Route::get('customers/{customer}/payments/create', [CustomerController::class, 'createPayment'])->name('customers.payments.create');
    Route::post('customers/{customer}/payments', [CustomerController::class, 'storePayment'])->name('customers.payments.store');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('groups/{group}/customers', [GroupController::class, 'enrollCustomers'])->name('groups.customers.store');
    Route::resource('groups', GroupController::class);
    Route::resource('packages', PackageController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('levels', LevelController::class)->except('show');
    Route::get('employees/{employee}/salary-payments/create', [EmployeeController::class, 'createSalaryPayment'])->name('employees.salary-payments.create');
    Route::post('employees/{employee}/salary-payments', [EmployeeController::class, 'storeSalaryPayment'])->name('employees.salary-payments.store');
    Route::get('employees/{employee}/payrolls/create', [EmployeeController::class, 'createPayroll'])->name('employees.payrolls.create');
    Route::post('employees/{employee}/payrolls', [EmployeeController::class, 'storePayroll'])->name('employees.payrolls.store');
    Route::resource('employees', EmployeeController::class);
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

});

require __DIR__.'/auth.php';
