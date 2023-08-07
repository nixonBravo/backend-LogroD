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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')
                ->constrained('categorias')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('producto')->unique();
            $table->text('descripcion');
            $table->float('precio');
            $table->integer('stock');
            $table->string('public_id');
            $table->text('imagen');
            $table->boolean('estado_producto')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
