<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerFeedbackStoreRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomerFeedbackController extends Controller
{
    public function store(CustomerFeedbackStoreRequest $request, Customer $customer): RedirectResponse
    {
        $customer->feedbacks()->create([
            'level_id' => $request->input('level_id'),
            'feedback' => $request->input('feedback'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('customers.show', $customer)
            ->with('success', __('Feedback added successfully.'));
    }
}
