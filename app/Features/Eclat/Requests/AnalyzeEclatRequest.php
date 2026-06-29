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
            'filter_type' => ['nullable', 'string', 'in:all,range'],
            'date_from' => ['nullable', 'required_if:filter_type,range', 'date'],
            'date_to' => ['nullable', 'required_if:filter_type,range', 'date', 'after_or_equal:date_from'],
        ];
    }
}
