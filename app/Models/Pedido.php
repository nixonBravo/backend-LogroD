<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'pedidos';
    protected $fillable = [
        'user_id',
        'fecha_pedido',
        'direccion',
        'celular',
        'estado',
        'total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class);
    }
}
