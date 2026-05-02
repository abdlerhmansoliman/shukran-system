<?php

namespace App\DataTables;

use App\Models\Level;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LevelDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Level>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('name', function (Level $level) {
                return '<span class="font-semibold text-slate-900">'.e($level->name).'</span>';
            })
            ->editColumn('customers_count', function (Level $level) {
                return '<span class="font-medium text-slate-700">'.e($level->customers_count).'</span>';
            })
            ->addColumn('action', function (Level $level) {
                return view('components.level-datatable-actions', compact('level'))->render();
            })
            ->rawColumns(['name', 'customers_count', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Level>
     */
    public function query(Level $model): QueryBuilder
    {
        return $model->newQuery()
            ->withCount('customers')
            ->select('levels.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('level-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search levels...'),
                    'lengthMenu' => __('Show _MENU_ levels'),
                    'info' => __('Showing _START_ to _END_ of _TOTAL_ levels'),
                    'infoEmpty' => __('No levels available'),
                    'zeroRecords' => __('No matching levels found'),
                    'paginate' => [
                        'previous' => __('Previous'),
                        'next' => __('Next'),
                    ],
                ],
                'dom' => "<'level-table-toolbar flex flex-col gap-4 border-b border-slate-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between'<'flex flex-col gap-4 sm:flex-row sm:items-center'lf><'text-sm text-slate-500'i>>".
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
            ->orderBy(3, 'desc');
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

            Column::make('name')->title(__('Level'))->addClass('min-w-[260px]'),
            Column::make('customers_count')
                ->title(__('Customers Count'))
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
        return 'Level_'.date('YmdHis');
    }
}
