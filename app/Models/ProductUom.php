<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUom extends Model
{
    protected $fillable = [
        'product_id',
        'uom_id',
        'convert_uom_id',
        'conversion_qty',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function convertUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'convert_uom_id');
    }
}
