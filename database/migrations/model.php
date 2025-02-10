<?php

// app/Models/Service.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'type'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

// app/Models/Customer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function getTotalDebtAttribute()
    {
        return $this->debts()->where('status', 'pending')->sum('amount');
    }
}

// app/Models/Transaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_id',
        'quantity',
        'payment_status',
        'amount_paid'
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

    public function getTotalAmountAttribute()
    {
        return $this->service->price * $this->quantity;
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }
}

// app/Models/Debt.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'amount',
        'payment_date',
        'payment_method'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime'
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
}
