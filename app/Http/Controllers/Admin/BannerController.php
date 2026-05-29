<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithToggleStatus;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    use RespondsWithToggleStatus;
    public function index(Request $request)
    {
        $title = 'Homepage Banners';
        $banners = Banner::where('status', '!=', 0)
            ->when($request->filled('status'), fn ($q) => $q->where('status', (int) $request->query('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('date_from')))
            ->orderByDesc('id')
            ->get();

        return view('admin.banners.index', compact('title', 'banners'));
    }

    public function create()
    {
        $title = 'Add Banner';
        $hasBannerType = Schema::hasColumn('banners', 'banner_type');

        return view('admin.banners.create', compact('title', 'hasBannerType'));
    }

    public function store(Request $request)
    {
        $request->merge(['title' => trim((string) $request->input('title', ''))]);
        $validated = $request->validate(
            array_merge($this->baseBannerRules(true), $this->bannerTypeRules()),
            $this->bannerValidationMessages()
        );

        $banner = new Banner();
        $banner->title = $validated['title'] ?? null;
        $banner->subtitle = null;
        $banner->button_text = null;
        $link = isset($validated['link']) ? trim((string) $validated['link']) : '';
        $banner->button_link = $link !== '' ? $link : null;
        $banner->sort_order = 0;
        if (Schema::hasColumn('banners', 'banner_type')) {
            $banner->banner_type = $validated['banner_type'] ?? Banner::BANNER_TYPE_SLIDER;
        }
        $banner->visible_from = $validated['visible_from'] ?? null;
        $banner->visible_to = $validated['visible_to'] ?? null;
        $banner->status = (int) $validated['status'];

        $image = $request->file('banner_image');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/banners'), $imageName);
        $banner->banner_image = $imageName;

        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit($id)
    {
        $title = 'Edit Banner';
        $banner = Banner::where('id', $id)->where('status', '!=', 0)->firstOrFail();

        $hasBannerType = Schema::hasColumn('banners', 'banner_type');

        return view('admin.banners.edit', compact('title', 'banner', 'hasBannerType'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::where('id', $id)->where('status', '!=', 0)->firstOrFail();

        $request->merge(['title' => trim((string) $request->input('title', ''))]);
        $validated = $request->validate(
            array_merge($this->baseBannerRules(false), $this->bannerTypeRules()),
            $this->bannerValidationMessages()
        );

        $banner->title = $validated['title'] ?? null;
        $banner->subtitle = null;
        $banner->button_text = null;
        $link = isset($validated['link']) ? trim((string) $validated['link']) : '';
        $banner->button_link = $link !== '' ? $link : null;
        $banner->visible_from = $validated['visible_from'] ?? null;
        $banner->visible_to = $validated['visible_to'] ?? null;
        $banner->status = (int) $validated['status'];
        if (Schema::hasColumn('banners', 'banner_type')) {
            $banner->banner_type = $validated['banner_type'] ?? ($banner->banner_type ?: Banner::BANNER_TYPE_SLIDER);
        }

        if ($request->hasFile('banner_image')) {
            $oldPath = public_path('uploads/banners/' . $banner->banner_image);
            if (!empty($banner->banner_image) && File::exists($oldPath)) {
                File::delete($oldPath);
            }

            $image = $request->file('banner_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/banners'), $imageName);
            $banner->banner_image = $imageName;
        }

        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function delete($id)
    {
        $banner = Banner::where('id', $id)->where('status', '!=', 0)->firstOrFail();
        $banner->status = 0;
        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $banner = Banner::where('id', $id)->where('status', '!=', 0)->firstOrFail();
        $banner->status = (int) $banner->status === 1 ? 2 : 1;
        $banner->save();

        $active = (int) $banner->status === 1;

        return $this->respondToggleStatus($request, true, [
            'is_active' => $active ? 1 : 0,
            'label'     => $active ? 'Active' : 'Inactive',
        ], $active ? 'Banner enabled.' : 'Banner disabled.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function baseBannerRules(bool $imageRequired): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:190'],
            'link' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:1,2'],
            'visible_from' => ['nullable', 'date'],
            'visible_to' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'banner_image' => array_merge(
                $imageRequired ? ['required'] : ['nullable'],
                ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']
            ),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function bannerTypeRules(): array
    {
        if (!Schema::hasColumn('banners', 'banner_type')) {
            return [];
        }

        return [
            'banner_type' => ['nullable', 'string', Rule::in(Banner::homeBannerTypeOptions())],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function bannerValidationMessages(): array
    {
        return [
            'title.required' => 'Banner title is required.',
            'title.min' => 'Banner title is required.',
            'banner_image.required' => 'Banner image is required.',
            'banner_image.image' => 'Banner image must be a valid image file.',
            'banner_image.mimes' => 'Banner image must be JPG, PNG, or WEBP.',
            'banner_image.max' => 'Banner image may not be greater than 4MB.',
            'status.required' => 'Status is required.',
            'status.in' => 'Please select a valid status.',
            'banner_type.required' => 'Home screen section is required.',
            'visible_to.after_or_equal' => 'Visible to date must be on or after the start date.',
        ];
    }
}
