<?php

namespace App\DataTables;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Category>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('name', function (Category $category) {
                return '<span class="font-semibold text-slate-900">'.e($category->name).'</span>';
            })
            ->addColumn('parent', function (Category $category) {
                return $category->parent
                    ? '<span class="font-medium text-slate-700">'.e($category->parent->name).'</span>'
                    : '<span class="text-sm text-slate-400">'.e(__('Root category')).'</span>';
            })
            ->editColumn('children_count', function (Category $category) {
                return '<span class="font-medium text-slate-700">'.e($category->children_count).'</span>';
            })
            ->editColumn('customers_count', function (Category $category) {
                return '<span class="font-medium text-slate-700">'.e($category->customers_count).'</span>';
            })
            ->editColumn('groups_count', function (Category $category) {
                return '<span class="font-medium text-slate-700">'.e($category->groups_count).'</span>';
            })
            ->addColumn('action', function (Category $category) {
                return view('components.category-datatable-actions', compact('category'))->render();
            })
            ->filterColumn('parent', function (QueryBuilder $query, string $keyword) {
                $query->whereHas('parent', function (QueryBuilder $builder) use ($keyword) {
                    $builder->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['name', 'parent', 'children_count', 'customers_count', 'groups_count', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Category>
     */
    public function query(Category $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('parent')
            ->withCount(['children', 'customers', 'groups'])
            ->select('categories.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('category-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search categories...'),
                    'lengthMenu' => __('Show _MENU_ categories'),
                    'info' => __('Showing _START_ to _END_ of _TOTAL_ categories'),
                    'infoEmpty' => __('No categories available'),
                    'zeroRecords' => __('No matching categories found'),
                    'paginate' => [
                        'previous' => __('Previous'),
                        'next' => __('Next'),
                    ],
                ],
                'dom' => "<'category-table-toolbar flex flex-col gap-4 border-b border-slate-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between'<'flex flex-col gap-4 sm:flex-row sm:items-center'lf><'text-sm text-slate-500'i>>".
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

            Column::make('name')->title(__('Category'))->addClass('min-w-[240px]'),
            Column::computed('parent')
                ->title(__('Parent Category'))
                ->searchable(true)
                ->orderable(false)
                ->addClass('min-w-[220px]'),
            Column::make('children_count')
                ->title(__('Child Categories'))
                ->searchable(false)
                ->addClass('whitespace-nowrap'),
            Column::make('customers_count')
                ->title(__('Customers Count'))
                ->searchable(false)
                ->addClass('whitespace-nowrap'),
            Column::make('groups_count')
                ->title(__('Groups Count'))
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
        return 'Category_'.date('YmdHis');
    }
}
