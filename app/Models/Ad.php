<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Ad extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_SOLD     = 'sold';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED  = 'expired';

    public const STATUS_DELETED  = 'deleted';

    protected $fillable = [
        'category_id', 'city_id', 'title', 'slug', 'description',
        'price', 'is_negotiable', 'condition', 'contact_phone',
    ];

    protected $casts = [
        'is_negotiable' => 'boolean',
        'price' => 'integer',
        'published_at'  => 'datetime',
        'expires_at'    => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function user()
    {

        return $this->belongsTo(User::class);

    }
    public function category()
    {

        return $this->belongsTo(Category::class);

    }
    public function city()
    {

        return $this->belongsTo(City::class);

    }
    public function images()
    {

        return $this->hasMany(AdImage::class)->orderBy('sort_order');

    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function canBeRenewed(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }


        return $this->status === self::STATUS_ACTIVE
            && $this->expires_at
            && $this->expires_at->lte(now()->addDays(7));
    }

    public function canBeRestored(): bool
    {
        if ($this->status === self::STATUS_DELETED) {
            return true;
        }

        return $this->status === self::STATUS_PENDING
            && $this->updated_at->lte(now()->subDays(2));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function reports()
    {

        return $this->hasMany(Report::class);

    }

    public function publishSince(): string
    {
        return ($this->published_at ?? $this->created_at)->diffForHumans();
    }
}
