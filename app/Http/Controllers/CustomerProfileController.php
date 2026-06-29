<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileStoreRequest;
use App\Models\Customer;

class CustomerProfileController extends Controller
{
    public function store(ProfileStoreRequest $request, Customer $customer)
    {
        $profile = $customer->profiles()->create($request->profileData());

        return redirect()
            ->route('customers.show', [$customer, 'profile_id' => $profile->id])
            ->with('success', __('Profile added successfully.'));
    }
}
