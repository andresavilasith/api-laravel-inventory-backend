<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function incomings()
    {
        return $this->hasMany(Incoming::class);
    }

    public function outgoings()
    {
        return $this->hasMany(Outgoing::class);
    }
}
