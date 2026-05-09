<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('parent_id', $this->input('parent_id')))
                    ->ignore($categoryId),
            ],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->whereNull('parent_id'), Rule::notIn([$categoryId])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $category = $this->route('category');

            if (! $category instanceof Category || ! $this->filled('parent_id')) {
                return;
            }

            $parent = Category::query()->find($this->input('parent_id'));

            while ($parent) {
                if ($parent->id === $category->id) {
                    $validator->errors()->add('parent_id', __('A category cannot use one of its child categories as a parent.'));

                    return;
                }

                $parent = $parent->parent;
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryData(): array
    {
        return $this->validated();
    }
}
