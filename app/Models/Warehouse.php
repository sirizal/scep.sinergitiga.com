<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'type',
        'address',
        'country_id',
        'province_id',
        'district_id',
        'sub_district_id',
        'village_id',
        'postal_code',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [];

        if ($this->address) {
            $parts[] = $this->address;
        }

        if ($this->village) {
            $parts[] = $this->village->name;
        }

        if ($this->subDistrict) {
            $parts[] = $this->subDistrict->name;
        }

        if ($this->district) {
            $parts[] = $this->district->name;
        }

        if ($this->province) {
            $parts[] = $this->province->name;
        }

        if ($this->country) {
            $parts[] = $this->country->name;
        }

        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }

        return implode(', ', array_filter($parts));
    }

    protected static function booted(): void
    {
        static::creating(function (Warehouse $warehouse) {
            if (empty($warehouse->code)) {
                $warehouse->code = self::generateCode();
            }

            if (empty($warehouse->slug)) {
                $warehouse->slug = self::generateSlug($warehouse->name);
            }
        });
    }

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $lastRecord = self::lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $lastNumber = 0;
            if ($lastRecord && preg_match('/^WH(\d+)$/', $lastRecord->code, $matches)) {
                $lastNumber = (int) $matches[1];
            }

            $newNumber = $lastNumber + 1;

            return 'WH'.str_pad($newNumber, 2, '0', STR_PAD_LEFT);
        });
    }

    public static function generateSlug(string $name): string
    {
        return Str::slug($name);
    }
}
