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
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carritos()
    {
        return $this->hasMany(CarritoProducto::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_productos', 'carrito_id', 'producto_id')
            ->withPivot('cantidad');
    }

}
