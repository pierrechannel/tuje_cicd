<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;
    // Define the fillable fields
    protected $fillable = [
        'transaction_id',
        'service_id',
        'quantity',
        'price',
    ];

    /**
     * Defines the relationship with the Transaction model.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Defines the relationship with the Service model.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
