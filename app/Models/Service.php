<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the transactions associated with the service.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the price histories associated with the service.
     */
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Update the service price and record the change in price history.
     *
     * @param float $newPrice
     * @param string|null $reason
     * @return bool
     */
    public function updatePrice(float $newPrice, ?string $reason = null): bool
    {
        if ($this->price !== $newPrice) {
            PriceHistory::recordPriceChange($this->id, $this->price, $newPrice, $reason);
            $this->price = $newPrice;
            return $this->save();
        }

        return false;
    }

    /**
     * Get the current price.
     *
     * @return float
     */
    public function getCurrentPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * Get price history with an optional date range.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPriceHistory(?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->priceHistories()
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get();
    }
}
