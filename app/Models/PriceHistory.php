<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth; // Import for Auth class

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'old_price',
        'new_price',
        'reason',
        'changed_by'
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    /**
     * Get the service associated with the price history.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the user who changed the price.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Record a price change in the price histories.
     *
     * @param int $serviceId
     * @param float $oldPrice
     * @param float $newPrice
     * @param string|null $reason
     * @return PriceHistory
     * @throws \InvalidArgumentException
     */
    public static function recordPriceChange(int $serviceId, float $oldPrice, float $newPrice, ?string $reason = null): PriceHistory
    {
        // Basic validation
        if ($newPrice < 0 || $oldPrice < 0) {
            throw new \InvalidArgumentException('Prices cannot be negative.');
        }

        if ($oldPrice === $newPrice) {
            throw new \InvalidArgumentException('Old price must be different from new price.');
        }

        // Capture the currently authenticated user (assuming they are logged in)
        $changedBy = 1; // Or pass the ID as an argument if you wish

        $priceHistory = self::create([
            'service_id' => $serviceId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'reason' => $reason,
            'changed_by' => $changedBy,
        ]);

        // Optionally dispatch an event or log the change
        // PriceChangeRecorded::dispatch($priceHistory);

        return $priceHistory;
    }
}
