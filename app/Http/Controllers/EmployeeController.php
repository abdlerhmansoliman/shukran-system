<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;

class EmployeeController extends Controller
{
    public function index(EmployeeDataTable $datatable)
    {
        return $datatable->render('employees.index');
    }
}
