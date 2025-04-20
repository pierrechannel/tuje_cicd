<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'purchase_price',
        'selling_price', 'quantity_in_stock', 'minimum_stock',
        'category_id', 'supplier_id', 'image_url'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Check if product is out of stock
    public function isOutOfStock()
    {
        return $this->quantity_in_stock <= 0;
    }

    // Check if stock is low
    public function hasLowStock()
    {
        return $this->quantity_in_stock <= $this->minimum_stock;
    }
}
