<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash; // Import the Hash facade


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'image',
    ];

    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // Using 'hashed' to ensure the password is always stored as a hash
        'password' => 'hashed',
    ];

    /**
     * Get the expenses for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Automatically hash the password when it is set.
     *
     * @param string $password
     * @return void
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
 * Get the user's permissions based on their role.
 *
 * @return array
 */
public function getPermissions(): array
{
    // Define your roles and associated permissions
    $rolesPermissions = [
        'admin' => [
            'view_dashboard',
            'view_transactions',
            'view_customers',
            'view_expenses',
            'view_debts',
            'view_payments',
            'manage_users',
            'view_reports',
            'manage_settings',
        ],
        'user' => [
            'view_dashboard',
            'view_transactions',
            'view_customers',
            'view_expenses',
            'view_debts',
            'view_payments',
        ],
        // Add more roles and permissions as needed
    ];

    return $rolesPermissions[$this->role] ?? [];
}

}
