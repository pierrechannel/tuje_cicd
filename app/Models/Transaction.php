<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'payment_status',
        'amount_paid',
        'total_amount', // Add this line to allow mass assignment
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function debt()
    {
        return $this->hasOne(Debt::class);
    }

    public function items()  // This method represents the relationship with TransactionItem
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }
}
