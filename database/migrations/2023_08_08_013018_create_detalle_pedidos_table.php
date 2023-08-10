<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->onUpdate('cascade')
                ->onUpdate('cascade');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onUpdate('cascade')
                ->onUpdate('cascade');
            $table->integer('cantidad');
            $table->float('precio_unitario');
            $table->float('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
