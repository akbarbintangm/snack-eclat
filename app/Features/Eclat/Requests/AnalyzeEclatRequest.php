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
            'filter_type' => ['nullable', 'string', 'in:all,date,month,year'],
            'date' => ['nullable', 'required_if:filter_type,date', 'date'],
            'month' => ['nullable', 'required_if:filter_type,month', 'date_format:Y-m'],
            'year' => ['nullable', 'required_if:filter_type,year', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}
