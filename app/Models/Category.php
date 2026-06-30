<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model {
    protected $fillable = ['id', 'name', 'slug', 'description', 'image'];

    protected static function booted() {
        static::creating(fn($cat) => $cat->slug = Str::slug($cat->name));
    }

    public function products() {
        return $this->hasMany(Product::class);
    }
}
