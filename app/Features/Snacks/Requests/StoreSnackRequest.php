<?php

namespace App\Features\Snacks\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSnackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:snacks,name'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
