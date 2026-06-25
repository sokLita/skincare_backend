<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model {
    protected $fillable = ['category_id','name','slug','description','price','stock','image','is_active'];
    protected $casts = ['price' => 'float', 'is_active' => 'boolean'];
    protected $appends = ['image_url'];

    protected static function boot() {
        parent::boot();
        static::creating(function($p) {
            if (empty($p->slug)) {
                $p->slug = Str::slug($p->name);
            }
        });
    }

    public function category()  { return $this->belongsTo(Category::class); }
    public function reviews()   { return $this->hasMany(Review::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function cartItems() { return $this->hasMany(Cart::class); }
    public function orderItems(){ return $this->hasMany(OrderItem::class); }

    public function getImageUrlAttribute() {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}