<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'productos';
    protected $fillable = [
        'categoria_id',
        'producto',
        'descripcion',
        'precio',
        'stock',
        'public_id',
        'url',
    ];

    public function categoria(){
        return $this->belongsTo(Categoria::class);
    }
}
