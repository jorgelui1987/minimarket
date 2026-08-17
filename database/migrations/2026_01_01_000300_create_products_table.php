<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable()->index();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit')->default('UND'); // UND, KG, LT
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
