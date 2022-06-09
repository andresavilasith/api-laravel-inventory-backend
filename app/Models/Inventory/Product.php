<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category_product()
    {
        return $this->belongsTo(CategoryProduct::class, 'category_product_id');
    }
    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}
