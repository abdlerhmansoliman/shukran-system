<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Discount;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = Discount::query()->latest()->paginate(10);
        return view('discounts.index', compact('discounts'));
    }

    public function create()
    {
        return view('discounts.create');
    }

    public function store(StoreDiscountRequest $request)
    {
        Discount::create($request->validated());
        return redirect()->route('discounts.index')->with('success', __('Discount created successfully.'));
    }

    public function edit(Discount $discount)
    {
        return view('discounts.edit', compact('discount'));
    }

    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        $discount->update($request->validated());
        return redirect()->route('discounts.index')->with('success', __('Discount updated successfully.'));
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('discounts.index')->with('success', __('Discount deleted successfully.'));
    }
}
