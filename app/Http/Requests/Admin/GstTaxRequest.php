<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GstTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('gst_tax'); // used for unique check on edit

        return [
            'name'       => ['required', 'string', 'max:100', Rule::unique('gst_taxes', 'name')->ignore($id)],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d{1,3}(\.\d{1,2})?$/'],
            'status'     => ['required', Rule::in(['0', '1'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'GST name is required.',
            'name.unique'          => 'A GST slab with this name already exists.',
            'percentage.required'  => 'Percentage is required.',
            'percentage.numeric'   => 'Percentage must be a number.',
            'percentage.min'       => 'Percentage must be at least 0.',
            'percentage.max'       => 'Percentage must not exceed 100.',
            'percentage.regex'     => 'Percentage format is invalid (max 2 decimal places).',
            'status.required'      => 'Status is required.',
            'status.in'            => 'Status must be active or inactive.',
        ];
    }
}
