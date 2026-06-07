<?php

namespace App\Features\Eclat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeEclatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_support' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_confidence' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
