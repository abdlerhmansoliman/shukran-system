<?php

namespace App\DataTables;

use App\Models\Package;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PackageDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Package>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('name', function (Package $package) {
                return '<span class="font-semibold text-slate-900">'.e($package->name).'</span>';
            })
            ->editColumn('levels_count', function (Package $package) {
                return '<span class="font-medium text-slate-700">'.e($package->levels_count).'</span>';
            })
            ->editColumn('price', function (Package $package) {
                return '<span class="font-semibold text-slate-900">'.e(number_format((float) $package->price, 2)).'</span>';
            })
            ->editColumn('status', function (Package $package) {
                $classes = $package->status === 'active'
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$classes.'">'.e(__(Str::headline($package->status))).'</span>';
            })
            ->editColumn('customer_packages_count', function (Package $package) {
                return '<span class="font-medium text-slate-700">'.e($package->customer_packages_count).'</span>';
            })
            ->addColumn('action', function (Package $package) {
                return view('components.package-datatable-actions', compact('package'))->render();
            })
            ->rawColumns(['name', 'levels_count', 'price', 'status', 'customer_packages_count', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Package>
     */
    public function query(Package $model): QueryBuilder
    {
        return $model->newQuery()
            ->withCount('customerPackages')
            ->select('packages.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('package-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search packages...'),
                    'lengthMenu' => __('Show _MENU_ packages'),
                    'info' => __('Showing _START_ to _END_ of _TOTAL_ packages'),
                    'infoEmpty' => __('No packages available'),
                    'zeroRecords' => __('No matching packages found'),
                    'paginate' => [
                        'previous' => __('Previous'),
                        'next' => __('Next'),
                    ],
                ],
                'dom' => "<'package-table-toolbar flex flex-col gap-4 border-b border-slate-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between'<'flex flex-col gap-4 sm:flex-row sm:items-center'lf><'text-sm text-slate-500'i>>".
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
            ->orderBy(6, 'desc');
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

            Column::make('name')->title(__('Package'))->addClass('min-w-[260px]'),
            Column::make('levels_count')->title(__('Levels Count'))->addClass('whitespace-nowrap'),
            Column::make('price')->title(__('Price'))->addClass('whitespace-nowrap'),
            Column::make('status')->title(__('Status')),
            Column::make('customer_packages_count')
                ->title(__('Assigned Customers'))
                ->searchable(false)
                ->addClass('whitespace-nowrap'),
            Column::make('created_at')->title(__('Created'))->addClass('whitespace-nowrap min-w-[160px]')->render("data ? new Date(data).toLocaleDateString() : 'N/A'"),

            Column::computed('action')
                ->title(__('Actions'))
                ->searchable(false)
                ->orderable(false)
                ->width(140)
                ->addClass('text-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Package_'.date('YmdHis');
    }
}
