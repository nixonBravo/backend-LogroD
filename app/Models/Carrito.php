<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'carritos';
    protected $fillable = [
        'user_id',
        'fecha',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carritoProducto()
    {
        return $this->hasMany(CarritoProducto::class);
    }

    public function calcularTotal()
    {
        return $this->carritoProducto->sum(function ($productoEnCarrito) {
            return $productoEnCarrito->producto->precio * $productoEnCarrito->cantidad;
        });
    }
}
