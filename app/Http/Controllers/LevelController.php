<?php

namespace App\Http\Controllers;

use App\DataTables\LevelDataTable;
use App\Http\Requests\LevelStoreRequest;
use App\Http\Requests\LevelUpdateRequest;
use App\Models\Level;

class LevelController extends Controller
{
    public function index(LevelDataTable $datatable)
    {
        return $datatable->render('levels.index');
    }

    public function create()
    {
        return view('levels.create');
    }

    public function store(LevelStoreRequest $request)
    {
        $level = Level::query()->create($request->levelData());

        return redirect()
            ->route('levels.edit', $level)
            ->with('success', __('Level created successfully.'));
    }

    public function edit(Level $level)
    {
        return view('levels.edit', compact('level'));
    }

    public function update(LevelUpdateRequest $request, Level $level)
    {
        $level->update($request->levelData());

        return redirect()
            ->route('levels.edit', $level)
            ->with('success', __('Level updated successfully.'));
    }

    public function destroy(Level $level)
    {
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
