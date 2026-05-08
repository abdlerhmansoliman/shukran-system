<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\CustomerPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerPaymentStoreRequest extends FormRequest
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
        $customer = $this->route('customer');

        return [
            'customer_package_id' => [
                'required',
                Rule::exists('customer_packages', 'id')->where(function ($query) use ($customer) {
                    if ($customer instanceof Customer) {
                        $query->where('customer_id', $customer->id);
                    }

                    $query->where('status', 'active');
                }),
            ],
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customerPackage = $this->customerPackage();

            if (! $customerPackage) {
                return;
            }

            $remainingAmount = (float) $customerPackage->remaining_amount;
            $amount = (float) $this->input('amount', 0);

            if ($remainingAmount <= 0) {
                $validator->errors()->add('customer_package_id', __('This subscription is already fully paid.'));
            }

            if ($amount > $remainingAmount) {
                $validator->errors()->add('amount', __('The payment amount cannot be greater than the remaining subscription balance.'));
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentData(): array
    {
        $validated = $this->validated();

        return [
            'customer_package_id' => $validated['customer_package_id'],
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

    public function customerPackageId(): int
    {
        return (int) $this->validated('customer_package_id');
    }

    private function customerPackage(): ?CustomerPackage
    {
        $customer = $this->route('customer');

        if (! $customer instanceof Customer) {
            return null;
        }

        return CustomerPackage::query()
            ->where('customer_id', $customer->id)
            ->whereKey($this->input('customer_package_id'))
            ->first();
    }
}
