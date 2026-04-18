<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    use SoftDeletes;

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
        'phone_no',
        'fax_no',
        'email',
        'website',
        'contact_name',
        'payment_term_id',
        'credit_limit',
        'tax_id',
        'bussiness_license_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
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
        return $this->belongsTo(SubDistrict::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Vendor $vendor) {
            if (empty($vendor->code)) {
                $vendor->code = self::generateCode();
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
            if ($lastRecord && preg_match('/^VE(\d+)$/', $lastRecord->code, $matches)) {
                $lastNumber = (int) $matches[1];
            }

            $newNumber = $lastNumber + 1;

            return 'VE'.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
