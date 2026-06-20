<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Gate;

class WalletController extends Controller
{
    public function show(Customer $customer)
    {
        Gate::authorize('view customers');

        $customer->load([
            'payments' => fn ($query) => $query->latest('paid_at')->latest(),
            'payments.creator',
            'payments.paymentMethod',
            'payments.customerPackage.package',
            'customerPackages' => fn ($query) => $query
                ->where('status', 'active')
                ->where('remaining_amount', '>', 0)
                ->orderBy('start_date')
                ->orderBy('created_at'),
            'customerPackages.package',
        ]);

        return view('customers.wallet', [
            'customer' => $customer,
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
