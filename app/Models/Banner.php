<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Banner extends Model
{
    use HasFactory;

    /** Main hero carousel on customer home. */
    public const TYPE_SLIDER = 'slider';

    public const TYPE_OFFER = 'offer';

    public const TYPE_PROMOTION = 'promotion';

    public const TYPE_ANNOUNCEMENT = 'announcement';

    /**
     * @return list<string>
     */
    public static function homeBannerTypeOptions(): array
    {
        return [
            self::TYPE_SLIDER,
            self::TYPE_OFFER,
            self::TYPE_PROMOTION,
            self::TYPE_ANNOUNCEMENT,
        ];
    }

    protected $table = 'banners';

    protected $fillable = [
        'title',
        'subtitle',
        'location',
        'banner_image',
        'button_text',
        'button_link',
        'sort_order',
        'banner_type',
        'visible_from',
        'visible_to',
        'status',
    ];

    protected $casts = [
        'visible_from' => 'date',
        'visible_to' => 'date',
    ];

    /**
     * Banners with no start/end restriction, or whose range includes today (date only).
     */
    public function scopeVisibleInDateRange($query)
    {
        if (!Schema::hasColumn((new static())->getTable(), 'visible_from')) {
            return $query;
        }

        $d = now()->toDateString();

        return $query
            ->where(function ($q) use ($d) {
                $q->whereNull('visible_from')
                    ->orWhereDate('visible_from', '<=', $d);
            })
            ->where(function ($q) use ($d) {
                $q->whereNull('visible_to')
                    ->orWhereDate('visible_to', '>=', $d);
            });
    }
}
