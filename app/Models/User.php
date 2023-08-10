<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
/* use function Illuminate\Events\queueable;
use Laravel\Cashier\Billable; */

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; //Billable;
    public $timestamps = false;
    protected $table = 'users';

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'email',
        'password',
    ];

    public function carritos()
    {
        return $this->hasMany(Carrito::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    /* protected static function booted(): void
    {
        static::updated(queueable(function (User $customer) {
            if ($customer->hasStripeId()) {
                $customer->syncStripeCustomerDetails();
            }
        }));
    } */
}
