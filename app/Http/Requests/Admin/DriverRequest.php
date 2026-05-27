<?php

namespace App\Http\Requests\Admin;

use App\Models\Users;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
                Rule::unique('users', 'mobile')
                    ->where(fn ($query) => $query->where('role_type', Users::DRIVER_APP_ROLE_TYPE))
                    ->ignore($driverId, 'user_id'),
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->where(fn ($query) => $query->where('role_type', Users::DRIVER_APP_ROLE_TYPE))
                    ->ignore($driverId, 'user_id'),
            ],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'vehicle_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'driving_license_number' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'vehicle_type' => ['nullable', 'string', 'max:50', Rule::in(['bike', 'scooter', 'car', 'van', 'truck', 'other', 'Bike', 'Scooter', 'Car', 'Van', 'Truck', 'Other'])],
            'account_holder_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:150'],
            'branch_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'ifsc_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'account_type' => ['required', 'string', Rule::in(['savings', 'current', 'Savings', 'Current'])],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'status' => ['nullable', Rule::in(['0', '1', 0, 1])],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'driving_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'aadhar_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];

        if ($isCreate) {
            $rules['password'] = ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).+$/', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).+$/', 'confirmed'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'account_number.regex' => 'Account number must contain digits only.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code.',
            'vehicle_number.regex' => 'Vehicle number format is invalid.',
            'driving_license_number.regex' => 'Driving license number format is invalid.',
            'password.regex' => 'Password must include uppercase, lowercase, number and special character.',
            'driving_license.mimes' => 'Driving license must be JPG, PNG, WEBP, or PDF.',
            'aadhar_card.mimes' => 'Aadhar card must be JPG, PNG, WEBP, or PDF.',
        ];
    }
}
