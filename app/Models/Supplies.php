<?php

// app/Models/Approvisionnement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplies extends Model
{
    use HasFactory;

    protected $fillable = [
        'product',
        'quantity',
        'unit_price',
        'supply_date',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}