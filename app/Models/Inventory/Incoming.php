<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incoming extends Model
{
    use HasFactory;
    protected $casts = [
        'products' => 'array'
    ];

    protected $guarded = [];

    public function actor()
    {
        return $this->belongsTo(Actor::class, 'actor_id');
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
