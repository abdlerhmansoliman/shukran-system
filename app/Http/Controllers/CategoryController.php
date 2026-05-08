<?php

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(CategoryDataTable $datatable)
    {
        return $datatable->render('categories.index');
    }

    public function create()
    {
        return view('categories.create', $this->formData());
    }

    public function store(CategoryStoreRequest $request)
    {
        $category = Category::query()->create($request->categoryData());

        return redirect()
            ->route('categories.edit', $category)
            ->with('success', __('Category created successfully.'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', [
            'category' => $category,
            ...$this->formData($category),
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $category->update($request->categoryData());

        return redirect()
            ->route('categories.edit', $category)
            ->with('success', __('Category updated successfully.'));
    }

    public function destroy(Category $category)
    {
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
                ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                ->with('parent')
                ->orderBy('name')
                ->get(),
        ];
    }
}
