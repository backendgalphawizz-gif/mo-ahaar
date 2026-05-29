<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VideoController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;
    private const TYPE_PRODUCT_SHOWCASE = 'product_showcase';
    private const TYPE_BUSINESS_INTRO = 'business_intro';

    public function feed(Request $request)
    {
        $user = $this->authorizedCustomer($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $type = (string) $request->input('type', 'all');
        $limit = (int) $request->input('limit', 20);
        if ($limit <= 0) {
            $limit = 20;
        }
        $limit = min($limit, 100);

        $videos = collect();

        if (in_array($type, ['all', self::TYPE_PRODUCT_SHOWCASE], true)) {
            $videos = $videos->concat($this->productShowcaseVideos());
        }

        if (in_array($type, ['all', self::TYPE_BUSINESS_INTRO], true)) {
            $videos = $videos->concat($this->businessIntroVideos());
        }

        $videos = $videos
            ->sortByDesc('created_at')
            ->values()
            ->take($limit)
            ->values();

        $videoKeys = $videos->pluck('video_key')->filter()->values()->all();

        $likeCounts = [];
        $shareCounts = [];
        $likedByUser = [];

        if (!empty($videoKeys) && Schema::hasTable('customer_video_likes')) {
            $likeCounts = DB::table('customer_video_likes')
                ->select('video_key', DB::raw('COUNT(*) as total'))
                ->whereIn('video_key', $videoKeys)
                ->groupBy('video_key')
                ->pluck('total', 'video_key')
                ->toArray();

            $likedByUser = DB::table('customer_video_likes')
                ->where('user_id', $user->user_id)
                ->whereIn('video_key', $videoKeys)
                ->pluck('video_key')
                ->flip()
                ->toArray();
        }

        if (!empty($videoKeys) && Schema::hasTable('customer_video_shares')) {
            $shareCounts = DB::table('customer_video_shares')
                ->select('video_key', DB::raw('COUNT(*) as total'))
                ->whereIn('video_key', $videoKeys)
                ->groupBy('video_key')
                ->pluck('total', 'video_key')
                ->toArray();
        }

        $videos = $videos->map(function (array $video) use ($likeCounts, $shareCounts, $likedByUser) {
            $key = $video['video_key'];
            $video['engagement'] = [
                'likes' => (int) ($likeCounts[$key] ?? 0),
                'shares' => (int) ($shareCounts[$key] ?? 0),
                'is_liked' => isset($likedByUser[$key]),
            ];

            return $video;
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Video feed loaded successfully',
            'data' => [
                'videos' => $videos,
            ],
        ], 200);
    }

    public function toggleLike(Request $request)
    {
        $user = $this->authorizedCustomer($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $validated = $request->validate([
            'video_type' => ['required', 'in:' . self::TYPE_PRODUCT_SHOWCASE . ',' . self::TYPE_BUSINESS_INTRO],
            'product_id' => ['nullable', 'integer'],
            'vendor_id' => ['nullable', 'integer'],
        ]);

        [$videoKey, $videoType, $productId, $vendorId, $error] = $this->resolveVideoReference($validated);
        if ($error) {
            return response()->json([
                'status' => false,
                'message' => $error,
            ], 422);
        }

        if (!Schema::hasTable('customer_video_likes')) {
            return response()->json([
                'status' => false,
                'message' => 'This feature is currently unavailable. Please try again later',
            ], 500);
        }

        $existing = DB::table('customer_video_likes')
            ->where('user_id', $user->user_id)
            ->where('video_key', $videoKey)
            ->first();

        $isLiked = false;
        if ($existing) {
            DB::table('customer_video_likes')
                ->where('id', $existing->id)
                ->delete();
        } else {
            DB::table('customer_video_likes')->insert([
                'user_id' => $user->user_id,
                'video_key' => $videoKey,
                'video_type' => $videoType,
                'product_id' => $productId,
                'vendor_id' => $vendorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $isLiked = true;
        }

        $totalLikes = DB::table('customer_video_likes')
            ->where('video_key', $videoKey)
            ->count();

        return response()->json([
            'status' => true,
            'message' => $isLiked ? 'Video liked successfully!' : 'Video unliked successfully',
            'data' => [
                'video_key' => $videoKey,
                'is_liked' => $isLiked,
                'likes' => (int) $totalLikes,
            ],
        ], 200);
    }

    public function share(Request $request)
    {
        $user = $this->authorizedCustomer($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $validated = $request->validate([
            'video_type' => ['required', 'in:' . self::TYPE_PRODUCT_SHOWCASE . ',' . self::TYPE_BUSINESS_INTRO],
            'product_id' => ['nullable', 'integer'],
            'vendor_id' => ['nullable', 'integer'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        [$videoKey, $videoType, $productId, $vendorId, $error] = $this->resolveVideoReference($validated);
        if ($error) {
            return response()->json([
                'status' => false,
                'message' => $error,
            ], 422);
        }

        if (!Schema::hasTable('customer_video_shares')) {
            return response()->json([
                'status' => false,
                'message' => 'This feature is currently unavailable. Please try again later',
            ], 500);
        }

        DB::table('customer_video_shares')->insert([
            'user_id' => $user->user_id,
            'video_key' => $videoKey,
            'video_type' => $videoType,
            'product_id' => $productId,
            'vendor_id' => $vendorId,
            'platform' => $validated['platform'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $totalShares = DB::table('customer_video_shares')
            ->where('video_key', $videoKey)
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Video shared successfully!',
            'data' => [
                'video_key' => $videoKey,
                'shares' => (int) $totalShares,
                'platform' => $validated['platform'] ?? null,
            ],
        ], 200);
    }

    private function productShowcaseVideos(): Collection
    {
        return DB::table('products as p')
            ->leftJoin('vendors as v', 'v.vendor_id', '=', 'p.vendor_id')
            ->select(
                'p.product_id',
                'p.vendor_id',
                'p.product_name',
                'p.short_description',
                'p.price',
                'p.sale_price',
                'p.product_image',
                'p.video',
                'p.created_at',
                'v.owner_name',
                'v.business_name',
                'v.profile_image as vendor_profile_image'
            )
            ->whereNotNull('p.video')
            ->where('p.video', '!=', '')
            ->when(Schema::hasColumn('products', 'is_active_status'), function ($query) {
                $query->whereIn('p.is_active_status', [1, '1']);
            })
            ->when(Schema::hasColumn('products', 'status'), function ($query) {
                $query->whereIn('p.status', [1, '1']);
            })
            ->when(Schema::hasColumn('vendors', 'status'), function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('v.status', '1')
                        ->orWhereNull('p.vendor_id');
                });
            })
            ->orderByDesc('p.product_id')
            ->get()
            ->map(function ($row) {
                $videoKey = 'product:' . $row->product_id;

                return [
                    'video_key' => $videoKey,
                    'video_type' => self::TYPE_PRODUCT_SHOWCASE,
                    'video_url' => $row->video,
                    'title' => $row->product_name,
                    'description' => $row->short_description,
                    'vendor' => [
                        'vendor_id' => $row->vendor_id,
                        'vendor_name' => $row->owner_name,
                        'business_name' => $row->business_name,
                        'profile_image_url' => !empty($row->vendor_profile_image) ? url('public/uploads/vendors/' . $row->vendor_profile_image) : null,
                    ],
                    'product' => [
                        'product_id' => $row->product_id,
                        'product_name' => $row->product_name,
                        'price' => $row->price,
                        'sale_price' => $row->sale_price,
                        'product_image_url' => !empty($row->product_image) ? url('public/uploads/products/' . $row->product_image) : null,
                    ],
                    'shop_now' => [
                        'enabled' => true,
                        'product_id' => $row->product_id,
                        'vendor_id' => $row->vendor_id,
                    ],
                    'created_at' => $row->created_at,
                ];
            });
    }

    private function businessIntroVideos(): Collection
    {
        if (!Schema::hasColumn('vendor_business_details', 'business_intro_video')) {
            return collect();
        }

        return DB::table('vendor_business_details as vbd')
            ->join('vendors as v', 'v.vendor_id', '=', 'vbd.vendor_id')
            ->select(
                'vbd.vendor_id',
                'vbd.business_intro_video',
                'vbd.business_description',
                'vbd.updated_at',
                'v.owner_name',
                'v.business_name',
                'v.profile_image as vendor_profile_image'
            )
            ->whereNotNull('vbd.business_intro_video')
            ->where('vbd.business_intro_video', '!=', '')
            ->when(Schema::hasColumn('vendors', 'status'), function ($query) {
                $query->where('v.status', '1');
            })
            ->orderByDesc('vbd.id')
            ->get()
            ->map(function ($row) {
                $videoKey = 'vendor_intro:' . $row->vendor_id;

                return [
                    'video_key' => $videoKey,
                    'video_type' => self::TYPE_BUSINESS_INTRO,
                    'video_url' => $row->business_intro_video,
                    'title' => $row->business_name,
                    'description' => $row->business_description,
                    'vendor' => [
                        'vendor_id' => $row->vendor_id,
                        'vendor_name' => $row->owner_name,
                        'business_name' => $row->business_name,
                        'profile_image_url' => !empty($row->vendor_profile_image) ? url('public/uploads/vendors/' . $row->vendor_profile_image) : null,
                    ],
                    'product' => null,
                    'shop_now' => [
                        'enabled' => true,
                        'vendor_id' => $row->vendor_id,
                    ],
                    'created_at' => $row->updated_at,
                ];
            });
    }

    private function resolveVideoReference(array $payload): array
    {
        $videoType = $payload['video_type'];

        if ($videoType === self::TYPE_PRODUCT_SHOWCASE) {
            $productId = isset($payload['product_id']) ? (int) $payload['product_id'] : 0;
            if ($productId <= 0) {
                return [null, null, null, null, 'product_id is required for product_showcase videos'];
            }

            $exists = DB::table('products')
                ->where('product_id', $productId)
                ->whereNotNull('video')
                ->where('video', '!=', '')
                ->exists();

            if (!$exists) {
                return [null, null, null, null, 'Product showcase video not found'];
            }

            $vendorId = (int) DB::table('products')->where('product_id', $productId)->value('vendor_id');

            return ['product:' . $productId, $videoType, $productId, $vendorId, null];
        }

        $vendorId = isset($payload['vendor_id']) ? (int) $payload['vendor_id'] : 0;
        if ($vendorId <= 0) {
            return [null, null, null, null, 'vendor_id is required for business_intro videos'];
        }

        if (!Schema::hasColumn('vendor_business_details', 'business_intro_video')) {
            return [null, null, null, null, 'Business introduction video feature is not available yet'];
        }

        $exists = DB::table('vendor_business_details')
            ->where('vendor_id', $vendorId)
            ->whereNotNull('business_intro_video')
            ->where('business_intro_video', '!=', '')
            ->exists();

        if (!$exists) {
            return [null, null, null, null, 'Business introduction video not found'];
        }

        return ['vendor_intro:' . $vendorId, $videoType, null, $vendorId, null];
    }

    private function authorizedCustomer(Request $request): ?Users
    {
        /** @var Users|null $user */
        $user = $request->user();
        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return null;
        }

        return $user;
    }
}
