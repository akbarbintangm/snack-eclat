<?php

namespace App\Features\Snacks\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSnackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('snacks', 'name')->ignore($this->route('snack'))],
            'status' => ['sometimes', 'required', 'string', 'max:30'],
        ];
    }
}
