<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function incomings()
    {
        return $this->hasMany(Incoming::class);
    }

    public function outgoings()
    {
        return $this->hasMany(Outgoing::class);
    }
}
