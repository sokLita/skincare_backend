<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'old_price',
        'stock',
        'image',
        'images',
        'is_active'
    ];

    protected $appends = [
        'image_url',
        'avg_rating',
        'rating_count',
        'discount_percent',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'images' => 'array',
        'is_active' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Helper for main image
    public function getMainImageAttribute()
    {
        return $this->image ?? ($this->images[0] ?? null);
    }

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }

            return asset('storage/' . $this->image);
        }

        return null;
    }

    // Accessor for average rating
    public function getAvgRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    // Accessor for review count
    public function getRatingCountAttribute()
    {
        return $this->reviews()->count();
    }

    // Accessor for discount percentage
    public function getDiscountPercentAttribute()
    {
        if (!$this->old_price || $this->old_price <= 0) {
            return 0;
        }
        return round((($this->old_price - $this->price) / $this->old_price) * 100);
    }
}
