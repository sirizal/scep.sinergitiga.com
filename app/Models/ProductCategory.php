<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'unspsc',
        'slug',
        'id_name',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductCategory $category) {
            $category->slug = static::generateUniqueSlug($category->name);
        });
    }

    private static function generateUniqueSlug(string $name): string
    {
        return Str::slug($name);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
