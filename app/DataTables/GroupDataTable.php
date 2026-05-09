<?php

namespace App\DataTables;

use App\Enums\GroupEnrollmentStatus;
use App\Models\Group;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GroupDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Group>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('group', function (Group $group) {
                return '
                    <div>
                        <a href="'.e(route('groups.show', $group)).'" class="font-semibold text-slate-900">
                            '.e($group->name).'
                        </a>
                    </div>
                ';
            })
            ->addColumn('instructor', function (Group $group) {
                return $group->instructor
                    ? '<span class="font-medium text-slate-700">'.e($group->instructor->name).'</span>'
                    : '<span class="text-sm text-slate-400">'.e(__('Not specified')).'</span>';
            })
            ->addColumn('schedule', function (Group $group) {
                $days = collect($group->days_of_week ?? [])
                    ->map(fn (string $day) => __(Str::headline($day)))
                    ->implode(', ');
                $time = collect([
                    $group->start_time ? substr((string) $group->start_time, 0, 5) : null,
                    $group->end_time ? substr((string) $group->end_time, 0, 5) : null,
                ])->filter()->implode(' - ');

                if (! $days && ! $time) {
                    return '<span class="text-sm text-slate-400">'.e(__('Not scheduled')).'</span>';
                }

                return '
                    <div class="text-sm text-slate-700">
                        <div class="font-medium">'.e($days ?: __('Days not specified')).'</div>
                        <div class="mt-1 text-slate-500">'.e($time ?: __('Time not specified')).'</div>
                    </div>
                ';
            })
            ->editColumn('status', function (Group $group) {
                $classes = match ($group->status) {
                    'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                    'completed' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                    'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                    default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                };

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$classes.'">'.e(__(Str::headline($group->status))).'</span>';
            })
            ->editColumn('active_enrollments_count', function (Group $group) {
                $capacity = $group->capacity ? ' / '.$group->capacity : '';

                return '<span class="font-medium text-slate-700">'.e($group->active_enrollments_count.$capacity).'</span>';
            })
            ->addColumn('action', function (Group $group) {
                return view('components.group-datatable-actions', compact('group'))->render();
            })
            ->filterColumn('group', function (QueryBuilder $query, string $keyword) {
                $query->where(function (QueryBuilder $builder) use ($keyword) {
                    $builder->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['group', 'instructor', 'schedule', 'status', 'active_enrollments_count', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Group>
     */
    public function query(Group $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['instructor'])
            ->withCount([
                'groupEnrollments as active_enrollments_count' => fn (QueryBuilder $query) => $query->whereIn('status', GroupEnrollmentStatus::reservedValues()),
            ])
            ->select('groups.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('group-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search groups...'),
                    'lengthMenu' => __('Show _MENU_ groups'),
                    'info' => __('Showing _START_ to _END_ of _TOTAL_ groups'),
                    'infoEmpty' => __('No groups available'),
                    'zeroRecords' => __('No matching groups found'),
                    'paginate' => [
                        'previous' => __('Previous'),
                        'next' => __('Next'),
                    ],
                ],
                'dom' => "<'group-table-toolbar flex flex-col gap-4 border-b border-slate-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between'<'flex flex-col gap-4 sm:flex-row sm:items-center'lf><'text-sm text-slate-500'i>>".
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

            Column::computed('group')
                ->title(__('Group'))
                ->searchable(true)
                ->orderable(false)
                ->addClass('min-w-[220px]'),
            Column::computed('instructor')->title(__('Instructor'))->searchable(false)->orderable(false)->addClass('min-w-[180px]'),
            Column::computed('schedule')->title(__('Schedule'))->searchable(false)->orderable(false)->addClass('min-w-[220px]'),
            Column::make('status')->title(__('Status')),
            Column::make('active_enrollments_count')
                ->title(__('Students'))
                ->searchable(false)
                ->addClass('whitespace-nowrap'),
            Column::make('created_at')->title(__('Created'))->addClass('whitespace-nowrap min-w-[160px]')->render("data ? new Date(data).toLocaleDateString() : 'N/A'"),

            Column::computed('action')
                ->title(__('Actions'))
                ->searchable(false)
                ->orderable(false)
                ->width(190)
                ->addClass('text-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Group_'.date('YmdHis');
    }
}
