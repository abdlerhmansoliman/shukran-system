<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryStoreRequest extends FormRequest
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
        return [
            'parent_name' => ['nullable', 'string', 'max:255'],
            'child_name' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->whereNull('parent_id')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentName = trim((string) $this->input('parent_name'));
            $childName = trim((string) $this->input('child_name'));

            if ($parentName === '' && $childName === '') {
                $validator->errors()->add('parent_name', __('Enter a parent category name or child category name.'));

                return;
            }

            if ($childName !== '' && $parentName === '' && ! $this->filled('parent_id')) {
                $validator->errors()->add('parent_id', __('Choose a parent category before creating a child category.'));
            }
        });
    }

    public function parentName(): ?string
    {
        $name = trim((string) $this->validated('parent_name'));

        return $name !== '' ? $name : null;
    }

    public function childName(): ?string
    {
        $name = trim((string) $this->validated('child_name'));

        return $name !== '' ? $name : null;
    }

    public function parentId(): ?int
    {
        $parentId = $this->validated('parent_id');

        return $parentId ? (int) $parentId : null;
    }
}
