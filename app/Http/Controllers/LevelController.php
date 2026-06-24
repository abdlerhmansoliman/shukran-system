<?php

namespace App\Http\Controllers;

use App\DataTables\LevelDataTable;
use App\Http\Requests\LevelStoreRequest;
use App\Http\Requests\LevelUpdateRequest;
use App\Models\Level;
use Illuminate\Support\Facades\Gate;

class LevelController extends Controller
{
    public function index(LevelDataTable $datatable)
    {
        Gate::authorize('view levels');

        return $datatable->render('levels.index');
    }

    public function create()
    {
        Gate::authorize('create levels');

        return view('levels.create');
    }

    public function store(LevelStoreRequest $request)
    {
        Gate::authorize('create levels');
        $level = Level::query()->create($request->levelData());

        return redirect()
            ->route('levels.edit', $level)
            ->with('success', __('Level created successfully.'));
    }

    public function edit(Level $level)
    {
        Gate::authorize('edit levels');

        return view('levels.edit', compact('level'));
    }

    public function update(LevelUpdateRequest $request, Level $level)
    {
        Gate::authorize('edit levels');
        $level->update($request->levelData());

        return redirect()
            ->route('levels.edit', $level)
            ->with('success', __('Level updated successfully.'));
    }

    public function destroy(Level $level)
    {
        Gate::authorize('delete levels');
        if ($level->customers()->exists()) {
            return redirect()
                ->route('levels.index')
                ->with('error', __('Level cannot be deleted while it is assigned to customers.'));
        }

        $level->delete();

        return redirect()
            ->route('levels.index')
            ->with('success', __('Level deleted successfully.'));
    }
}
