<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'transaction_id',
        'amount',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
