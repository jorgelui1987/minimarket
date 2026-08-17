<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // ingreso, egreso
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('concept');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
