<?php

namespace App\Http\Controllers;

use App\DataTables\ProgramDataTable;
use App\Http\Requests\ProgramStoreRequest;
use App\Http\Requests\ProgramUpdateRequest;
use App\Models\Program;
use Illuminate\Support\Facades\Gate;

class ProgramController extends Controller
{
    public function index(ProgramDataTable $datatable)
    {
        Gate::authorize('view programs');
        return $datatable->render('programs.index');
    }

    public function create()
    {
        Gate::authorize('create programs');
        return view('programs.create');
    }

    public function store(ProgramStoreRequest $request)
    {
        Gate::authorize('create programs');
        $program = Program::query()->create($request->programData());

        return redirect()
            ->route('programs.edit', $program)
            ->with('success', __('Program created successfully.'));
    }

    public function edit(Program $program)
    {
        Gate::authorize('edit programs');
        return view('programs.edit', compact('program'));
    }

    public function update(ProgramUpdateRequest $request, Program $program)
    {
        Gate::authorize('edit programs');
        $program->update($request->programData());

        return redirect()
            ->route('programs.edit', $program)
            ->with('success', __('Program updated successfully.'));
    }

    public function destroy(Program $program)
    {
        Gate::authorize('delete programs');
        if ($program->packages()->exists()) {
            return redirect()
                ->route('programs.index')
                ->with('error', __('Program cannot be deleted while it is assigned to packages.'));
        }

        $program->delete();

        return redirect()
            ->route('programs.index')
            ->with('success', __('Program deleted successfully.'));
    }
}
