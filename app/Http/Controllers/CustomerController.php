<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(CustomerDataTable $datatable)
    {
        dd('sss');
        return $datatable->render('customer.index');
    }
}
