<?php

namespace App\Features\Transactions\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_no' => ['sometimes', 'nullable', 'string', 'max:50', Rule::unique('transactions', 'reference_no')->ignore($this->route('transaction'))],
            'transaction_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'string', 'max:30'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.snack_id' => ['required_with:items', 'integer', 'exists:snacks,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
