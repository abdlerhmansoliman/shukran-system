<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(CustomerDataTable $datatable)
    {
        return $datatable->render('customers.index');
    }
    public function create()
    {
        return view('customers.create');
    }
    public function show(Customer $customer)
    {
        $customer->load([
            'level',
            'category.parent',
            'country',
            'creator',
            'customerPackages.package',
            'customerPackages.creator',
        ]);

        return view('customers.show', compact('customer'));
    }   

}
