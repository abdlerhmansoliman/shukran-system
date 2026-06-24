<?php

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(CategoryDataTable $datatable)
    {
        Gate::authorize('view categories');

        return $datatable->render('categories.index');
    }

    public function create()
    {
        Gate::authorize('create categories');

        return view('categories.create', $this->formData());
    }

    public function store(CategoryStoreRequest $request)
    {
        Gate::authorize('create categories');
        $category = DB::transaction(function () use ($request) {
            $parent = null;

            if ($parentName = $request->parentName()) {
                $parent = Category::query()->firstOrCreate([
                    'name' => $parentName,
                    'parent_id' => null,
                ]);
            }

            if (! $request->childName()) {
                return $parent;
            }

            $parent ??= Category::query()->parents()->findOrFail($request->parentId());

            $this->ensureCategoryNameIsUnique($request->childName(), $parent->id, 'child_name');

            return Category::query()->create([
                'name' => $request->childName(),
                'parent_id' => $parent->id,
            ]);
        });

        return redirect()
            ->route('categories.edit', $category)
            ->with('success', __('Category created successfully.'));
    }

    private function ensureCategoryNameIsUnique(string $name, ?int $parentId, string $field = 'name'): void
    {
        $exists = Category::query()
            ->where('name', $name)
            ->where('parent_id', $parentId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                $field => __('A category with this name already exists under the selected parent.'),
            ]);
        }
    }

    public function edit(Category $category)
    {
        Gate::authorize('edit categories');

        return view('categories.edit', [
            'category' => $category,
            ...$this->formData($category),
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        Gate::authorize('edit categories');
        $category->update($request->categoryData());

        return redirect()
            ->route('categories.edit', $category)
            ->with('success', __('Category updated successfully.'));
    }

    public function destroy(Category $category)
    {
        Gate::authorize('delete categories');
        if ($category->children()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', __('Category cannot be deleted while it has child categories.'));
        }

        if ($category->customers()->exists() || $category->groups()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', __('Category cannot be deleted while it is assigned to customers or groups.'));
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', __('Category deleted successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Category $category = null): array
    {
        return [
            'parentCategories' => Category::query()
                ->parents()
                ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                ->orderBy('name')
                ->get(),
        ];
    }
}
