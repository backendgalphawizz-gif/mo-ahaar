<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDetails;
use App\Models\ProductSubCategory;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductBulkImportService
{
    public const SAMPLE_HEADERS = [
        'product_name',
        'category',
        'sub_category',
        'product_type',
        'price',
        'discount',
        'gst_percentage',
        'gst_calculation_type',
        'min_quantity',
        'sku',
        'ingredients',
        'tags',
        'featured',
        'vendor_id',
    ];

    /**
     * @return list<array<string, string>>
     */
    public function sampleDataRows(bool $includeVendorColumn): array
    {
        $category = ProductCategory::where('status', '!=', 0)->orderBy('category_id')->first();
        $categoryLabel = $category?->category_name ?? 'Main Category';
        $categoryId = $category?->category_id ?? 1;

        $subLabel = '';
        if ($category) {
            $sub = ProductSubCategory::where('category_id', $category->category_id)
                ->where('status', '!=', 0)
                ->first();
            $subLabel = $sub?->sub_cat_name ?? '';
        }

        $row = [
            'product_name' => 'Paneer Butter Masala',
            'category' => $categoryLabel,
            'sub_category' => $subLabel,
            'product_type' => 'veg',
            'price' => '240',
            'discount' => '10',
            'gst_percentage' => '18',
            'gst_calculation_type' => 'excluded',
            'min_quantity' => '1',
            'sku' => 'SF-BULK-SAMPLE',
            'ingredients' => 'Paneer, butter, tomato, cream, spices',
            'tags' => 'main course,popular',
            'featured' => '0',
            'vendor_id' => '',
        ];

        if ($includeVendorColumn) {
            $vendorId = Vendor::where('approval_status', 'approved')->value('vendor_id');
            $row['vendor_id'] = $vendorId ? (string) $vendorId : '';
        }

        $hint = [
            'product_name' => '(Required) Max 100 chars',
            'category' => '(Required) Category name or ID e.g. ' . $categoryId,
            'sub_category' => '(Optional) Sub-category name or ID',
            'product_type' => '(Required) veg or non-veg',
            'price' => '(Required) Selling price in INR',
            'discount' => '(Optional) Percent 0-100',
            'gst_percentage' => '(Optional) e.g. 5, 12, 18',
            'gst_calculation_type' => '(Optional) excluded or included',
            'min_quantity' => '(Optional) Minimum order qty',
            'sku' => '(Optional) Auto-generated if empty',
            'ingredients' => '(Required) Description / ingredients',
            'tags' => '(Optional) Comma-separated',
            'featured' => '(Optional) 1 or 0',
            'vendor_id' => $includeVendorColumn ? '(Admin only) Vendor ID for restaurant' : '',
        ];

        return [$row, $hint];
    }

    /**
     * @return array{created:int, failed:int, errors:array<int, string>}
     */
    public function import(UploadedFile $file, ?int $forcedVendorId = null, bool $allowVendorColumn = false): array
    {
        $rows = $this->parseFile($file);
        if (count($rows) < 2) {
            return [
                'created' => 0,
                'failed' => 0,
                'errors' => [1 => 'File is empty or has no data rows.'],
            ];
        }

        $headerRow = array_shift($rows);
        $map = $this->mapHeaders($headerRow, $allowVendorColumn);

        if (!in_array('product_name', $map, true)) {
            return [
                'created' => 0,
                'failed' => 0,
                'errors' => [1 => 'Missing required column: product_name'],
            ];
        }

        $created = 0;
        $failed = 0;
        $errors = [];
        $lineNumber = 1;

        foreach ($rows as $row) {
            $lineNumber++;
            if ($this->isEmptyRow($row)) {
                continue;
            }

            if ($this->isHintRow($row, $map)) {
                continue;
            }

            $data = $this->rowToAssoc($row, $map);

            try {
                $this->importRow($data, $forcedVendorId, $allowVendorColumn);
                $created++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[$lineNumber] = $e->getMessage();
            }
        }

        return compact('created', 'failed', 'errors');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function importRow(array $data, ?int $forcedVendorId, bool $allowVendorColumn): void
    {
        $name = trim((string) ($data['product_name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Product name is required.');
        }
        if (mb_strlen($name) > 100) {
            throw new \InvalidArgumentException('Product name may not exceed 100 characters.');
        }
        if (!preg_match('/^[a-zA-Z0-9 &()\-\/.,]+$/', $name)) {
            throw new \InvalidArgumentException('Product name contains invalid characters.');
        }

        $categoryId = $this->resolveCategoryId($data['category'] ?? '');
        if (!$categoryId) {
            throw new \InvalidArgumentException('Valid category is required (name or ID).');
        }

        $subCategoryRaw = trim((string) ($data['sub_category'] ?? ''));
        $subCategoryId = $this->resolveSubCategoryId($subCategoryRaw, $categoryId);
        if ($subCategoryRaw !== '' && $subCategoryId === null) {
            throw new \InvalidArgumentException('Sub-category not found for the selected category.');
        }

        $productType = strtolower(trim((string) ($data['product_type'] ?? '')));
        if (!in_array($productType, ['veg', 'non-veg'], true)) {
            throw new \InvalidArgumentException('product_type must be veg or non-veg.');
        }

        $price = $this->parseMoney($data['price'] ?? null, 'price');
        $discount = $this->parseOptionalPercent($data['discount'] ?? null);

        $ingredients = trim(strip_tags((string) ($data['ingredients'] ?? '')));
        if ($ingredients === '') {
            throw new \InvalidArgumentException('ingredients (description) is required.');
        }

        $gstCalcType = strtolower(trim((string) ($data['gst_calculation_type'] ?? Product::GST_EXCLUDED)));
        if ($gstCalcType === '') {
            $gstCalcType = Product::GST_EXCLUDED;
        }
        if (!in_array($gstCalcType, Product::gstCalculationTypeOptions(), true)) {
            throw new \InvalidArgumentException('gst_calculation_type must be excluded or included.');
        }

        $gstPercentage = null;
        $taxName = null;
        $gstRaw = trim((string) ($data['gst_percentage'] ?? ''));
        if ($gstRaw !== '') {
            if (!is_numeric($gstRaw)) {
                throw new \InvalidArgumentException('gst_percentage must be numeric.');
            }
            $gstPercentage = number_format((float) $gstRaw, 2, '.', '');
            $taxName = 'GST ' . $gstPercentage . '%';
        }

        $minQty = null;
        $minRaw = trim((string) ($data['min_quantity'] ?? ''));
        if ($minRaw !== '') {
            if (!ctype_digit($minRaw) || (int) $minRaw < 1) {
                throw new \InvalidArgumentException('min_quantity must be a positive whole number.');
            }
            $minQty = (int) $minRaw;
        }

        $sku = trim((string) ($data['sku'] ?? ''));
        if ($sku === '') {
            $sku = $this->generateUniqueSku();
        } elseif (Product::where('sku', $sku)->exists()) {
            throw new \InvalidArgumentException('SKU already exists: ' . $sku);
        }

        $vendorId = $forcedVendorId;
        if ($allowVendorColumn && $vendorId === null) {
            $vendorRaw = trim((string) ($data['vendor_id'] ?? ''));
            if ($vendorRaw !== '') {
                if (!ctype_digit($vendorRaw)) {
                    throw new \InvalidArgumentException('vendor_id must be numeric.');
                }
                $vendorId = (int) $vendorRaw;
                if (!Vendor::where('vendor_id', $vendorId)->exists()) {
                    throw new \InvalidArgumentException('Vendor not found for vendor_id: ' . $vendorId);
                }
            }
        }

        $featured = in_array(strtolower(trim((string) ($data['featured'] ?? '0'))), ['1', 'yes', 'true'], true) ? 1 : 0;
        $tags = trim((string) ($data['tags'] ?? ''));

        DB::transaction(function () use (
            $name,
            $categoryId,
            $subCategoryId,
            $productType,
            $price,
            $discount,
            $ingredients,
            $gstCalcType,
            $gstPercentage,
            $taxName,
            $minQty,
            $sku,
            $vendorId,
            $featured,
            $tags
        ) {
            $product = new Product();
            $product->product_name = $name;
            $product->short_description = Str::limit($ingredients, 300);
            $product->product_slug = Str::slug($name) . '-' . time() . '-' . Str::lower(Str::random(4));
            $product->product_type = $productType;
            $product->category_id = $categoryId;
            $product->sub_category_id = $subCategoryId;
            $product->sub_sub_category_id = null;
            $product->price = $price;
            $product->mrp_price = $price;
            $product->discount = $discount;
            $product->sale_price = null;
            $product->sku = $sku;
            $product->min_quantity = $minQty;
            $product->applyLegacyStockDefaults();
            $product->tags = $tags !== '' ? $tags : null;
            $product->featured = $featured;
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
            $product->gst_calculation_type = $gstCalcType;
            $product->gst_percentage = $gstPercentage;
            $product->tax_name = $taxName;
            $product->vendor_id = $vendorId;
            $product->product_image = $this->defaultProductImageName();
            $product->save();

            $details = new ProductDetails();
            $details->product_id = $product->product_id;
            $details->product_description = $ingredients;
            $details->save();
        });
    }

    private function defaultProductImageName(): string
    {
        $source = public_path('assets/images/product/1.png');
        if (!is_file($source)) {
            $source = public_path('assets/images/logo/1.png');
        }

        $filename = 'bulk_' . time() . '_' . Str::lower(Str::random(6)) . '.png';
        $targetDir = public_path('uploads/products');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (is_file($source)) {
            copy($source, $targetDir . DIRECTORY_SEPARATOR . $filename);
        } else {
            touch($targetDir . DIRECTORY_SEPARATOR . $filename);
        }

        return $filename;
    }

    private function generateUniqueSku(): string
    {
        do {
            $sku = 'SF-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    private function resolveCategoryId(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $id = (int) $value;
            if (ProductCategory::where('category_id', $id)->where('status', '!=', 0)->exists()) {
                return $id;
            }

            return null;
        }

        $id = ProductCategory::where('status', '!=', 0)
            ->whereRaw('LOWER(category_name) = ?', [mb_strtolower($value)])
            ->value('category_id');

        return $id ? (int) $id : null;
    }

    private function resolveSubCategoryId(string $value, int $categoryId): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $id = (int) $value;
            $exists = ProductSubCategory::where('sub_category_id', $id)
                ->where('category_id', $categoryId)
                ->where('status', '!=', 0)
                ->exists();

            return $exists ? $id : null;
        }

        $id = ProductSubCategory::where('category_id', $categoryId)
            ->where('status', '!=', 0)
            ->whereRaw('LOWER(sub_cat_name) = ?', [mb_strtolower($value)])
            ->value('sub_category_id');

        return $id ? (int) $id : null;
    }

    private function parseMoney(mixed $value, string $field): float
    {
        $raw = trim((string) $value);
        if ($raw === '' || !is_numeric($raw)) {
            throw new \InvalidArgumentException($field . ' is required and must be numeric.');
        }
        $amount = (float) $raw;
        if ($amount < 0) {
            throw new \InvalidArgumentException($field . ' must be at least 0.');
        }

        return round($amount, 2);
    }

    private function parseOptionalPercent(mixed $value): float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }
        if (!is_numeric($raw)) {
            throw new \InvalidArgumentException('discount must be numeric.');
        }
        $pct = (float) $raw;
        if ($pct < 0 || $pct > 100) {
            throw new \InvalidArgumentException('discount must be between 0 and 100.');
        }

        return round($pct, 2);
    }

    /**
     * @return list<array<int, string>>
     */
    private function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        $rows = [];

        if ($extension === 'csv') {
            $handle = fopen($path, 'r');
            if ($handle === false) {
                return [];
            }
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = array_map(fn ($cell) => trim((string) $cell), $row);
            }
            fclose($handle);

            return $rows;
        }

        $content = (string) file_get_contents($path);
        $delimiter = str_contains($content, "\t") ? "\t" : ',';
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $rows[] = array_map('trim', str_getcsv($line, $delimiter));
        }

        return $rows;
    }

    /**
     * @param list<string> $headerRow
     * @return array<int, string>
     */
    private function mapHeaders(array $headerRow, bool $allowVendorColumn): array
    {
        $map = [];
        foreach ($headerRow as $index => $header) {
            $key = $this->normalizeHeader($header);
            if ($key === '') {
                continue;
            }
            if ($key === 'vendor_id' && !$allowVendorColumn) {
                continue;
            }
            $map[$index] = $key;
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        $header = preg_replace('/\*+$/', '', $header) ?? $header;
        $header = strtolower($header);
        $header = str_replace([' ', '-'], '_', $header);

        $aliases = [
            'product' => 'product_name',
            'food_name' => 'product_name',
            'name' => 'product_name',
            'category_name' => 'category',
            'category_id' => 'category',
            'subcategory' => 'sub_category',
            'sub_category_name' => 'sub_category',
            'type' => 'product_type',
            'food_type' => 'product_type',
            'mrp' => 'price',
            'selling_price' => 'price',
            'description' => 'ingredients',
            'product_description' => 'ingredients',
            'gst' => 'gst_percentage',
            'gst_percent' => 'gst_percentage',
            'gst_type' => 'gst_calculation_type',
            'vendor' => 'vendor_id',
            'restaurant_id' => 'vendor_id',
        ];

        return $aliases[$header] ?? $header;
    }

    /**
     * @param list<string> $row
     * @param array<int, string> $map
     * @return array<string, string>
     */
    private function rowToAssoc(array $row, array $map): array
    {
        $data = [];
        foreach ($map as $index => $key) {
            $data[$key] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    /**
     * @param list<string> $row
     * @param array<int, string> $map
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<string> $row
     * @param array<int, string> $map
     */
    private function isHintRow(array $row, array $map): bool
    {
        $first = strtolower(trim((string) ($row[0] ?? '')));

        return str_starts_with($first, '(') || str_contains($first, 'required');
    }
}
