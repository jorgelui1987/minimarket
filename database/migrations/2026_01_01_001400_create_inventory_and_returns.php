<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kardex / movimientos de inventario
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');                 // entrada, salida, ajuste
            $table->integer('quantity');            // cambio con signo (+ entra, - sale)
            $table->integer('stock_after');         // stock resultante
            $table->string('source')->nullable();   // venta, compra, devolucion, ajuste
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });

        // Devoluciones de venta (notas de crédito)
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cash_register_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total', 10, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('stock_movements');
    }
};
