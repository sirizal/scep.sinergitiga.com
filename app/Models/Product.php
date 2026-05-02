<?php

namespace App\Models;

use App\Services\ProductCategoryFinder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'sku',
        'slug',
        'name',
        'description',
        'variant_code',
        'product_category_id',
        'uom_id',
        'customer_product_code',
        'is_active',
    ];

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function productUoms(): HasMany
    {
        return $this->hasMany(ProductUom::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->withResponsiveImages();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->fit(Fit::Crop)
            ->format('webp')
            ->queued();

        $this->addMediaConversion('small')
            ->width(300)
            ->format('webp')
            ->queued();

        $this->addMediaConversion('medium')
            ->width(600)
            ->format('webp')
            ->queued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->format('webp')
            ->queued();
    }

    public function markFirstImageAsPrimary(): void
    {
        $mediaItems = $this->getMedia('images');

        if ($mediaItems->isEmpty()) {
            return;
        }

        $mediaItems->each(function ($media, $index) {
            $media->setCustomProperty('is_primary', $index === 0);
            $media->save();
        });
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $product->sku = self::generateSku();
            }

            if (empty($product->slug)) {
                $product->slug = self::generateSlug($product->name);
            }
        });

        static::created(function (Product $product) {
            ProductUom::create([
                'product_id' => $product->id,
                'uom_id' => $product->uom_id,
                'convert_uom_id' => $product->uom_id,
                'conversion_qty' => 1,
            ]);

            if (empty($product->product_category_id)) {
                $categoryId = app(ProductCategoryFinder::class)->findForProduct($product->name);
                if ($categoryId) {
                    $product->update(['product_category_id' => $categoryId]);
                }
            }
        });

        static::saved(function (Product $product) {
            if ($product->getMedia('images')->isNotEmpty()) {
                $product->markFirstImageAsPrimary();
            }
        });
    }

    public static function generateSku(): string
    {
        return DB::transaction(function () {
            $lastRecord = self::lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $lastNumber = 0;
            if ($lastRecord && preg_match('/^SKU(\d+)$/', $lastRecord->sku, $matches)) {
                $lastNumber = (int) $matches[1];
            }

            $newNumber = $lastNumber + 1;

            return 'SKU'.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->withTrashed()->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
