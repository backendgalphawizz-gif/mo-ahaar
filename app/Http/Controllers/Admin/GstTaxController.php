<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithToggleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GstTaxRequest;
use App\Models\GstTax;
use Illuminate\Http\Request;

class GstTaxController extends Controller
{
    use RespondsWithToggleStatus;
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $taxes = GstTax::when($search !== '', fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.gst-taxes.index', compact('taxes', 'search'));
    }

    public function create()
    {
        return view('admin.gst-taxes.create');
    }

    public function store(GstTaxRequest $request)
    {
        GstTax::create($request->validated());

        return redirect()->route('admin.gst-taxes.index')->with('success', 'GST tax created successfully.');
    }

    public function show(GstTax $gst_tax)
    {
        return view('admin.gst-taxes.show', compact('gst_tax'));
    }

    public function edit(GstTax $gst_tax)
    {
        return view('admin.gst-taxes.edit', compact('gst_tax'));
    }

    public function update(GstTaxRequest $request, GstTax $gst_tax)
    {
        $gst_tax->update($request->validated());

        return redirect()->route('admin.gst-taxes.index')->with('success', 'GST tax updated successfully.');
    }

    public function destroy(GstTax $gst_tax)
    {
        $gst_tax->delete();

        return redirect()->route('admin.gst-taxes.index')->with('success', 'GST tax deleted successfully.');
    }

    /**
     * AJAX: toggle active/inactive status.
     */
    public function toggleStatus(Request $request, GstTax $gst_tax)
    {
        $gst_tax->update(['status' => $gst_tax->status === 1 ? 0 : 1]);

        $active = (int) $gst_tax->status === 1;

        return $this->respondToggleStatus($request, true, [
            'is_active' => $gst_tax->status,
            'label'     => $active ? 'Active' : 'Inactive',
        ], $active ? 'GST tax enabled.' : 'GST tax disabled.');
    }
}
