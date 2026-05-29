<?php

namespace App\Http\Requests\Admin;

use App\Models\DriverProfile;
use App\Support\DriverProfileValidator;
use Illuminate\Foundation\Http\FormRequest;

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
    }

    public function rules(): array
    {
        $driverId = $this->route('id');
        $isCreate = $this->isMethod('post') && $this->routeIs('admin.delivery.store');

        if ($isCreate) {
            return DriverProfileValidator::adminCreateRules($driverId);
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
}
