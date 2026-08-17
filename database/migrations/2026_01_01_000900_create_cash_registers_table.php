<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('opening_amount', 10, 2)->default(0);
            $table->decimal('closing_amount', 10, 2)->nullable();
            $table->decimal('expected_amount', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            $table->string('status')->default('abierta'); // abierta, cerrada
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
