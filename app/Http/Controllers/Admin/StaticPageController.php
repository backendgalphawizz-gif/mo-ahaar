<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\VendorStaticPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaticPageController extends Controller
{
    private array $allowedSlugs = ['privacy-policy', 'terms-and-conditions', 'faqs'];

    public function index(Request $request)
    {
        $title = 'Static Pages';
        // Vendor logic removed
        $pages = StaticPage::whereIn('slug', $this->allowedSlugs)
            ->orderBy('title')
            ->get();
        return view('admin.static-pages.index', compact('title', 'pages'));
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

    public function view($vendorId, $slug)
    {
        abort_unless(in_array($slug, $this->allowedSlugs, true), 404);
        $vendor = Vendor::where('vendor_id', $vendorId)->firstOrFail();

        $page = $this->ensureVendorPage($vendor->vendor_id, $slug);

        return view('admin.static-pages.view', [
            'title' => $page->title,
            'page' => $page,
            'vendor' => $vendor,
        ]);
    }

    protected function ensureVendorPage(int $vendorId, string $slug): VendorStaticPage
    {
        $existing = VendorStaticPage::where('vendor_id', $vendorId)->where('slug', $slug)->first();
        if ($existing) {
            return $existing;
        }

        $base = StaticPage::where('slug', $slug)->first();

        return VendorStaticPage::create([
            'vendor_id' => $vendorId,
            'slug' => $slug,
            'title' => $base->title ?? ucwords(str_replace('-', ' ', $slug)),
            'content' => $base->content ?? '',
            'status' => (int) ($base->status ?? 1),
        ]);
    }
}
