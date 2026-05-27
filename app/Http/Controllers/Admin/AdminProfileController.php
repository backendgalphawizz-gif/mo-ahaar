<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class AdminProfileController extends Controller
{
    public function edit()
    {
        $userId = session('user_id');
        $admin = Users::where('user_id', $userId)->where('role_type', 1)->firstOrFail();

        return view('admin.settings.profile', compact('admin'));
    }

    public function update(Request $request)
    {
        $userId = session('user_id');
        $admin = Users::where('user_id', $userId)->where('role_type', 1)->firstOrFail();

        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
        ];

        if (Schema::hasColumn('users', 'profile_image')) {
            $rules['profile_image'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048';
        }

        if (Schema::hasColumn('users', 'mobile')) {
            $rules['mobile'] = 'nullable|digits:10|unique:users,mobile,' . $admin->user_id . ',user_id';
        }

        $rules['current_password'] = 'nullable|string';
        $rules['new_password'] = ['nullable', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/', 'confirmed'];

        $validated = $request->validate($rules, [
            'name.regex' => 'Name may only contain letters and spaces.',
            'profile_image.image' => 'Please upload a valid image file for profile photo.',
            'profile_image.mimes' => 'Profile photo must be a JPG, JPEG, PNG, or WEBP file.',
            'profile_image.max' => 'Profile photo size must not exceed 2 MB.',
            'new_password.regex' => 'New password must include uppercase, lowercase, number, and special character.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        $updateData = [
            'name' => $validated['name'],
        ];

        if (Schema::hasColumn('users', 'mobile')) {
            $updateData['mobile'] = $validated['mobile'] ?? null;
        }

        if (Schema::hasColumn('users', 'profile_image') && $request->hasFile('profile_image')) {
            $uploadPath = public_path('uploads/admins');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            if (!empty($admin->profile_image) && File::exists($uploadPath . DIRECTORY_SEPARATOR . $admin->profile_image)) {
                File::delete($uploadPath . DIRECTORY_SEPARATOR . $admin->profile_image);
            }

            $file = $request->file('profile_image');
            $imageName = time() . '_admin_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move($uploadPath, $imageName);
            $updateData['profile_image'] = $imageName;
        }

        if (!empty($validated['new_password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }

            $updateData['password'] = Hash::make($validated['new_password']);
        }

        Users::where('user_id', $admin->user_id)->update($updateData);

        $sessionPayload = ['name' => $updateData['name']];
        if (array_key_exists('profile_image', $updateData)) {
            $sessionPayload['profile_image'] = $updateData['profile_image'];
        }
        session($sessionPayload);

        return redirect()->route('admin.profile.edit')->with('success', 'Profile updated successfully.');
    }
}
