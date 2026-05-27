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

        return DriverProfileValidator::applyPucImageRule(
            DriverProfileValidator::adminUpdateRules((int) $driverId, $profile),
            $profile
        );
    }

    public function messages(): array
    {
        return DriverProfileValidator::messages();
    }
}
