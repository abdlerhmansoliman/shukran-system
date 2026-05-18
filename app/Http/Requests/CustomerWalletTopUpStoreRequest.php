<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerWalletTopUpStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'paid_at' => $this->input('paid_at', now()->toDateString()),
            'status' => $this->input('status', 'completed'),
        ]);
    }

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
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'paid_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id')->where('status', 'active'),
            ],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentData(): array
    {
        $validated = $this->validated();

        return [
            'amount' => round((float) $validated['amount'], 2),
            'status' => $validated['status'],
            'payment_method_id' => $validated['payment_method_id'],
            'reference' => $validated['reference'] ?? null,
            'paid_at' => $validated['paid_at'],
            'notes' => $validated['notes'] ?? null,
            'direction' => 'incoming',
            'created_by' => $this->user()?->id,
        ];
    }
}
