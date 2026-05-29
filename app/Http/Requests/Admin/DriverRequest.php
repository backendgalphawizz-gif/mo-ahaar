<?php

namespace App\Http\Requests\Admin;

use App\Models\DriverProfile;
use App\Support\DriverProfileValidator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $digits = preg_replace('/\D/', '', (string) $this->input('mobile'));
            $this->merge(['mobile' => $digits !== '' ? substr($digits, 0, 10) : '']);
        }

        if ($this->has('ifsc_code')) {
            $this->merge(['ifsc_code' => strtoupper(trim((string) $this->input('ifsc_code')))]);
        }

        if ($this->filled('account_type')) {
            $type = strtolower(trim((string) $this->input('account_type')));
            if ($type === 'saving') {
                $type = 'savings';
            }
            $this->merge(['account_type' => $type]);
        }

        if ($this->filled('document_type')) {
            $this->merge(['document_type' => strtolower(trim((string) $this->input('document_type')))]);
        }
    }

    public function rules(): array
    {
        $driverId = $this->route('id');
        $isCreate = $this->isMethod('post') && $this->routeIs('admin.delivery.store');

        if ($isCreate) {
            return DriverProfileValidator::adminCreateRules($driverId ? (int) $driverId : null);
        }

        $profile = $driverId
            ? DriverProfile::where('driver_id', $driverId)->first()
            : null;

        return DriverProfileValidator::adminUpdateRules((int) $driverId, $profile);
    }

    public function messages(): array
    {
        return DriverProfileValidator::messages();
    }

    protected function failedValidation(Validator $validator): void
    {
        $tab = DriverProfileValidator::tabForFirstError(array_keys($validator->errors()->toArray()));

        throw new HttpResponseException(
            redirect()
                ->back()
                ->withInput(array_merge($this->all(), ['driver_tab' => $tab]))
                ->withErrors($validator)
        );
    }
}
