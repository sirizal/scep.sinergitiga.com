<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategoryLevel extends Model
{
    protected $fillable = [
        'category_0',
        'category_1',
        'category_2',
        'category_3',
        'category_4',
        'category_5',
        'category_6',
        'unspsc',
    ];
}
