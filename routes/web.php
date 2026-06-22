<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerWalletController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupEnrollmentController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

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
    Route::post('customers/group-enrollments', [GroupEnrollmentController::class, 'bulkStore'])->name('customers.group-enrollments.store');
    Route::post('customers/{customer}/packages', [CustomerPackageController::class, 'store'])->name('customers.packages.store');
    Route::delete('customers/{customer}/subscriptions/{customerPackage}', [CustomerPackageController::class, 'destroy'])->name('customers.subscriptions.destroy');
    Route::get('customers/{customer}/payments/create', [CustomerPaymentController::class, 'create'])->name('customers.payments.create');
    Route::post('customers/{customer}/payments', [CustomerPaymentController::class, 'store'])->name('customers.payments.store');
    Route::get('customers/{customer}/wallet', [WalletController::class, 'show'])->name('customers.wallet.show');
    Route::get('customers/{customer}/wallet/top-up', [CustomerWalletController::class, 'create'])->name('customers.wallet.top-ups.create');
    Route::post('customers/{customer}/wallet/top-up', [CustomerWalletController::class, 'store'])->name('customers.wallet.top-ups.store');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('groups/{group}/customers', [GroupEnrollmentController::class, 'store'])->name('groups.customers.store');
    Route::patch('groups/{group}/customers/{groupEnrollment}', [GroupEnrollmentController::class, 'update'])->name('groups.customers.update');
    Route::delete('groups/{group}/customers/{groupEnrollment}', [GroupEnrollmentController::class, 'destroy'])->name('groups.customers.destroy');
    Route::get('groups/available-instructors', [GroupController::class, 'availableInstructors'])->name('groups.available-instructors');
    Route::resource('groups', GroupController::class);
    Route::resource('packages', PackageController::class)->except('show');
    Route::resource('discounts', DiscountController::class)->except('show');
    Route::resource('programs', ProgramController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('levels', LevelController::class)->except('show');
    Route::get('employees/{employee}/salary-payments/create', [EmployeeController::class, 'createSalaryPayment'])->name('employees.salary-payments.create');
    Route::post('employees/{employee}/salary-payments', [EmployeeController::class, 'storeSalaryPayment'])->name('employees.salary-payments.store');
    Route::get('employees/{employee}/payrolls/create', [EmployeeController::class, 'createPayroll'])->name('employees.payrolls.create');
    Route::post('employees/{employee}/payrolls', [EmployeeController::class, 'storePayroll'])->name('employees.payrolls.store');
    Route::resource('employees', EmployeeController::class);
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::delete('attendance/{employeeAbsence}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    Route::get('attendance/monthly', [AttendanceController::class, 'monthly'])->name('attendance.monthly');
    Route::resource('roles', RoleController::class)->except('show');
    Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

});

require __DIR__.'/auth.php';
