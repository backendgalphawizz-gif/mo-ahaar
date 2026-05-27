<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:150'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'discount_type'    => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value'   => ['required', 'numeric', 'min:0.01'],
            'apply_to'         => ['required', Rule::in(['all', 'specific_products', 'specific_categories'])],
            'product_ids'      => ['nullable', 'array'],
            'product_ids.*'    => ['integer', 'min:1'],
            'category_ids'     => ['nullable', 'array'],
            'category_ids.*'   => ['integer', 'min:1'],
            'valid_from'       => ['nullable', 'date'],
            'valid_until'      => ['nullable', 'date', 'after_or_equal:valid_from'],
            'min_quantity'     => ['nullable', 'integer', 'min:1'],
            'max_quantity'     => ['nullable', 'integer', 'min:1', 'gte:min_quantity'],
            'min_cart_amount'  => ['nullable', 'numeric', 'min:0'],
            'max_cart_amount'  => ['nullable', 'numeric', 'min:0', 'gte:min_cart_amount'],
            'is_active'        => ['required', Rule::in(['0', '1'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'           => 'Offer title is required.',
            'discount_type.required'   => 'Discount type is required.',
            'discount_type.in'         => 'Discount type must be percentage or fixed.',
            'discount_value.required'  => 'Discount value is required.',
            'discount_value.min'       => 'Discount value must be greater than zero.',
            'apply_to.required'        => 'Apply-to scope is required.',
            'apply_to.in'              => 'Invalid apply-to scope.',
            'valid_until.after_or_equal' => 'Valid until must be on or after the start date.',
            'max_quantity.gte'         => 'Max quantity must be greater than or equal to min quantity.',
            'max_cart_amount.gte'      => 'Max cart amount must be greater than or equal to min cart amount.',
        ];
    }

    /**
     * Prepare data before validation – coerce empty strings to null for optional numerics/dates.
     */
    protected function prepareForValidation(): void
    {
        $nullables = ['valid_from', 'valid_until', 'min_quantity', 'max_quantity', 'min_cart_amount', 'max_cart_amount'];
        $patch = [];
        foreach ($nullables as $field) {
            if ($this->input($field) === '') {
                $patch[$field] = null;
            }
        }

        if (!empty($patch)) {
            $this->merge($patch);
        }

        // When apply_to is not specific_products/categories, clear the ID arrays
        if ($this->input('apply_to') === 'all') {
            $this->merge(['product_ids' => null, 'category_ids' => null]);
        } elseif ($this->input('apply_to') === 'specific_products') {
            $this->merge(['category_ids' => null]);
        } elseif ($this->input('apply_to') === 'specific_categories') {
            $this->merge(['product_ids' => null]);
        }
    }
}
