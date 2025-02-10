<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;
    protected $fillable = ['category_name'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
