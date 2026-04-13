<?php

namespace App\DataTables;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Customer> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('customer', function (Customer $customer) {
                $name = trim($customer->first_name . ' ' . $customer->last_name);
                $initials = Str::of($name)
                    ->explode(' ')
                    ->filter()
                    ->take(2)
                    ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
                    ->implode('');

                $email = $customer->email ?: __('No email provided');

                return '
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold tracking-wide text-white">
                            ' . e($initials ?: 'NA') . '
                        </div>
                        <div>
                            <a href="' . e(route('customers.show', $customer->id)) . '" class="font-semibold text-slate-900 transition hover:text-indigo-600 hover:underline">
                                ' . e($name ?: __('Unnamed customer')) . '
                            </a>
                            <div class="text-sm text-slate-500">' . e($email) . '</div>
                        </div>
                    </div>
                ';
            })
            ->editColumn('phone', function (Customer $customer) {
                return '<span class="font-medium text-slate-700">' . e($customer->phone) . '</span>';
            })
            ->editColumn('status', function (Customer $customer) {
                $classes = $customer->status === 'active'
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ' . $classes . '">' . e(Str::headline($customer->status)) . '</span>';
            })
            ->editColumn('source', function (Customer $customer) {
                if (! $customer->source) {
                    return '<span class="text-sm text-slate-400">' . e(__('Not specified')) . '</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 ring-1 ring-inset ring-sky-600/20">' . e(Str::headline($customer->source)) . '</span>';
            })
            ->addColumn('action', function (Customer $customer) {
                return view('components.datatable-actions', compact('customer'))->render();
            })
            ->filterColumn('customer', function (QueryBuilder $query, string $keyword) {
                $query->where(function (QueryBuilder $builder) use ($keyword) {
                    $builder
                        ->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$keyword}%"])
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['customer', 'phone', 'status', 'source', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Customer>
     */
    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery()->select('customers.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('customer-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search customers...'),
                    'lengthMenu' => __('Show _MENU_ customers'),
                    'info' => __('Showing _START_ to _END_ of _TOTAL_ customers'),
                    'infoEmpty' => __('No customers available'),
                    'zeroRecords' => __('No matching customers found'),
                    'paginate' => [
                        'previous' => __('Previous'),
                        'next' => __('Next'),
                    ],
                ],
                'dom' => "<'customer-table-toolbar flex flex-col gap-4 border-b border-slate-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between'<'flex flex-col gap-4 sm:flex-row sm:items-center'lf><'text-sm text-slate-500'i>>" .
                    "<'overflow-x-auto'tr>" .
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
            ->orderBy(5, 'desc')
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

            Column::computed('customer')
                ->title(__('Customer'))
                ->searchable(true)
                ->orderable(false)
                ->addClass('min-w-[280px]'),
            Column::make('phone')->title(__('Phone'))->addClass('whitespace-nowrap'),
            Column::make('status')->title(__('Status')),
            Column::make('source')->title(__('Source')),
            Column::make('created_at')->title(__('Created'))->render("data ? new Date(data).toLocaleDateString() : 'N/A'"),

            Column::computed('action')
                ->title(__('Actions'))
                ->searchable(false)
                ->orderable(false)
                ->width(160)
                ->addClass('text-right'),
        ];
    }


    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Customer_' . date('YmdHis');
    }
}
