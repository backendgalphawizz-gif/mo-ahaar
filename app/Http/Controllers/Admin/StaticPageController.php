<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaticPageController extends Controller
{
    private array $allowedSlugs = ['privacy-policy', 'terms-and-conditions', 'faqs'];
    private array $allowedAudiences = ['user', 'driver'];

    public function index(Request $request)
    {
        $title = 'Static Pages';
        $selectedAudience = $request->query('audience', 'user');
        if (!in_array($selectedAudience, $this->allowedAudiences, true)) {
            $selectedAudience = 'user';
        }

        $pagesByAudience = [];
        foreach ($this->allowedAudiences as $audience) {
            $pagesByAudience[$audience] = [];
            foreach ($this->allowedSlugs as $baseSlug) {
                $pagesByAudience[$audience][$baseSlug] = $this->ensureAudiencePage($audience, $baseSlug);
            }
        }

        return view('admin.static-pages.index', compact('title', 'pagesByAudience', 'selectedAudience'));
    }

    public function edit($id)
    {
        $page = StaticPage::findOrFail($id);
        $title = 'Edit Static Page';
        return view('admin.static-pages.edit', compact('title', 'page'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['0', '1'])],
        ]);
        $page = StaticPage::findOrFail($id);
        $page->title = $validated['title'];
        $page->content = $validated['content'];
        $page->status = (int) $validated['status'];
        $page->save();
        return redirect()->route('admin.static-pages.index')->with('success', 'Static page updated successfully.');
    }

    public function saveByContext(Request $request)
    {
        $validated = $request->validate([
            'audience' => ['required', Rule::in($this->allowedAudiences)],
            'page_type' => ['required', Rule::in($this->allowedSlugs)],
            'title' => ['nullable', 'string', 'max:160'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['0', '1'])],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*.question' => ['nullable', 'string', 'max:500'],
            'faq_items.*.answer' => ['nullable', 'string', 'max:5000'],
        ]);

        $page = $this->ensureAudiencePage($validated['audience'], $validated['page_type']);
        $content = trim((string) ($validated['content'] ?? ''));

        if ($validated['page_type'] === 'faqs') {
            $content = $this->renderFaqHtml((array) ($validated['faq_items'] ?? []));
        }

        $page->title = $validated['title'] ?: $this->defaultTitle($validated['page_type']);
        $page->content = $content;
        $page->status = (int) $validated['status'];
        $page->save();

        return redirect()->route('admin.static-pages.index', ['audience' => $validated['audience']])
            ->with('success', 'Static page updated successfully.');
    }

    protected function ensureAudiencePage(string $audience, string $baseSlug): StaticPage
    {
        $scopedSlug = $audience . '-' . $baseSlug;

        $page = StaticPage::where('slug', $scopedSlug)->first();
        if ($page) {
            return $page;
        }

        $fallback = StaticPage::where('slug', $baseSlug)->first();

        return StaticPage::create([
            'slug' => $scopedSlug,
            'title' => $fallback?->title ?? $this->defaultTitle($baseSlug),
            'content' => $fallback?->content ?? '',
            'status' => (int) ($fallback?->status ?? 1),
        ]);
    }

    private function defaultTitle(string $baseSlug): string
    {
        return match ($baseSlug) {
            'privacy-policy' => 'Privacy Policy',
            'terms-and-conditions' => 'Terms & Conditions',
            default => 'FAQs',
        };
    }

    /**
     * @param array<int, array{question?: string, answer?: string}> $items
     */
    private function renderFaqHtml(array $items): string
    {
        $segments = [];
        foreach ($items as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));
            if ($question === '' && $answer === '') {
                continue;
            }

            $q = e($question);
            $a = nl2br(e($answer));
            $segments[] = '<p><strong>' . $q . '</strong><br>' . $a . '</p>';
        }

        return implode("\n", $segments);
    }
}
