<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;

    public function supported(Request $request)
    {
        $supportedLocales = (array) config('app.supported_locales', [
            'en' => 'English',
            'hi' => 'Hindi',
        ]);

        $languages = collect($supportedLocales)->map(function ($label, $code) use ($request) {
            return [
                'code' => $code,
                'name' => $label,
                'is_selected' => $request->attributes->get('resolved_locale', config('app.locale')) === $code,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => __('api.languages_supported_fetched'),
            'data' => [
                'current_language' => $request->attributes->get('resolved_locale', config('app.locale')),
                'supported_languages' => $languages,
            ],
        ], 200);
    }

    public function current(Request $request)
    {
        $user = $this->resolveCustomer($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('api.unauthorized_customer_access'),
            ], 403);
        }

        $language = $user->preferred_language ?: $request->attributes->get('resolved_locale', config('app.locale'));

        return response()->json([
            'status' => true,
            'message' => __('api.current_language_fetched'),
            'data' => [
                'language' => $language,
            ],
        ], 200);
    }

    public function update(Request $request)
    {
        $user = $this->resolveCustomer($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('api.unauthorized_customer_access'),
            ], 403);
        }

        $validated = $request->validate([
            'language' => ['required', 'string', Rule::in(array_keys((array) config('app.supported_locales', ['en' => 'English', 'hi' => 'Hindi'])))],
        ]);

        $user->preferred_language = $validated['language'];
        $user->save();

        app()->setLocale($validated['language']);

        return response()->json([
            'status' => true,
            'message' => __('api.language_updated_successfully'),
            'data' => [
                'language' => $user->preferred_language,
            ],
        ], 200);
    }

    private function resolveCustomer(Request $request): ?Users
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return null;
        }

        return $user;
    }
}