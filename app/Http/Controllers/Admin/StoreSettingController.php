<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StoreSettingController extends Controller
{
    private function normalizeInputs(Request $request, array $fields, bool $emptyToNull = false): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            if (!$request->exists($field)) {
                continue;
            }

            $value = $request->input($field);
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            $normalized[$field] = $emptyToNull && $value === '' ? null : $value;
        }

        if (!empty($normalized)) {
            $request->merge($normalized);
        }
    }

    private function normalizeStoreSettingInputs(Request $request): void
    {
        $this->normalizeInputs($request, [
            'site_title',
            'app_name',
            'support_number',
            'support_email',
            'address',
            'address',
        ], true);
    }

    public function edit()
    {
        $title = 'Store Settings';
        $setting = StoreSetting::first();

        return view('admin.settings.store', compact('title', 'setting'));
    }

    public function update(Request $request)
    {
        $this->normalizeStoreSettingInputs($request);

        $setting = StoreSetting::first();

        $validated = $request->validate(array_merge([
            'site_title' => 'required|string|min:2|max:150',
            'app_name' => 'required|string|min:2|max:150',
            'support_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'support_email' => 'required|email:rfc|max:150',
            'address' => 'required|string|min:5|max:1000',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg,ico|max:2048',
            'favicon' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg,ico|max:1024',
        ], $this->customerAppHomeValidationRules(), $this->customerRegistrationPageValidationRules()), [
            'site_title.required' => 'Site Title is required.',
            'site_title.min' => 'Site Title must be at least 2 characters.',
            'site_title.max' => 'Site Title must not exceed 150 characters.',

            'app_name.required' => 'App Name is required.',
            'app_name.min' => 'App Name must be at least 2 characters.',
            'app_name.max' => 'App Name must not exceed 150 characters.',

            'support_number.required' => 'Support Number is required.',
            'support_number.regex' => 'Support Number must be exactly 10 digits.',

            'support_email.required' => 'Support Email is required.',
            'support_email.email' => 'Support Email must be a valid email address.',
            'support_email.max' => 'Support Email must not exceed 150 characters.',

            'address.required' => 'Address is required.',
            'address.min' => 'Address must be at least 5 characters.',
            'address.max' => 'Address must not exceed 1000 characters.',

            'logo.mimes' => 'Logo must be JPG, JPEG, PNG, WEBP, SVG, or ICO format.',
            'logo.max' => 'Logo size must not exceed 2MB.',

            'favicon.mimes' => 'Favicon must be JPG, JPEG, PNG, WEBP, SVG, or ICO format.',
            'favicon.max' => 'Favicon size must not exceed 1MB.',
        ]);

        foreach (array_merge($this->customerAppHomeSettingKeys(), $this->customerRegistrationPageSettingKeys()) as $key) {
            if (Schema::hasColumn('store_settings', $key) && array_key_exists($key, $validated)) {
                $validated[$key] = (bool) ((int) $validated[$key]);
            }
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logoName = time() . '_logo_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/settings'), $logoName);
            $validated['logo'] = $logoName;
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $faviconName = time() . '_favicon_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/settings'), $faviconName);
            $validated['favicon'] = $faviconName;
        }

        StoreSetting::updateOrCreate(
            ['id' => $setting->id ?? 1],
            $validated
        );

        return redirect()->route('admin.settings.store.edit')->with('success', 'Store settings updated successfully.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function customerAppHomeValidationRules(): array
    {
        if (!Schema::hasTable('store_settings')
            || !Schema::hasColumn('store_settings', 'customer_home_sliders_enabled')) {
            return [];
        }

        $rules = [];
        foreach ($this->customerAppHomeSettingKeys() as $key) {
            if (Schema::hasColumn('store_settings', $key)) {
                $rules[$key] = 'required|in:0,1';
            }
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    private function customerAppHomeSettingKeys(): array
    {
        return [
            'customer_home_sliders_enabled',
            'customer_home_offers_enabled',
            'customer_home_promotions_enabled',
            'customer_home_announcements_enabled',
            'customer_home_featured_products_enabled',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function customerRegistrationPageValidationRules(): array
    {
        if (!Schema::hasTable('store_settings')
            || !Schema::hasColumn('store_settings', 'customer_registration_privacy_policy_enabled')) {
            return [];
        }

        $rules = [];
        foreach ($this->customerRegistrationPageSettingKeys() as $key) {
            if (Schema::hasColumn('store_settings', $key)) {
                $rules[$key] = 'required|in:0,1';
            }
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    private function customerRegistrationPageSettingKeys(): array
    {
        return [
            'customer_registration_privacy_policy_enabled',
            'customer_registration_terms_enabled',
            'customer_registration_faq_enabled',
        ];
    }
}
