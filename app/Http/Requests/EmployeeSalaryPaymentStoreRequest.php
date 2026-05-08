<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeSalaryPaymentStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'paid_at' => $this->input('paid_at', now()->toDateString()),
            'status' => $this->input('status', 'completed'),
            'bonus_amount' => $this->input('bonus_amount', 0),
            'deduction_amount' => $this->input('deduction_amount', 0),
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
            'bonus_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'deduction_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'paid_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $baseAmount = (float) $this->input('amount', 0);
            $bonusAmount = (float) $this->input('bonus_amount', 0);
            $deductionAmount = (float) $this->input('deduction_amount', 0);

            if (($baseAmount + $bonusAmount - $deductionAmount) <= 0) {
                $validator->errors()->add('deduction_amount', __('The final salary payment amount must be greater than zero.'));
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentData(): array
    {
        $validated = $this->validated();
        $amount = (float) $validated['amount'];
        $bonusAmount = (float) ($validated['bonus_amount'] ?? 0);
        $deductionAmount = (float) ($validated['deduction_amount'] ?? 0);

        return [
            'amount' => round($amount + $bonusAmount - $deductionAmount, 2),
            'status' => $validated['status'],
            'payment_method_id' => $validated['payment_method_id'],
            'reference' => $validated['reference'] ?? null,
            'paid_at' => $validated['paid_at'],
            'notes' => $validated['notes'] ?? null,
            'direction' => 'outgoing',
            'created_by' => $this->user()?->id,
        ];
    }

    public function bonusAmount(): float
    {
        return (float) ($this->validated()['bonus_amount'] ?? 0);
    }

    public function deductionAmount(): float
    {
        return (float) ($this->validated()['deduction_amount'] ?? 0);
    }
}
