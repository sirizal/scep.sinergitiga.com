<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ShipAddress extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'country_id',
        'province_id',
        'district_id',
        'sub_district_id',
        'village_id',
        'postal_code',
        'contact_name',
        'phone_no',
        'email',
        'customer_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

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
        return $this->belongsTo(SubDistrict::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    protected static function booted(): void
    {
        static::creating(function (ShipAddress $shipAddress) {
            if (empty($shipAddress->code)) {
                $shipAddress->code = self::generateCode();
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
            if ($lastRecord && preg_match('/^SA(\d+)$/', $lastRecord->code, $matches)) {
                $lastNumber = (int) $matches[1];
            }

            $newNumber = $lastNumber + 1;

            return 'SA'.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
