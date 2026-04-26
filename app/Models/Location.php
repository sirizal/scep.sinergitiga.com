<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'zone',
        'aisle',
        'rack',
        'level',
        'bin',
        'warehouse_id',
        'type',
        'name',
        'max_weight',
        'max_volume',
        'is_active',
    ];

    protected $casts = [
        'max_weight' => 'decimal:2',
        'max_volume' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::creating(function (Location $location) {
            if (empty($location->code)) {
                $location->code = self::generateCode($location);
            }
        });
    }

    public static function generateCode(Location $location): string
    {
        return DB::transaction(function () use ($location) {
            $warehousePrefix = $location->warehouse_id
                ? Warehouse::find($location->warehouse_id)?->code ?? 'WH00'
                : 'WH00';

            $zone = $location->zone ?? '00';
            $aisle = $location->aisle ?? '00';
            $rack = $location->rack ?? '00';
            $level = $location->level ?? '00';
            $bin = $location->bin ?? '0';

            return "{$warehousePrefix}-{$zone}-{$aisle}-{$rack}-{$level}-{$bin}";
        });
    }
}
