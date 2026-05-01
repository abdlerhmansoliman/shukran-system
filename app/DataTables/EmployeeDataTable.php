<?php

namespace App\DataTables;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmployeeDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Employee>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('employee', function (Employee $employee) {
                $name = $employee->user?->name ?: __('Unnamed employee');
                $initials = Str::of($name)
                    ->explode(' ')
                    ->filter()
                    ->take(2)
                    ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
                    ->implode('');

                return '
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold tracking-wide text-white">
                            '.e($initials ?: 'NA').'
                        </div>
                        <div>
                            <a href="'.e(route('employees.show', $employee->id)).'" class="font-semibold text-slate-900 transition hover:text-indigo-600 hover:underline">
                                '.e($name).'
                            </a>
                            <div class="text-sm text-slate-500">'.e($employee->user?->email ?: __('No email provided')).'</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('department', function (Employee $employee) {
                if (! $employee->department) {
                    return '<span class="text-sm text-slate-400">'.e(__('Not assigned')).'</span>';
                }

                return '<span class="font-medium text-slate-700">'.e($employee->department->name).'</span>';
            })
            ->editColumn('job_title', function (Employee $employee) {
                return $employee->job_title
                    ? '<span class="text-slate-700">'.e($employee->job_title).'</span>'
                    : '<span class="text-sm text-slate-400">'.e(__('Not specified')).'</span>';
            })
            ->editColumn('phone', function (Employee $employee) {
                return $employee->phone
                    ? '<span class="font-medium text-slate-700">'.e($employee->phone).'</span>'
                    : '<span class="text-sm text-slate-400">'.e(__('Not specified')).'</span>';
            })
            ->editColumn('age', function (Employee $employee) {
                return $employee->age
                    ? '<span class="text-slate-700">'.e($employee->age).'</span>'
                    : '<span class="text-sm text-slate-400">'.e(__('Not specified')).'</span>';
            })
            ->editColumn('basic_salary', function (Employee $employee) {
                return '<span class="font-semibold text-slate-900">'.e(number_format((float) $employee->basic_salary, 2)).'</span>';
            })
            ->editColumn('salary_type', function (Employee $employee) {
                return '<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">'.e(__(Str::headline($employee->salary_type))).'</span>';
            })
            ->editColumn('status', function (Employee $employee) {
                $classes = $employee->status === 'active'
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$classes.'">'.e(__(Str::headline($employee->status))).'</span>';
            })
            ->editColumn('hire_date', function (Employee $employee) {
                return $employee->hire_date
                    ? '<span class="whitespace-nowrap text-slate-700">'.e($employee->hire_date->format('M d, Y')).'</span>'
                    : '<span class="text-sm text-slate-400">'.e(__('Not specified')).'</span>';
            })
            ->addColumn('action', function (Employee $employee) {
                return view('components.employee-datatable-actions', compact('employee'))->render();
            })
            ->filterColumn('employee', function (QueryBuilder $query, string $keyword) {
                $query->whereHas('user', function (QueryBuilder $builder) use ($keyword) {
                    $builder
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('department', function (QueryBuilder $query, string $keyword) {
                $query->whereHas('department', function (QueryBuilder $builder) use ($keyword) {
                    $builder->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['employee', 'department', 'job_title', 'phone', 'age', 'basic_salary', 'salary_type', 'status', 'hire_date', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Employee>
     */
    public function query(Employee $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['user', 'department'])
            ->select('employees.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('employee-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search employees...'),
                    'lengthMenu' => __('Show _MENU_ employees'),
                    'info' => __('Showing _START_ to _END_ of _TOTAL_ employees'),
                    'infoEmpty' => __('No employees available'),
                    'zeroRecords' => __('No matching employees found'),
                    'paginate' => [
                        'previous' => __('Previous'),
                        'next' => __('Next'),
                    ],
                ],
                'dom' => "<'employee-table-toolbar flex flex-col gap-4 border-b border-slate-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between'<'flex flex-col gap-4 sm:flex-row sm:items-center'lf><'text-sm text-slate-500'i>>".
                    "<'overflow-x-auto'tr>".
                    "<'flex flex-col gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between'<'text-sm text-slate-500'i><'pagination-wrap'p>>",
                'drawCallback' => 'function() {
                    const wrapper = this.api().table().container();
                    wrapper.querySelectorAll("thead th").forEach((th) => {
                        th.classList.add("bg-slate-50", "text-xs", "font-semibold", "uppercase", "tracking-[0.16em]", "text-slate-500");
                    });
                }',
                'initComplete' => 'function() {
                    const wrapper = this.api().table().container();
                    const filterInput = wrapper.querySelector(".dataTables_filter input");
                    const lengthSelect = wrapper.querySelector(".dataTables_length select");

                    if (filterInput) {
                        filterInput.className = "w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10";
                    }

                    if (lengthSelect) {
                        lengthSelect.className = "rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10";
                    }
                }',
            ])
            ->orderBy(10, 'desc')
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')
                ->title('#')
                ->searchable(false)
                ->orderable(false)
                ->width(40)
                ->addClass('text-slate-400'),

            Column::computed('employee')
                ->title(__('Employee'))
                ->searchable(true)
                ->orderable(false)
                ->addClass('min-w-[300px]'),
            Column::computed('department')
                ->title(__('Department'))
                ->searchable(true)
                ->orderable(false)
                ->addClass('whitespace-nowrap min-w-[180px]'),
            Column::make('job_title')->title(__('Job Title'))->addClass('min-w-[180px]'),
            Column::make('phone')->title(__('Phone'))->addClass('whitespace-nowrap min-w-[160px]'),
            Column::make('age')->title(__('Age'))->addClass('whitespace-nowrap'),
            Column::make('salary_type')->title(__('Salary Type'))->addClass('whitespace-nowrap'),
            Column::make('basic_salary')->title(__('Basic Salary'))->addClass('whitespace-nowrap'),
            Column::make('status')->title(__('Status')),
            Column::make('hire_date')->title(__('Hire Date'))->addClass('whitespace-nowrap min-w-[140px]'),
            Column::make('created_at')->title(__('Created'))->addClass('whitespace-nowrap min-w-[160px]')->render("data ? new Date(data).toLocaleDateString() : 'N/A'"),

            Column::computed('action')
                ->title(__('Actions'))
                ->searchable(false)
                ->orderable(false)
                ->width(180)
                ->addClass('text-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Employee_'.date('YmdHis');
    }
}
