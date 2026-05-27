<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCustomerLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys((array) config('app.supported_locales', ['en' => 'English', 'hi' => 'Hindi']));
        $locale = null;

        $explicitLocale = $request->header('X-Language')
            ?? $request->get('lang')
            ?? $this->resolveFromAcceptLanguage($request->header('Accept-Language'));

        if (is_string($explicitLocale) && $explicitLocale !== '') {
            $locale = $this->normalizeLocale($explicitLocale);
        }

        if ($locale === null) {
            $user = $request->user();
            if ($user && !empty($user->preferred_language)) {
                $locale = $this->normalizeLocale((string) $user->preferred_language);
            }
        }

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = (string) config('app.locale', 'en');
        }

        app()->setLocale($locale);
        $request->attributes->set('resolved_locale', $locale);

        return $next($request);
    }

    private function resolveFromAcceptLanguage(?string $header): ?string
    {
        if (!$header) {
            return null;
        }

        $first = trim(explode(',', $header)[0] ?? '');

        return $first !== '' ? $first : null;
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(trim($locale));

        return match (true) {
            str_starts_with($locale, 'hi') => 'hi',
            default => 'en',
        };
    }
}