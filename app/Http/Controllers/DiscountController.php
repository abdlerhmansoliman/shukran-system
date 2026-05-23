<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Discount;
use Illuminate\Support\Facades\Gate;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('view discounts');
        $discounts = Discount::query()->latest()->paginate(10);
        return view('discounts.index', compact('discounts'));
    }

    public function create()
    {
        Gate::authorize('create discounts');
        return view('discounts.create');
    }

    public function store(StoreDiscountRequest $request)
    {
        Gate::authorize('create discounts');
        Discount::create($request->validated());
        return redirect()->route('discounts.index')->with('success', __('Discount created successfully.'));
    }

    public function edit(Discount $discount)
    {
        Gate::authorize('edit discounts');
        return view('discounts.edit', compact('discount'));
    }

    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        Gate::authorize('edit discounts');
        $discount->update($request->validated());
        return redirect()->route('discounts.index')->with('success', __('Discount updated successfully.'));
    }

    public function destroy(Discount $discount)
    {
        Gate::authorize('delete discounts');
        $discount->delete();
        return redirect()->route('discounts.index')->with('success', __('Discount deleted successfully.'));
    }
}
