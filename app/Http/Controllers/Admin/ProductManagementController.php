<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithToggleStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\ProductVariant;
use App\Models\GstTax;
use App\Models\DiscountOffer;
use App\Models\ProductReview;
use App\Models\StoreSetting;
use App\Services\ProductBulkImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProductManagementController extends Controller
{
    use RespondsWithToggleStatus;
    private function isVendorPanel(): bool
    {
        return (int) session('role_type') === 3;
    }

    private function currentVendorId(): ?int
    {
        $vendorId = session('vendor_id');
        return $vendorId ? (int) $vendorId : null;
    }

    private function panelRoute(string $adminRoute, string $vendorRoute, array $params = [])
    {
        return $this->isVendorPanel() ? route($vendorRoute, $params) : route($adminRoute, $params);
    }

    public function products(Request $request)
    {
        $title = 'Products List';
        $search = trim((string) $request->query('search', ''));

        $statsQuery = Product::where('status', '!=', 0)
            ->when($this->isVendorPanel(), fn ($q) => $q->where('vendor_id', $this->currentVendorId()));
        $approved = (clone $statsQuery)->where('status', 1)->count();
        $pending = (clone $statsQuery)->where('status', 2)->count();
        $rejected = (clone $statsQuery)->where('status', 3)->count();

        $allProducts = Product::join(
            'product_categories',
            'product_categories.category_id',
            '=',
            'products.category_id',
            'inner',
            false
        )
            ->leftJoin('sub_categories', 'sub_categories.sub_category_id', '=', 'products.sub_category_id')
            ->leftJoin('product_details', function ($join) {
                $join->on('product_details.product_id', '=', 'products.product_id')
                    ->whereRaw('product_details.product_details_id = (SELECT MAX(pd2.product_details_id) FROM product_details pd2 WHERE pd2.product_id = products.product_id)');
            })
            // ->leftJoin('vendors', 'vendors.vendor_id', '=', 'products.vendor_id')
            ->where('products.status', '!=', 0)
            ->when($this->isVendorPanel(), fn ($q) => $q->where('products.vendor_id', $this->currentVendorId()))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('products.product_name', 'like', '%' . $search . '%')
                        ->orWhere('products.sku', 'like', '%' . $search . '%')
                        ->orWhere('products.store_name', 'like', '%' . $search . '%')
                        ->orWhere('product_categories.category_name', 'like', '%' . $search . '%')
                        ->orWhere('sub_categories.sub_cat_name', 'like', '%' . $search . '%');
                });
            })
            ->select(
                'products.*',
                'product_details.product_description',
                'product_categories.category_name',
                'sub_categories.sub_cat_name'
            )
            ->selectRaw('(SELECT ROUND(AVG(pr.rating), 1) FROM product_reviews pr WHERE pr.product_id = products.product_id AND pr.status = 1) as avg_rating')
            ->orderByDesc('products.product_id')
            ->paginate(10)
            ->withQueryString();
        $categoryList = ProductCategory::where('status', '!=', 0)->get();
        return view('admin.products.productList', compact('title', 'allProducts', 'approved', 'pending', 'rejected', 'search', 'categoryList'));
    
    }

    public function addProduct(Request $request)
    {
        $title = 'Add New Food Item';
        $categoryList = ProductCategory::where('status', '!=', 0)->get();
        $gstTaxes = GstTax::active()->orderBy('percentage')->get();
        $promoCodes = DiscountOffer::active()->currentlyValid()->orderBy('title')->get(['id', 'title']);
        return view('admin.products.addProduct', compact('title', 'categoryList', 'gstTaxes', 'promoCodes'));
    }

    public function storeProduct(Request $request)
    {
        $desc = (string) $request->input('product_description', '');
        $plainDesc = trim(strip_tags($desc));
        $shortFromDesc = Str::limit($plainDesc !== '' ? $plainDesc : (string) $request->input('product_name', 'Product'), 300);

        if (trim((string) $request->input('sku', '')) === '') {
            $request->merge(['sku' => $this->generateUniqueProductSku()]);
        }

        $singlePrice = $request->input('price', $request->input('mrp_price'));
        $request->merge([
            'sub_category_id' => $request->filled('sub_category_id') ? $request->input('sub_category_id') : null,
            'short_description' => $shortFromDesc,
            'gst_calculation_type' => $request->input('gst_calculation_type', Product::GST_EXCLUDED),
            'price' => $singlePrice,
            'mrp_price' => $singlePrice,
        ]);

        if (!$request->filled('category_id')) {
            $firstCategoryId = ProductCategory::where('status', '!=', 0)->value('category_id');
            if ($firstCategoryId) {
                $request->merge(['category_id' => $firstCategoryId]);
            }
        }

        $rules = [
            'product_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9 &()\-\/.,]+$/', 'not_regex:/(.)(\1{3,})/'],
            'category_id' => ['nullable', 'exists:product_categories,category_id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,sub_category_id'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->whereNotNull('sku')],
            'mrp_price' => ['required', 'numeric', 'min:0', 'regex:/^\d{1,10}(\.\d{1,2})?$/'],
            'price' => ['required', 'numeric', 'min:0', 'regex:/^\d{1,10}(\.\d{1,2})?$/'],
            'min_quantity' => ['nullable', 'integer', 'min:1'],
            'product_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'product_description' => ['required', 'string'],
            'video' => ['nullable', 'url', 'max:255'],
            'featured' => ['nullable', Rule::in(['0', '1'])],
            'gst_tax_id' => ['nullable', 'exists:gst_taxes,id'],
            'gst_calculation_type' => ['required', Rule::in(Product::gstCalculationTypeOptions())],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'product_type' => ['required', Rule::in(['veg', 'non-veg'])],
            'tags' => ['nullable', 'string', 'max:100'],
        ];

        $messages = [
            'product_name.max' => 'Product name may not be greater than 100 characters.',
            'product_name.regex' => 'Product name must not contain special characters.',
            'product_name.not_regex' => 'Product name must not repeat the same character more than 3 times in a row.',
            'sku.required' => 'SKU is required.',
            'sku.max' => 'SKU may not be greater than 100 characters.',
            'sku.unique' => 'This SKU is already in use.',
            'mrp_price.required' => 'Price is required.',
            'mrp_price.numeric' => 'Price must be a valid number.',
            'mrp_price.min' => 'Price must be at least 0.',
            'mrp_price.regex' => 'Price may not be greater than 10 digits.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price must be at least 0.',
            'price.regex' => 'Price may not be greater than 10 digits.',
            'product_description.required' => 'Ingredients are required.',
            'product_type.required' => 'Please select veg or non-veg.',
            'product_type.in' => 'Food type must be veg or non-veg.',
            'min_quantity.integer' => 'Minimum order quantity must be a whole number.',
            'min_quantity.min' => 'Minimum order quantity must be at least 1.',
            'sub_category_id.exists' => 'Selected sub-category is invalid.',
            'product_image.required' => 'Please upload a product thumbnail image.',
            'product_image.image' => 'The product thumbnail must be an image file (jpg, jpeg, png, webp).',
            'product_image.mimes' => 'The product thumbnail must be a file of type: jpg, jpeg, png, webp.',
            'product_image.max' => 'The product thumbnail may not be greater than 2MB.',
            'gallery_images.*.image' => 'Each gallery image must be an image file (jpg, jpeg, png, webp).',
            'gallery_images.*.mimes' => 'Each gallery image must be a file of type: jpg, jpeg, png, webp.',
            'gallery_images.*.max' => 'Each gallery image may not be greater than 4MB.',
        ];

        $validated = $request->validate($rules, $messages);

        $product = new Product();
        $product->product_name = $validated['product_name'];
        $product->short_description = $shortFromDesc;
        $product->product_slug = Str::slug($validated['product_name']) . '-' . time();
        $product->product_type = $validated['product_type'];
        $product->category_id = $validated['category_id'];
        $product->sub_category_id = $validated['sub_category_id'] ?? null;
        $product->sub_sub_category_id = null;
        $product->price = $validated['price'];
        $product->discount = $validated['discount'] ?? 0;
        if (Schema::hasColumn('products', 'mrp_price')) {
            $product->mrp_price = $validated['mrp_price'];
        }
        $product->sale_price = null;
        $product->sku = $validated['sku'];
        $product->min_quantity = $validated['min_quantity'] ?? null;
        $product->applyLegacyStockDefaults();
        $product->video = $validated['video'] ?? null;
        $product->tags = $validated['tags'] ?? null;
        $product->featured = (int) ($validated['featured'] ?? 0);
        // Set defaults for admin-added products
        $product->status = 1;
        $product->sale_status = 0;
        $product->random_related_product = 0;
        $product->free_shipping = 0;
        $product->safe_checkout = 0;
        $product->secure_checkout = 0;
        $product->social_share = 0;
        $product->encourage_order = 0;
        $product->encourage_view = 0;
        $product->trending = 0;
        $product->is_returnable = 0;
        $product->is_active_status = 1;
        $product->gst_calculation_type = $validated['gst_calculation_type'] ?? Product::GST_EXCLUDED;
        if ($this->isVendorPanel()) {
            $product->vendor_id = $this->currentVendorId();
        }
        $gstTax = !empty($validated['gst_tax_id']) ? GstTax::find($validated['gst_tax_id']) : null;
        $product->gst_percentage = $gstTax ? number_format((float) $gstTax->percentage, 2) : null;
        $product->tax_name = $gstTax ? 'GST ' . number_format((float) $gstTax->percentage, 2) . '%' : null;

        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $product->product_image = $imageName;
        }

        if (!$product->save()) {
            return redirect()->back()->withInput()->with('error', 'Product could not be added. Please try again.');
        }

        $productDetails = new ProductDetails();
        $productDetails->product_id = $product->product_id;
        $productDetails->product_description = $validated['product_description'] ?? '';

        if ($request->hasFile('gallery_images')) {
            $imageNames = [];
            foreach ($request->file('gallery_images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $imageName);
                $imageNames[] = $imageName;
            }
            $productDetails->gallery_images = implode(',', $imageNames);
        }

        if (!$productDetails->save()) {
            return redirect()->back()->withInput()->with('error', 'Product details could not be saved. Please try again.');
        }

        return redirect($this->panelRoute('admin.products', 'vendor.products'))->with('success', 'Product created successfully!');
    }

    public function viewProduct($id)
    {
        $title = 'View Product';

        $product = Product::leftJoin('product_details', 'product_details.product_id', '=', 'products.product_id')
            ->leftJoin('product_categories', 'product_categories.category_id', '=', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.sub_category_id', '=', 'products.sub_category_id')
            ->where('products.product_id', $id)
            ->where('products.status', '!=', 0)
            ->when($this->isVendorPanel(), fn ($q) => $q->where('products.vendor_id', $this->currentVendorId()))
            ->select(
                'products.*',
                'product_details.product_description',
                'product_details.gallery_images',
                'product_categories.category_name',
                'sub_categories.sub_cat_name'
            )
            ->selectRaw('(SELECT ROUND(AVG(pr.rating), 1) FROM product_reviews pr WHERE pr.product_id = products.product_id AND pr.status = 1) as avg_rating')
            ->first();

        if (!$product) {
            return redirect($this->panelRoute('admin.products', 'vendor.products'))->with('error', 'Product not found.');
        }

        // Attribute/variant logic removed
        return view('admin.products.viewProduct', compact('title', 'product'));
    }

    public function editProduct($id)
    {
        $title = 'Edit Food Item';

        $product = Product::leftJoin('product_details', 'product_details.product_id', '=', 'products.product_id')
            ->where('products.product_id', $id)
            ->where('products.status', '!=', 0)
            ->when($this->isVendorPanel(), fn ($q) => $q->where('products.vendor_id', $this->currentVendorId()))
            ->select('products.*', 'product_details.product_description', 'product_details.gallery_images', 'product_details.meta_title', 'product_details.meta_description', 'product_details.meta_keywords')
            ->first();

        if (!$product) {
            return redirect($this->panelRoute('admin.products', 'vendor.products'))->with('error', 'Product not found.');
        }

        $categoryList = ProductCategory::where('status', '!=', 0)->get();
        $gstTaxes = GstTax::active()->orderBy('percentage')->get();
        $promoCodes = DiscountOffer::active()->currentlyValid()->orderBy('title')->get(['id', 'title']);
        return view('admin.products.editProduct', compact('title', 'product', 'categoryList', 'gstTaxes', 'promoCodes'));
    }

    public function updateProduct(Request $request)
    {
        $product = Product::where('product_id', (int) $request->input('product_id'))->first();
        if ($product && $this->isVendorPanel() && (int) $product->vendor_id !== (int) $this->currentVendorId()) {
            $product = null;
        }
        if (!$product) {
            return redirect($this->panelRoute('admin.products', 'vendor.products'))->with('error', 'Product not found.');
        }

        $desc = (string) $request->input('product_description', '');
        $plainDesc = trim(strip_tags($desc));
        $singlePrice = $request->input('price', $request->input('mrp_price', $product->price));
        $request->merge([
            'short_description' => Str::limit($plainDesc !== '' ? $plainDesc : (string) $request->input('product_name', $product->product_name), 300),
            'sub_category_id' => $request->filled('sub_category_id') ? $request->input('sub_category_id') : null,
            'gst_calculation_type' => $request->input('gst_calculation_type', $product->gst_calculation_type ?? Product::GST_EXCLUDED),
            'price' => $singlePrice,
            'mrp_price' => $singlePrice,
        ]);
        if (!$request->filled('category_id')) {
            $request->merge(['category_id' => $product->category_id ?: ProductCategory::where('status', '!=', 0)->value('category_id')]);
        }
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'product_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9 &()\-\/.,]+$/', 'not_regex:/(.)(\1{3,})/'],

            'short_description' => ['required', 'string', 'max:300'],
            'product_description' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:product_categories,category_id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,sub_category_id'],
            'mrp_price' => ['required', 'numeric', 'min:0', 'regex:/^\d{1,10}(\.\d{1,2})?$/'],
            'price' => ['required', 'numeric', 'min:0', 'regex:/^\d{1,10}(\.\d{1,2})?$/'],
            'min_quantity' => ['nullable', 'integer', 'min:1'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->product_id, 'product_id')->whereNotNull('sku')],
            'status' => ['required', Rule::in(['1', '2', '3'])],
            'product_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'video' => ['nullable', 'url', 'max:255'],
            'featured' => ['nullable', Rule::in(['0', '1'])],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gst_tax_id' => ['nullable', 'exists:gst_taxes,id'],
            'gst_calculation_type' => ['required', Rule::in(Product::gstCalculationTypeOptions())],
            'product_type' => ['required', Rule::in(['veg', 'non-veg'])],
            'tags' => ['nullable', 'string', 'max:100'],
        ], [
            'mrp_price.required' => 'MRP price is required.',
            'mrp_price.numeric' => 'MRP price must be a number.',
            'mrp_price.min' => 'MRP price must be at least 0.',
            'mrp_price.regex' => 'MRP price may not be greater than 10 digits.',
            'sku.unique' => 'This SKU is already in use.',
            'sub_category_id.exists' => 'Selected sub-category is invalid.',
            'price.lte' => 'Discounted price cannot be greater than MRP price.',
            'price.regex' => 'Price may not be greater than 10 digits.',
        ]);

        $product->product_name = $validated['product_name'];
        $product->short_description = $validated['short_description'];
        $product->product_slug = Str::slug($validated['product_name']) . '-' . $product->product_id;
        $product->category_id = $validated['category_id'];
        $product->sub_category_id = $validated['sub_category_id'] ?? null;
        $product->sub_sub_category_id = null;
        $product->price = $validated['price'];
        $product->discount = $validated['discount'] ?? 0;
        if (Schema::hasColumn('products', 'mrp_price')) {
            $product->mrp_price = $validated['mrp_price'];
        }
        $product->sale_price = null;
        $product->min_quantity = $validated['min_quantity'] ?? null;
        $product->applyLegacyStockDefaults();
        $product->sku = $validated['sku'];
        $product->status = (int) $validated['status'];
        $product->is_active_status = $request->boolean('is_active_status') ? 1 : 0;
        $product->featured = (int) ($validated['featured'] ?? 0);
        $product->video = $validated['video'] ?? null;
        $product->product_type = $validated['product_type'];
        $product->tags = $validated['tags'] ?? null;
        $product->sale_status = 0;
        $product->gst_calculation_type = $validated['gst_calculation_type'] ?? Product::GST_EXCLUDED;
        $gstTaxUpdate = !empty($validated['gst_tax_id']) ? GstTax::find($validated['gst_tax_id']) : null;
        $product->gst_percentage = $gstTaxUpdate ? number_format((float) $gstTaxUpdate->percentage, 2) : null;
        $product->tax_name = $gstTaxUpdate ? 'GST ' . number_format((float) $gstTaxUpdate->percentage, 2) . '%' : null;

        if ($request->hasFile('product_image')) {
            $oldPath = public_path('uploads/products/' . $product->product_image);
            if (!empty($product->product_image) && File::exists($oldPath)) {
                File::delete($oldPath);
            }

            $image = $request->file('product_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $product->product_image = $imageName;
        }

        $product->save();

        $productDetails = ProductDetails::firstOrNew(['product_id' => $product->product_id]);
        $productDetails->product_description = $validated['product_description'];

        // Handle deletion of selected gallery images
        $imagesToDelete = $request->input('gallery_images_to_delete', '');
        if (!empty($imagesToDelete)) {
            $deleteList = array_filter(array_map('trim', explode(',', $imagesToDelete)));
            if (!empty($deleteList) && !empty($productDetails->gallery_images)) {
                $currentImages = array_filter(array_map('trim', explode(',', $productDetails->gallery_images)));
                $remainingImages = [];
                
                foreach ($currentImages as $img) {
                    if (!in_array($img, $deleteList)) {
                        $remainingImages[] = $img;
                    } else {
                        // Delete the file
                        $imgPath = public_path('uploads/products/' . $img);
                        if (File::exists($imgPath)) {
                            File::delete($imgPath);
                        }
                    }
                }
                
                $productDetails->gallery_images = !empty($remainingImages) ? implode(',', $remainingImages) : null;
            }
        }

        // Handle upload of new gallery images
        if ($request->hasFile('gallery_images')) {
            $galleryNames = [];
            foreach ($request->file('gallery_images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $imageName);
                $galleryNames[] = $imageName;
            }
            // Append new images to existing or replace if none remain
            $productDetails->gallery_images = !empty($galleryNames) ? implode(',', $galleryNames) : $productDetails->gallery_images;
        }

        $productDetails->save();

        if ($request->has('variants') && is_array($request->input('variants'))) {
            $submittedVariants = $request->input('variants', []);
            $submittedIds = collect($submittedVariants)->pluck('id')->filter()->map('intval')->values()->toArray();

            $toDelete = ProductVariant::where('product_id', $product->product_id)
                ->when(!empty($submittedIds), fn ($q) => $q->whereNotIn('id', $submittedIds))
                ->when(empty($submittedIds), fn ($q) => $q)
                ->get();
            foreach ($toDelete as $delVariant) {
                if ($delVariant->image && File::exists(public_path('uploads/products/variants/' . $delVariant->image))) {
                    File::delete(public_path('uploads/products/variants/' . $delVariant->image));
                }
                $delVariant->delete();
            }

            foreach ($submittedVariants as $idx => $variantData) {
                $variantId = !empty($variantData['id']) ? (int) $variantData['id'] : null;
                if ($variantId) {
                    $variant = ProductVariant::where('id', $variantId)->where('product_id', $product->product_id)->first();
                    if (!$variant) {
                        continue;
                    }
                } else {
                    $variant = new ProductVariant();
                    $variant->product_id = $product->product_id;
                }
                $variant->variant_label = $variantData['label'] ?? null;
                $variant->attribute_combination = !empty($variantData['combination']) ? json_encode($variantData['combination']) : null;
                $variant->price = $variantData['price'];
                $variant->sale_price = !empty($variantData['sale_price']) ? $variantData['sale_price'] : null;
                if (Schema::hasColumn('product_variants', 'stock')) {
                    $variant->stock = null;
                }
                $variant->sku = $variantData['sku'] ?? null;

                $variantFiles = $request->file('variant_images');
                if (!empty($variantFiles[$idx])) {
                    if ($variant->image && File::exists(public_path('uploads/products/variants/' . $variant->image))) {
                        File::delete(public_path('uploads/products/variants/' . $variant->image));
                    }
                    $imgFile = $variantFiles[$idx];
                    $imgName = time() . '_' . uniqid() . '.' . $imgFile->getClientOriginalExtension();
                    $imgFile->move(public_path('uploads/products/variants'), $imgName);
                    $variant->image = $imgName;
                }
                $variant->save();
            }
        }

        return redirect($this->panelRoute('admin.products', 'vendor.products'))->with('success', 'Product updated successfully!');
    }

    public function deleteProduct($id)
    {
        $product = Product::where('product_id', $id)->first();
        if ($product && $this->isVendorPanel() && (int) $product->vendor_id !== (int) $this->currentVendorId()) {
            $product = null;
        }
        if (!$product) {
            return redirect($this->panelRoute('admin.products', 'vendor.products'))->with('error', 'Product not found.');
        }

        $product->status = 0;
        $product->save();

        return redirect($this->panelRoute('admin.products', 'vendor.products'))->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $product = Product::where('product_id', $id)->first();
        if ($product && $this->isVendorPanel() && (int) $product->vendor_id !== (int) $this->currentVendorId()) {
            $product = null;
        }
        if (!$product) {
            return $this->respondToggleStatus($request, false, [], 'Product not found.');
        }

        $product->is_active_status = (int) $product->is_active_status === 1 ? 0 : 1;
        $product->save();

        $active = (int) $product->is_active_status === 1;

        return $this->respondToggleStatus($request, true, [
            'is_active' => $product->is_active_status,
            'label'     => $active ? 'Active' : 'Inactive',
        ], 'Product status updated successfully.');
    }

    public function updateApprovalStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['1', '2', '3'])],
        ]);

        $product = Product::where('product_id', $id)->first();
        if (!$product) {
            return back()->with('error', 'Product not found.');
        }

        $product->status = (int) $validated['status'];
        $product->save();

        return back()->with('success', 'Product approval status updated successfully.');
    }

    public function categories()
    {
        $title = 'Categories List';
        $search = trim((string) request()->query('search', ''));
        $allCategories = ProductCategory::where('status', '!=', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('category_name', 'like', "%$search%")
                      ->orWhere('slug', 'like', "%$search%");
                });
            })
            ->orderByDesc('category_id')
            ->paginate(10)
            ->withQueryString();
        return view('admin.categories.categoryList', compact('title', 'allCategories'));
    }

    public function addNewCategory()
    {
        return view('admin.categories.addCategory');
    }

    public function storeCategory(Request $request)
    {
        // Validate input, especially category_image as image
        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:product_categories,category_name',
            'category_description' => 'nullable|string',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $category = new ProductCategory();
        $category->category_name = $validated['category_name'];
        $category->slug = Str::slug($validated['category_name']);
        $category->category_description = $validated['category_description'] ?? null;

        if ($request->hasFile('category_image')) {
            $image = $request->file('category_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/categories'), $imageName);
            $category->category_image = $imageName;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('success', 'Category Created Successfully');
    }

    public function editCategory($id)
    {
        $categoryId = Crypt::decrypt(urldecode($id));
        $category = ProductCategory::findOrFail($categoryId);
        return view('admin.categories.editCategory', compact('category'));
    }

    public function updateCategory(Request $request)
    {
        // Validate input, especially category_image as image
        $validated = $request->validate([
            'category_id' => 'required|exists:product_categories,category_id',
            'category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories', 'category_name')->ignore($request->category_id, 'category_id'),
            ],
            'category_description' => 'nullable|string',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $categoryId = $validated['category_id'];
        $category = ProductCategory::findOrFail($categoryId);

        $category->category_name = $validated['category_name'];
        $category->slug = Str::slug($validated['category_name']);
        $category->category_description = $validated['category_description'] ?? null;

        if ($request->hasFile('category_image')) {
            $oldImagePath = public_path('uploads/categories/' . $category->category_image);

            if ($category->category_image && File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }

            $image = $request->file('category_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/categories'), $imageName);

            $category->category_image = $imageName;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('success', 'Category Updated Successfully');
    }

    public function deleteCategory($category_id)
    {
        $category = ProductCategory::findOrFail($category_id);
        $category->status = '0';
        $category->save();
        return redirect()->route('admin.categories')->with('success', 'Category Deleted Successfully');
    }

    public function subCategories()
    {
        $title = 'Sub Categories List';
        $search = trim((string) request()->query('search', ''));
        $allSubCategories = ProductSubCategory::join(
            'product_categories',
            'product_categories.category_id',
            '=',
            'sub_categories.category_id',
            'inner',
            false
        )
            ->where('sub_categories.status', '!=', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('sub_categories.sub_cat_name', 'like', "%$search%")
                      ->orWhere('sub_categories.sub_cat_slug', 'like', "%$search%")
                      ->orWhere('product_categories.category_name', 'like', "%$search%");
                });
            })
            ->select('sub_categories.*', 'product_categories.category_name')
            ->orderByDesc('sub_categories.sub_category_id')
            ->paginate(10)
            ->withQueryString();
        return view('admin.categories.subCategoryList', compact('title', 'allSubCategories'));
    }

    public function addSubCategory()
    {
        $title = 'Add Sub Category';
        $categoryList = ProductCategory::where('status', '!=', 0)->get();
        return view('admin.categories.addSubCategory', compact('title', 'categoryList'));
    }

    public function storeSubCategory(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:product_categories,category_id',
            'sub_category_name' => 'required|string|max:255|unique:sub_categories,sub_cat_name',
            'sub_category_description' => 'nullable|string',
            'sub_category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $subCategory = new ProductSubCategory();
        $subCategory->category_id = $validated['category_id'];
        $subCategory->sub_cat_name = $validated['sub_category_name'];
        $subCategory->sub_cat_slug = Str::slug($validated['sub_category_name']);
        $subCategory->sub_cat_description = $validated['sub_category_description'] ?? null;

        if ($request->hasFile('sub_category_image')) {
            $image = $request->file('sub_category_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/sub_categories'), $imageName);
            $subCategory->sub_cat_image = $imageName;
        }

        if ($subCategory->status === null || $subCategory->status === '') {
            $subCategory->status = 1;
        }

        $subCategory->save();
        return redirect()->back()->withInput()->with('success', 'Sub Category Created Successfully');
    }

    public function editSubCategory($sub_cat_id)
    {
        $title = 'Edit Sub Category';
        $subCategoryId = Crypt::decrypt(urldecode($sub_cat_id));
        $subCategory = ProductSubCategory::findOrFail($subCategoryId);
        $categoryList = ProductCategory::where('status', '!=', 0)->get();
        return view('admin.categories.editSubCategory', compact('subCategory', 'categoryList', 'title'));
    }

    public function updateSubCategory(Request $request)
    {
        $validated = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,sub_category_id',
            'category_id' => 'required|exists:product_categories,category_id',
            'sub_category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_categories', 'sub_cat_name')->ignore($request->sub_category_id, 'sub_category_id'),
            ],
            'sub_category_description' => 'nullable|string',
            'sub_category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $subCategoryId = $validated['sub_category_id'];
        $subCategory = ProductSubCategory::findOrFail($subCategoryId);

        $subCategory->category_id = $validated['category_id'];
        $subCategory->sub_cat_name = $validated['sub_category_name'];
        $subCategory->sub_cat_slug = Str::slug($validated['sub_category_name']);
        $subCategory->sub_cat_description = $validated['sub_category_description'] ?? null;

        if ($request->hasFile('sub_category_image')) {
            $oldImagePath = public_path('uploads/sub_categories/' . $subCategory->sub_cat_image);

            if ($subCategory->sub_cat_image && File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }

            $image = $request->file('sub_category_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/sub_categories'), $imageName);

            $subCategory->sub_cat_image = $imageName;
        }

        $subCategory->save();

        return redirect()->back()->withInput()->with('success', 'Sub Category Updated Successfully');
    }

    public function deleteSubCategory($sub_category_id)
    {
        $subCategory = ProductSubCategory::findOrFail($sub_category_id);
        $subCategory->status = '0';
        $subCategory->save();
        return redirect()->route('admin.sub-category')->with('success', 'Sub Category Deleted Successfully');
    }

    /**
     * AJAX: Change product category (and subcategory)
     */
    public function changeProductCategory(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'category_id' => ['required', 'exists:product_categories,category_id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,sub_category_id'],
        ]);

        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $product->category_id = $validated['category_id'];
        $product->sub_category_id = $validated['sub_category_id'] ?? null;
        $product->sub_sub_category_id = null;
        $product->save();

        return response()->json(['success' => true, 'message' => 'Category updated successfully.']);
    }

    private function generateUniqueProductSku(): string
    {
        do {
            $sku = 'SF-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    public function downloadBulkImportSample()
    {
        $includeVendor = !$this->isVendorPanel();
        $headers = ProductBulkImportService::SAMPLE_HEADERS;
        if (!$includeVendor) {
            $headers = array_values(array_filter($headers, fn ($h) => $h !== 'vendor_id'));
        }

        $service = new ProductBulkImportService();
        $rows = $service->sampleDataRows($includeVendor);

        $fileName = 'products-bulk-import-sample-' . date('Y-m-d') . '.xls';
        $displayHeaders = array_map(
            fn ($h) => in_array($h, ['product_name', 'category', 'product_type', 'price', 'ingredients'], true) ? $h . '*' : $h,
            $headers
        );

        $callback = function () use ($displayHeaders, $rows, $headers) {
            echo implode("\t", $displayHeaders) . "\n";
            foreach ($rows as $row) {
                $line = [];
                foreach ($headers as $key) {
                    $line[] = str_replace(["\t", "\r", "\n"], ' ', (string) ($row[$key] ?? ''));
                }
                echo implode("\t", $line) . "\n";
            }
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    public function importProductsBulk(Request $request, ProductBulkImportService $bulkImport)
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:5120'],
        ], [
            'import_file.required' => 'Please choose a file to upload.',
            'import_file.mimes' => 'File must be CSV or Excel (.csv, .xls, .xlsx).',
            'import_file.max' => 'File may not be greater than 5MB.',
        ]);

        $forcedVendorId = $this->isVendorPanel() ? $this->currentVendorId() : null;
        $allowVendorColumn = !$this->isVendorPanel();

        $result = $bulkImport->import(
            $request->file('import_file'),
            $forcedVendorId,
            $allowVendorColumn
        );

        $message = $result['created'] . ' product(s) imported successfully.';
        if ($result['failed'] > 0) {
            $message .= ' ' . $result['failed'] . ' row(s) failed.';
        }

        return redirect($this->panelRoute('admin.products', 'vendor.products'))
            ->with('success', $message)
            ->with('bulk_import_errors', $result['errors']);
    }

    public function exportProductsExcel(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::join('product_categories', 'product_categories.category_id', '=', 'products.category_id', 'inner', false)
            ->leftJoin('sub_categories', 'sub_categories.sub_category_id', '=', 'products.sub_category_id')
            ->leftJoin('product_details', function ($join) {
                $join->on('product_details.product_id', '=', 'products.product_id')
                    ->whereRaw('product_details.product_details_id = (SELECT MAX(pd2.product_details_id) FROM product_details pd2 WHERE pd2.product_id = products.product_id)');
            })
            ->where('products.status', '!=', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('products.product_name', 'like', '%' . $search . '%')
                        ->orWhere('products.sku', 'like', '%' . $search . '%')
                        ->orWhere('product_categories.category_name', 'like', '%' . $search . '%');
                });
            })
            ->select('products.*', 'product_categories.category_name', 'sub_categories.sub_cat_name')
            ->orderByDesc('products.product_id')
            ->get();

        $statusLabels = [1 => 'Approved', 2 => 'Pending', 3 => 'Rejected'];

        $fileName = 'products-export-' . date('Y-m-d-H-i-s') . '.xls';
        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($products, $statusLabels) {
            echo "S.No.\tProduct Name\tCategory\tSKU\tMRP (INR)\tPrice (INR)\tApproval\n";
            foreach ($products as $index => $product) {
                $cat = ($product->category_name ?? '-');
                if (!empty($product->sub_cat_name)) {
                    $cat .= ' / ' . $product->sub_cat_name;
                }
                echo ($index + 1) . "\t";
                echo ($product->product_name ?? 'N/A') . "\t";
                echo $cat . "\t";
                echo ($product->sku ?? '-') . "\t";
                echo number_format((float)($product->mrp ?? 0), 2, '.', '') . "\t";
                echo number_format((float)($product->price ?? 0), 2, '.', '') . "\t";
                echo ($statusLabels[(int)($product->status ?? 0)] ?? 'Unknown') . "\n";
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportProductsPdf(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::join('product_categories', 'product_categories.category_id', '=', 'products.category_id', 'inner', false)
            ->leftJoin('sub_categories', 'sub_categories.sub_category_id', '=', 'products.sub_category_id')
            ->leftJoin('product_details', function ($join) {
                $join->on('product_details.product_id', '=', 'products.product_id')
                    ->whereRaw('product_details.product_details_id = (SELECT MAX(pd2.product_details_id) FROM product_details pd2 WHERE pd2.product_id = products.product_id)');
            })
            ->where('products.status', '!=', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('products.product_name', 'like', '%' . $search . '%')
                        ->orWhere('products.sku', 'like', '%' . $search . '%')
                        ->orWhere('product_categories.category_name', 'like', '%' . $search . '%');
                });
            })
            ->select('products.*', 'product_categories.category_name', 'sub_categories.sub_cat_name')
            ->orderByDesc('products.product_id')
            ->get();

        $storeSetting = StoreSetting::first();

        $pdf = Pdf::loadView('admin.products.productsExportPdf', compact('products', 'storeSetting', 'search'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('products-export-' . date('Y-m-d-H-i-s') . '.pdf');
    }
}
